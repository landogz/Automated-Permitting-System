import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import 'datatables.net-buttons-bs5';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
import 'datatables.net-buttons/js/buttons.colVis.mjs';

import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import 'datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css';
import 'datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css';
import './apics-datatable.css';

import { hideContextMenu, showContextMenu } from './context-menu';
import type {
    ApicsColumn,
    ApicsDataTableApi,
    ApicsDataTableConfig,
    ApicsFetchContext,
    ApicsRowAction,
} from './types';
import { escapeHtml } from '../bootstrap-modal';
import { printDataTableGovernment } from '../print';
import { toastSuccess } from '../toast';

declare global {
    interface Window {
        JSZip?: unknown;
        pdfMake?: { vfs?: unknown };
    }
}

async function ensureExportLibraries(): Promise<void> {
    if (!window.JSZip) {
        const mod = await import('jszip');
        window.JSZip = mod.default;
    }

    if (!window.pdfMake?.vfs) {
        const pdfMakeMod = await import('pdfmake/build/pdfmake');
        const pdfFontsMod = await import('pdfmake/build/vfs_fonts');
        const pdfMake = (pdfMakeMod as { default?: typeof pdfMakeMod }).default ?? pdfMakeMod;
        const fontsRoot = pdfFontsMod as {
            pdfMake?: { vfs?: unknown };
            default?: { pdfMake?: { vfs?: unknown }; vfs?: unknown };
            vfs?: unknown;
        };
        const vfs =
            fontsRoot.pdfMake?.vfs ??
            fontsRoot.default?.pdfMake?.vfs ??
            fontsRoot.default?.vfs ??
            fontsRoot.vfs;
        (pdfMake as { vfs?: unknown }).vfs = vfs;
        window.pdfMake = pdfMake as { vfs?: unknown };
    }
}

function resolveTable(target: string | HTMLTableElement): HTMLTableElement {
    const el = typeof target === 'string' ? document.querySelector(target) : target;
    if (!(el instanceof HTMLTableElement)) {
        throw new Error('createApicsDataTable: table element not found');
    }
    return el;
}

function resolveHost(target: string | HTMLElement | null | undefined, table: HTMLTableElement): HTMLElement {
    if (target instanceof HTMLElement) {
        return target;
    }
    if (typeof target === 'string') {
        const found = document.querySelector(target);
        if (found instanceof HTMLElement) {
            return found;
        }
    }

    let shell = table.closest('.apics-dt-shell') as HTMLElement | null;
    if (!shell) {
        shell = document.createElement('div');
        shell.className = 'apics-dt-shell';
        table.parentElement?.insertBefore(shell, table);
        shell.appendChild(table);
    }

    return shell;
}

function badgeYesNo(active: boolean): string {
    return active
        ? '<span class="badge bg-success-subtle text-success">Yes</span>'
        : '<span class="badge bg-danger-subtle text-danger">No</span>';
}

function buildActionsHtml<T extends object>(actions: ApicsRowAction<T>[], row: T, rowKey: string): string {
    const visible = actions.filter((a) => (a.visible ? a.visible(row) : true));
    if (!visible.length) {
        return '<span class="text-muted">—</span>';
    }

    const primary = visible.find((a) => a.primary && !(a.disabled?.(row)));
    const menuActions = visible.filter((a) => !primary || a.id !== primary.id);

    const items = menuActions
        .map((action) => {
            const disabled = action.disabled?.(row) ? 'disabled' : '';
            const danger = action.danger ? 'text-danger' : '';
            const divider = action.dividerBefore ? '<li><hr class="dropdown-divider"></li>' : '';
            return `${divider}<li><button type="button" class="dropdown-item ${danger}" data-apics-action="${escapeHtml(action.id)}" data-apics-row="${escapeHtml(rowKey)}" ${disabled}>${escapeHtml(action.label)}</button></li>`;
        })
        .join('');

    const primaryBtn = primary
        ? `<button type="button" class="btn btn-sm btn-primary apics-dt-primary-action" data-apics-action="${escapeHtml(primary.id)}" data-apics-row="${escapeHtml(rowKey)}">${escapeHtml(primary.label)}</button>`
        : '';

    const kebab =
        menuActions.length > 0
            ? `<div class="dropdown apics-dt-action-dropdown">
            <button
                type="button"
                class="btn btn-sm btn-ghost-secondary apics-dt-action-toggle"
                data-bs-toggle="dropdown"
                data-bs-auto-close="true"
                data-bs-popper-config='{"strategy":"fixed","modifiers":[{"name":"preventOverflow","options":{"boundary":"viewport","padding":8}},{"name":"flip","options":{"fallbackPlacements":["top-end","bottom-end"]}}]}'
                aria-expanded="false"
                aria-label="Row actions"
                title="More actions"
            >
                <i class="ri-more-2-fill" aria-hidden="true"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow apics-dt-action-menu">${items}</ul>
        </div>`
            : '';

    return `<div class="apics-dt-actions-group">${primaryBtn}${kebab}</div>`;
}

function applyClientFilters<T extends object>(
    rows: T[],
    filters: ApicsDataTableConfig<T>['filters'],
    values: Record<string, string>,
): T[] {
    if (!filters?.length) {
        return rows;
    }

    return rows.filter((row) =>
        filters.every((filter) => {
            const value = values[filter.id] ?? '';
            if (value === '') {
                return true;
            }
            if (filter.match) {
                return filter.match(row, value);
            }
            return true;
        }),
    );
}

/**
 * Create a project-standard DataTable (search, filters, export, responsive, actions, context menu).
 */
export async function createApicsDataTable<T extends object>(
    config: ApicsDataTableConfig<T>,
): Promise<ApicsDataTableApi<T>> {
    await ensureExportLibraries();

    const table = resolveTable(config.table);
    table.classList.add('table', 'table-hover', 'align-middle', 'apics-datatable', 'w-100');
    table.classList.remove('table-nowrap');

    const shell = resolveHost(config.toolbarHost, table);
    shell.classList.add('apics-dt-shell');

    let loadingEl = shell.querySelector('.apics-dt-loading') as HTMLDivElement | null;
    if (!loadingEl) {
        loadingEl = document.createElement('div');
        loadingEl.className = 'apics-dt-loading';
        loadingEl.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div>';
        shell.appendChild(loadingEl);
    }

    const setLoading = (on: boolean): void => {
        shell.classList.toggle('is-loading', on);
    };

    const filterValues: Record<string, string> = {};
    (config.filters || []).forEach((f) => {
        filterValues[f.id] = f.value ?? '';
    });

    let allRows: T[] = config.data ? [...config.data] : [];
    const rowMap = new Map<string, T>();

    const getRowKey = (row: T, index: number): string => {
        if (typeof config.rowId === 'function') {
            return String(config.rowId(row));
        }
        if (typeof config.rowId === 'string' && row[config.rowId] != null) {
            return String(row[config.rowId]);
        }
        const maybeUuid = (row as { uuid?: string }).uuid;
        if (maybeUuid) {
            return String(maybeUuid);
        }
        return `row-${index}`;
    };

    const rebuildRowMap = (rows: T[]): void => {
        rowMap.clear();
        rows.forEach((row, index) => {
            rowMap.set(getRowKey(row, index), row);
        });
    };

    let toolbar = shell.querySelector('.apics-dt-toolbar') as HTMLDivElement | null;
    if (!toolbar) {
        toolbar = document.createElement('div');
        toolbar.className = 'apics-dt-toolbar';
        shell.insertBefore(toolbar, shell.firstChild);
    }
    toolbar.innerHTML = '';

    const searchGroup = document.createElement('div');
    searchGroup.className = 'apics-dt-search-group';
    searchGroup.innerHTML = `
        <label class="form-label" for="apics-dt-search-${table.id || 'table'}">Search</label>
        <input type="search" class="form-control form-control-sm apics-dt-search" id="apics-dt-search-${table.id || 'table'}" placeholder="Search…" autocomplete="off">`;
    toolbar.appendChild(searchGroup);

    (config.filters || []).forEach((filter) => {
        const group = document.createElement('div');
        group.className = 'apics-dt-filter-group';
        const options = filter.options
            .map((opt) => `<option value="${escapeHtml(opt.value)}" ${filterValues[filter.id] === opt.value ? 'selected' : ''}>${escapeHtml(opt.label)}</option>`)
            .join('');
        group.innerHTML = `
            <label class="form-label" for="apics-dt-filter-${filter.id}">${escapeHtml(filter.label)}</label>
            <select class="form-select form-select-sm apics-dt-filter" id="apics-dt-filter-${filter.id}" data-filter-id="${escapeHtml(filter.id)}">${options}</select>`;
        toolbar!.appendChild(group);
    });

    const actionsGroup = document.createElement('div');
    actionsGroup.className = 'apics-dt-actions-group';
    actionsGroup.innerHTML = `
        <button type="button" class="btn btn-link btn-sm text-decoration-none apics-dt-clear-filters d-none px-1">Clear filters</button>
        <div class="apics-dt-export-host"></div>`;
    toolbar.appendChild(actionsGroup);

    const searchInput = searchGroup.querySelector('.apics-dt-search') as HTMLInputElement;
    const exportHost = actionsGroup.querySelector('.apics-dt-export-host') as HTMLDivElement;
    const clearFiltersBtn = actionsGroup.querySelector('.apics-dt-clear-filters') as HTMLButtonElement;

    const syncClearVisibility = (): void => {
        const hasActive =
            searchInput.value.trim() !== '' ||
            Object.values(filterValues).some((value) => value !== '');
        clearFiltersBtn.classList.toggle('d-none', !hasActive);
    };

    const dataColumns: ApicsColumn<T>[] = [...config.columns];
    const hasActions = Boolean(config.actions?.length);

    if (hasActions) {
        dataColumns.push({
            title: 'Actions',
            orderable: false,
            searchable: false,
            exportable: false,
            toggleable: false,
            responsivePriority: 1,
            className: 'apics-dt-actions all',
            render: (_data, _type, row) => {
                const key = getRowKey(row, 0);
                return buildActionsHtml(config.actions || [], row, key);
            },
        });
    }

    const dtColumns = dataColumns.map((col, index) => {
        const isActions = hasActions && index === dataColumns.length - 1;
        return {
            data: col.data ?? null,
            title: col.title,
            name: col.name || col.data || `col_${index}`,
            orderable: col.orderable !== false && !isActions,
            searchable: col.searchable !== false && !isActions,
            className: col.className || (isActions ? 'apics-dt-actions all' : undefined),
            visible: col.visible !== false,
            responsivePriority: col.responsivePriority ?? (isActions ? 1 : index === 0 ? 2 : index + 4),
            defaultContent: '—',
            render: col.render
                ? (data: unknown, type: string, row: T, meta: unknown) => col.render!(data, type, row, meta)
                : undefined,
        };
    });

    const exportableIndexes = dataColumns
        .map((col, index) => ({ col, index }))
        .filter(({ col, index }) => {
            const isActions = hasActions && index === dataColumns.length - 1;
            return !isActions && col.exportable !== false;
        })
        .map(({ index }) => index);

    const fileName = config.exportFileName || 'export';

    // Destroy prior instance if re-init.
    if (DataTable.isDataTable(table)) {
        new DataTable.Api(table).destroy();
        table.querySelector('thead')?.remove();
        table.querySelector('tbody')?.replaceChildren();
    }

    const dt = new DataTable(table, {
        data: [],
        columns: dtColumns,
        pageLength: config.pageLength ?? 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: config.order ?? [[0, 'asc']],
        responsive: {
            details: {
                type: 'inline',
                target: 'tr',
                renderer: (_api, _rowIdx, columns) => {
                    const items = columns.filter(
                        (col) => col.hidden && !String(col.title || '').toLowerCase().includes('action'),
                    );
                    if (!items.length) {
                        return false;
                    }

                    const list = document.createElement('ul');
                    list.className = 'dtr-details';
                    items.forEach((col) => {
                        const li = document.createElement('li');
                        li.dataset.dtrIndex = String(col.columnIndex);
                        li.innerHTML = `<span class="dtr-title">${escapeHtml(String(col.title || ''))}</span><span class="dtr-data"></span>`;
                        const dataEl = li.querySelector('.dtr-data');
                        if (dataEl) {
                            if (typeof col.data === 'string') {
                                dataEl.innerHTML = col.data;
                            } else if (col.data instanceof Node) {
                                dataEl.appendChild(col.data);
                            } else {
                                dataEl.textContent = String(col.data ?? '—');
                            }
                        }
                        list.appendChild(li);
                    });
                    return list;
                },
            },
        },
        columnDefs: (hasActions
            ? [
                  {
                      targets: -1,
                      className: 'apics-dt-actions all',
                      orderable: false,
                      searchable: false,
                  },
                  {
                      targets: 0,
                      className: 'all',
                  },
              ]
            : [
                  {
                      targets: 0,
                      className: 'all',
                  },
              ]) as never,
        dom: '<"d-none"B>rt<"apics-dt-footer d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2"lip>',
        buttons: {
            dom: {
                button: {
                    className: 'btn btn-sm btn-soft-secondary',
                },
            },
            buttons: [
                {
                    extend: 'collection',
                    text: 'Export',
                    className: 'btn btn-sm btn-soft-secondary',
                    background: false,
                    autoClose: true,
                    align: 'button-right',
                    buttons: [
                        {
                            extend: 'copyHtml5',
                            exportOptions: { columns: exportableIndexes },
                            copySuccess: false,
                            action: function (e, dt, button, config, cb) {
                                const rows = dt.rows({ search: 'applied' }).count();
                                const original = (
                                    DataTable.ext.buttons.copyHtml5 as { action?: Function }
                                ).action;
                                if (typeof original !== 'function') {
                                    cb?.();
                                    return;
                                }
                                original.call(
                                    this,
                                    e,
                                    dt,
                                    button,
                                    { ...config, copySuccess: false },
                                    () => {
                                        toastSuccess(
                                            rows === 1
                                                ? 'Copied 1 row to clipboard'
                                                : `Copied ${rows} rows to clipboard`,
                                        );
                                        cb?.();
                                    },
                                );
                            },
                        },
                        { extend: 'csvHtml5', title: fileName, exportOptions: { columns: exportableIndexes } },
                        { extend: 'excelHtml5', title: fileName, exportOptions: { columns: exportableIndexes } },
                        { extend: 'pdfHtml5', title: fileName, exportOptions: { columns: exportableIndexes } },
                        {
                            text: 'Print',
                            action: function (_e: unknown, dtApi: unknown) {
                                printDataTableGovernment(dtApi, {
                                    exportColumnIndexes: exportableIndexes,
                                    exportFileName: fileName,
                                    formCode: 'LIST',
                                });
                            },
                        },
                    ],
                },
                {
                    extend: 'colvis',
                    text: 'Columns',
                    className: 'btn btn-sm btn-soft-secondary',
                    background: false,
                    autoClose: true,
                    align: 'button-right',
                    columns: (idx: number) => {
                        const col = dataColumns[idx];
                        if (!col) return false;
                        const isActions = hasActions && idx === dataColumns.length - 1;
                        return !isActions && col.toggleable !== false;
                    },
                },
            ],
        },
        language: {
            emptyTable: config.emptyMessage || 'There are currently no records.',
            zeroRecords: config.noResultsMessage || 'No matching records found.',
            search: '',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoFiltered: '(filtered from _MAX_ total)',
            paginate: {
                first: 'First',
                previous: 'Previous',
                next: 'Next',
                last: 'Last',
            },
        },
        pagingType: 'full_numbers',
        autoWidth: false,
    });

    // Fit-to-screen wrapper — Responsive collapses columns; no horizontal scroll.
    if (!table.closest('.apics-dt-scroll')) {
        const scroll = document.createElement('div');
        scroll.className = 'apics-dt-scroll';
        table.parentElement?.insertBefore(scroll, table);
        scroll.appendChild(table);
    }

    // Recalc responsive layout after scroll wrapper + toolbar settle.
    requestAnimationFrame(() => {
        try {
            dt.columns.adjust();
            // @ts-expect-error Responsive plugin API
            dt.responsive?.recalc?.();
        } catch {
            /* ignore */
        }
    });

    window.addEventListener(
        'resize',
        () => {
            try {
                dt.columns.adjust();
                // @ts-expect-error Responsive plugin API
                dt.responsive?.recalc?.();
            } catch {
                /* ignore */
            }
        },
        { passive: true },
    );

    // Move Buttons into toolbar (Export / Columns).
    const btnContainer = dt.buttons().container();
    exportHost.appendChild(btnContainer[0] as HTMLElement);

    // Control column for responsive expand — prepend after init is awkward; use first data col as control.
    // DataTables responsive with target 0 uses first column — ensure first column isn't Actions.
    // We already put Actions last.

    const syncRows = (rows: T[]): void => {
        rebuildRowMap(rows);
        const filtered = applyClientFilters(rows, config.filters, filterValues);
        const page = dt.page();
        const order = dt.order();
        const search = dt.search();
        dt.clear();
        dt.rows.add(filtered as never[]);
        dt.search(search);
        dt.order(order);
        dt.draw(false);
        try {
            dt.page(page).draw(false);
        } catch {
            dt.draw(false);
        }
    };

    const load = async (resetPaging = false): Promise<void> => {
        setLoading(true);
        try {
            if (config.fetchData) {
                const ctx: ApicsFetchContext = {
                    search: searchInput.value.trim(),
                    filters: { ...filterValues },
                };
                allRows = await config.fetchData(ctx);
            }
            rebuildRowMap(allRows);
            const filtered = applyClientFilters(allRows, config.filters, filterValues);
            dt.clear();
            dt.rows.add(filtered as never[]);
            if (resetPaging) {
                dt.search(searchInput.value.trim()).draw();
            } else {
                const page = dt.page();
                dt.draw(false);
                try {
                    dt.page(page).draw(false);
                } catch {
                    /* ignore */
                }
            }
        } finally {
            setLoading(false);
        }
    };

    const runAction = async (actionId: string, rowKey: string): Promise<void> => {
        const row = rowMap.get(rowKey);
        const action = (config.actions || []).find((a) => a.id === actionId);
        if (!row || !action) {
            return;
        }
        if (action.disabled?.(row)) {
            return;
        }
        await action.onClick(row);
    };

    table.addEventListener('click', (event) => {
        const target = event.target as HTMLElement | null;
        // Don't toggle responsive child when using the Actions menu.
        if (target?.closest('.apics-dt-actions, .apics-dt-action-menu, .dropdown-menu')) {
            event.stopPropagation();
        }
        const btn = target?.closest('[data-apics-action]') as HTMLElement | null;
        if (!btn) {
            return;
        }
        event.preventDefault();
        void runAction(btn.dataset.apicsAction || '', btn.dataset.apicsRow || '');
    });

    // Sticky Actions cells share z-index; lift the open row so the menu is not covered.
    table.addEventListener('show.bs.dropdown', (event) => {
        const toggle = event.target as HTMLElement | null;
        const cell = toggle?.closest('td.apics-dt-actions') as HTMLElement | null;
        cell?.classList.add('is-actions-open');
    });
    table.addEventListener('hide.bs.dropdown', (event) => {
        const toggle = event.target as HTMLElement | null;
        const cell = toggle?.closest('td.apics-dt-actions') as HTMLElement | null;
        cell?.classList.remove('is-actions-open');
    });

    table.addEventListener('contextmenu', (event) => {
        const tr = (event.target as HTMLElement | null)?.closest('tr');
        if (!tr || !table.tBodies[0]?.contains(tr) || tr.classList.contains('child')) {
            return;
        }
        event.preventDefault();

        const rowData = dt.row(tr).data() as T | undefined;
        if (!rowData) {
            return;
        }

        const key = getRowKey(rowData, 0);
        if (!rowMap.has(key)) {
            rowMap.set(key, rowData);
        }

        const items = (config.actions || [])
            .filter((a) => (a.visible ? a.visible(rowData) : true))
            .map((action) => ({
                id: action.id,
                label: action.label,
                danger: action.danger,
                dividerBefore: action.dividerBefore,
                disabled: Boolean(action.disabled?.(rowData)),
                onClick: () => runAction(action.id, key),
            }));

        showContextMenu(event.clientX, event.clientY, items);
    });

    const filtersNeedServer = (): boolean =>
        Boolean(config.fetchData && (config.filters || []).some((f) => !f.match));

    let searchTimer: number | undefined;
    searchInput.addEventListener('input', () => {
        syncClearVisibility();
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => {
            if (config.searchMode === 'server' && config.fetchData) {
                void load(true);
                return;
            }
            dt.search(searchInput.value).draw();
        }, 300);
    });

    toolbar.querySelectorAll('.apics-dt-filter').forEach((el) => {
        el.addEventListener('change', () => {
            const select = el as HTMLSelectElement;
            const id = select.dataset.filterId || '';
            filterValues[id] = select.value;
            syncClearVisibility();
            if (filtersNeedServer()) {
                void load(true);
            } else {
                syncRows(allRows);
            }
        });
    });

    clearFiltersBtn.addEventListener('click', () => {
        Object.keys(filterValues).forEach((id) => {
            filterValues[id] = '';
        });
        toolbar!.querySelectorAll('.apics-dt-filter').forEach((el) => {
            (el as HTMLSelectElement).value = '';
        });
        searchInput.value = '';
        dt.search('');
        syncClearVisibility();
        if (filtersNeedServer() || config.searchMode === 'server') {
            void load(true);
        } else {
            syncRows(allRows);
        }
    });

    syncClearVisibility();
    await load(true);

    return {
        reload: (resetPaging = false) => load(resetPaging),
        getFilters: () => ({ ...filterValues }),
        setFilter: (id, value) => {
            filterValues[id] = value;
            const select = toolbar!.querySelector(`[data-filter-id="${id}"]`) as HTMLSelectElement | null;
            if (select) {
                select.value = value;
            }
            syncClearVisibility();
            void load(true);
        },
        clearFilters: () => {
            clearFiltersBtn.click();
        },
        getSearch: () => searchInput.value.trim(),
        raw: dt,
        destroy: () => {
            hideContextMenu();
            dt.destroy();
        },
    };
}

export { badgeYesNo };
