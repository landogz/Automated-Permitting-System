import { hideModal } from '../../../utils/bootstrap-modal';
import { hasPermission } from '../../../utils/auth';
import { confirmAction, toastError, toastSuccess } from '../../../utils/toast';
import {
    escapeHtml,
    formatWhen,
    iconFor,
    navigateInternal,
    resolveNotificationItems,
    timeAgo,
    type InboxNotification,
} from '../../notifications/shared';

type FilterStatus = 'all' | 'unread' | 'read';

type Counts = { total: number; unread: number; read: number };

const FOLDER_LABELS: Record<FilterStatus, string> = {
    all: 'All notifications',
    unread: 'Unread',
    read: 'Read',
};

const PER_PAGE = 40;

export function initNotificationsPage(): void {
    const root = document.getElementById('apics-notif-inbox');
    if (!root) return;

    const listEl = root.querySelector<HTMLUListElement>('[data-notif-mail-list]');
    const loaderEl = root.querySelector<HTMLElement>('[data-notif-loader]');
    const emptyEl = root.querySelector<HTMLElement>('[data-notif-empty]');
    const rangeEl = root.querySelector<HTMLElement>('[data-notif-range]');
    const folderLabelEl = root.querySelector<HTMLElement>('[data-notif-folder-label]');
    const detailBody = root.querySelector<HTMLElement>('[data-notif-detail-body]');
    const openRelatedBtn = root.querySelector<HTMLButtonElement>('[data-notif-open-related]');
    const markOneBtn = root.querySelector<HTMLButtonElement>('[data-notif-mark-one]');
    const searchInput = root.querySelector<HTMLInputElement>('[data-notif-search]');
    const pagerEl = root.querySelector<HTMLElement>('[data-notif-pager]');
    const pageLabel = root.querySelector<HTMLElement>('[data-notif-page-label]');
    const prevBtn = root.querySelector<HTMLButtonElement>('[data-notif-prev]');
    const nextBtn = root.querySelector<HTMLButtonElement>('[data-notif-next]');
    const adminTools = root.querySelector<HTMLElement>('[data-notif-admin-tools]');
    const templatesTable = document.querySelector<HTMLTableElement>('[data-notif-templates-table]');
    const templateForm = document.getElementById('form-add-template') as HTMLFormElement | null;

    if (!listEl || !detailBody) return;

    let filter: FilterStatus = 'all';
    let search = '';
    let page = 1;
    let lastPage = 1;
    let items: InboxNotification[] = [];
    let selectedUuid: string | null = null;
    let searchTimer: number | null = null;
    let menuOpen = false;

    const canTemplates = hasPermission('workflow.manage');

    if (canTemplates) {
        adminTools?.classList.remove('d-none');
    }

    const setCounts = (counts: Counts): void => {
        (['total', 'unread', 'read'] as const).forEach((key) => {
            root.querySelectorAll<HTMLElement>(`[data-notif-count="${key}"]`).forEach((el) => {
                el.textContent = String(counts[key] ?? 0);
            });
        });
    };

    const closeDetail = (): void => {
        document.body.classList.remove('email-detail-show');
        selectedUuid = null;
        listEl.querySelectorAll('li.is-active').forEach((li) => li.classList.remove('is-active'));
        detailBody.innerHTML = `
            <div class="text-center text-muted py-5">
                <p class="fs-13 mb-0">Select a notification to read it.</p>
            </div>
        `;
        openRelatedBtn?.classList.add('d-none');
        markOneBtn?.classList.add('d-none');
        delete openRelatedBtn?.dataset.url;
        delete markOneBtn?.dataset.uuid;
    };

    const paintDetail = (item: InboxNotification): void => {
        const style = iconFor(item.data?.event);
        const url = (item.data?.url || '').trim();

        detailBody.innerHTML = `
            <div class="mt-4 mb-3">
                <div class="d-flex align-items-start gap-3">
                    <span class="apics-notif-inbox__avatar ${style.bg} ${style.text} fs-18">
                        <i class="${style.icon}"></i>
                    </span>
                    <div class="min-w-0 flex-grow-1">
                        <h5 class="fw-bold email-subject-title mb-1">${escapeHtml(item.title || 'Notification')}</h5>
                        <p class="text-muted fs-12 mb-0">
                            <i class="mdi mdi-clock-outline align-middle"></i>
                            ${escapeHtml(formatWhen(item.created_at))}
                            ${item.is_read ? '' : ' · <span class="badge bg-warning-subtle text-warning">Unread</span>'}
                        </p>
                    </div>
                </div>
            </div>
            <div class="apics-notif-inbox__detail-card mb-3">${escapeHtml(item.body || 'No additional details.')}</div>
            <div class="d-flex flex-wrap gap-2 mb-2">
                ${item.channel ? `<span class="badge bg-secondary-subtle text-secondary">${escapeHtml(item.channel)}</span>` : ''}
                ${item.template_code ? `<span class="badge bg-info-subtle text-info">${escapeHtml(item.template_code)}</span>` : ''}
                ${item.status ? `<span class="badge bg-primary-subtle text-primary">${escapeHtml(item.status)}</span>` : ''}
            </div>
            ${url && url !== '#'
                ? `<button type="button" class="btn btn-primary btn-sm" data-notif-detail-open data-url="${escapeHtml(url)}">
                        Open related page <i class="ri-arrow-right-line align-middle ms-1"></i>
                   </button>`
                : ''}
        `;

        if (openRelatedBtn) {
            openRelatedBtn.classList.toggle('d-none', !(url && url !== '#'));
            if (url && url !== '#') {
                openRelatedBtn.dataset.url = url;
            } else {
                delete openRelatedBtn.dataset.url;
            }
        }

        if (markOneBtn) {
            markOneBtn.classList.toggle('d-none', Boolean(item.is_read));
            markOneBtn.dataset.uuid = item.uuid;
        }
    };

    const openDetail = (item: InboxNotification): void => {
        selectedUuid = item.uuid;
        document.body.classList.add('email-detail-show');
        listEl.querySelectorAll('li').forEach((li) => {
            li.classList.toggle('is-active', li.dataset.uuid === item.uuid);
        });
        paintDetail(item);
    };

    const markRead = async (uuid: string): Promise<InboxNotification | null> => {
        await window.axios.post(`/api/v1/notifications/${uuid}/read`);
        items = items.map((row) => (
            row.uuid === uuid
                ? { ...row, is_read: true, read_at: new Date().toISOString() }
                : row
        ));
        const updated = items.find((row) => row.uuid === uuid) || null;
        const li = listEl.querySelector<HTMLElement>(`li[data-uuid="${uuid}"]`);
        li?.classList.remove('unread');
        return updated;
    };

    const renderList = (): void => {
        if (!listEl) return;

        if (items.length === 0) {
            listEl.innerHTML = '';
            emptyEl?.classList.remove('d-none');
            return;
        }

        emptyEl?.classList.add('d-none');
        listEl.innerHTML = items
            .map((item) => {
                const style = iconFor(item.data?.event);
                const unreadClass = item.is_read ? '' : 'unread';
                const activeClass = item.uuid === selectedUuid ? 'is-active' : '';
                const preview = escapeHtml((item.body || '').replace(/\s+/g, ' ').slice(0, 120));
                const when = escapeHtml(timeAgo(item.created_at));

                return `
                    <li class="${unreadClass} ${activeClass}" data-uuid="${escapeHtml(item.uuid)}" role="listitem">
                        <div class="col-mail col-mail-1">
                            <span class="apics-notif-inbox__avatar ${style.bg} ${style.text} me-2 fs-15">
                                <i class="${style.icon}"></i>
                            </span>
                            <a href="javascript:void(0)" class="title">
                                <span class="title-name">${escapeHtml(item.title || 'Notification')}</span>
                            </a>
                        </div>
                        <div class="col-mail col-mail-2">
                            <a href="javascript:void(0)" class="subject">
                                <span class="subject-title">${escapeHtml(item.title || 'Notification')}</span>
                                – <span class="teaser">${preview}${preview.length >= 120 ? '…' : ''}</span>
                            </a>
                            <div class="date">${when}</div>
                        </div>
                    </li>
                `;
            })
            .join('');
    };

    const load = async (showToast = false): Promise<void> => {
        root.setAttribute('aria-busy', 'true');
        loaderEl?.classList.remove('d-none');

        try {
            const { data } = await window.axios.get('/api/v1/notifications', {
                params: {
                    per_page: PER_PAGE,
                    page,
                    status: filter,
                    search: search || undefined,
                },
            });

            if (!data?.status) {
                throw new Error(data?.message || 'Unable to load notifications');
            }

            items = resolveNotificationItems(data);
            const counts = (data.data?.counts || {
                total: data.data?.meta?.total ?? items.length,
                unread: data.data?.unread_count ?? 0,
                read: 0,
            }) as Counts;

            if (typeof counts.read !== 'number') {
                counts.read = Math.max(0, (counts.total || 0) - (counts.unread || 0));
            }

            setCounts(counts);
            lastPage = Number(data.data?.meta?.last_page || 1);
            const current = Number(data.data?.meta?.current_page || page);
            const total = Number(data.data?.meta?.total || items.length);
            const perPage = Number(data.data?.meta?.per_page || PER_PAGE);
            const from = total === 0 ? 0 : (current - 1) * perPage + 1;
            const to = Math.min(current * perPage, total);

            if (folderLabelEl) {
                folderLabelEl.textContent = FOLDER_LABELS[filter];
            }
            if (rangeEl) {
                rangeEl.textContent = total === 0
                    ? 'No messages in this folder'
                    : `Showing ${from}–${to} of ${total}`;
            }

            if (pagerEl && pageLabel && prevBtn && nextBtn) {
                const multi = lastPage > 1;
                pagerEl.classList.toggle('d-none', !multi);
                pageLabel.textContent = `Page ${current} of ${lastPage}`;
                prevBtn.disabled = current <= 1;
                nextBtn.disabled = current >= lastPage;
            }

            renderList();

            if (selectedUuid) {
                const still = items.find((row) => row.uuid === selectedUuid);
                if (still) {
                    openDetail(still);
                } else {
                    closeDetail();
                }
            }

            if (showToast) {
                toastSuccess('Inbox refreshed');
            }
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Unable to load notifications');
            items = [];
            renderList();
            emptyEl?.classList.remove('d-none');
        } finally {
            loaderEl?.classList.add('d-none');
            root.setAttribute('aria-busy', 'false');
        }
    };

    const loadTemplates = async (): Promise<void> => {
        if (!templatesTable || !canTemplates) return;
        const tbody = templatesTable.querySelector('tbody');
        if (!tbody) return;

        try {
            const { data } = await window.axios.get('/api/v1/admin/notification-templates', {
                params: { per_page: 100 },
            });
            const rows = data.data?.items || [];
            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-muted text-center py-4">No templates yet.</td></tr>';
                return;
            }

            tbody.innerHTML = rows
                .map((row: { uuid: string; code: string; name: string; channel: string; is_active: boolean }) => `
                    <tr data-uuid="${escapeHtml(row.uuid)}">
                        <td><code class="fs-12">${escapeHtml(row.code)}</code></td>
                        <td>${escapeHtml(row.name)}</td>
                        <td>${escapeHtml(row.channel)}</td>
                        <td>${row.is_active
                            ? '<span class="badge bg-success-subtle text-success">Yes</span>'
                            : '<span class="badge bg-secondary-subtle text-secondary">No</span>'}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-soft-danger" data-tpl-delete data-uuid="${escapeHtml(row.uuid)}">
                                Delete
                            </button>
                        </td>
                    </tr>
                `)
                .join('');
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Unable to load templates');
        }
    };

    // Folder filters
    root.querySelectorAll<HTMLAnchorElement>('[data-notif-filter]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const next = (link.dataset.notifFilter || 'all') as FilterStatus;
            filter = next;
            page = 1;
            root.querySelectorAll('[data-notif-filter]').forEach((el) => el.classList.remove('active'));
            link.classList.add('active');
            void load();
        });
    });

    // List click → open + mark read
    listEl.addEventListener('click', (event) => {
        const li = (event.target as HTMLElement).closest<HTMLElement>('li[data-uuid]');
        if (!li?.dataset.uuid) return;

        const item = items.find((row) => row.uuid === li.dataset.uuid);
        if (!item) return;

        openDetail(item);

        if (!item.is_read) {
            void markRead(item.uuid)
                .then((updated) => {
                    if (updated && selectedUuid === updated.uuid) {
                        paintDetail(updated);
                    }
                    // Refresh counts without full flicker when possible
                    void load();
                })
                .catch(() => {
                    // Keep detail open
                });
        }
    });

    detailBody.addEventListener('click', (event) => {
        const openBtn = (event.target as HTMLElement).closest<HTMLElement>('[data-notif-detail-open]');
        if (openBtn?.dataset.url && !navigateInternal(openBtn.dataset.url)) {
            toastError('Unsafe notification link blocked.');
        }
    });

    root.querySelector('[data-notif-close-detail]')?.addEventListener('click', () => {
        closeDetail();
    });

    openRelatedBtn?.addEventListener('click', () => {
        const url = openRelatedBtn.dataset.url;
        if (url && !navigateInternal(url)) {
            toastError('Unsafe notification link blocked.');
        }
    });

    markOneBtn?.addEventListener('click', () => {
        const uuid = markOneBtn.dataset.uuid;
        if (!uuid) return;
        void (async () => {
            try {
                const updated = await markRead(uuid);
                toastSuccess('Marked as read');
                if (updated) {
                    paintDetail(updated);
                }
                await load();
            } catch (error: any) {
                toastError(error?.response?.data?.message || 'Unable to mark as read');
            }
        })();
    });

    root.querySelector('[data-notif-mark-all]')?.addEventListener('click', () => {
        void (async () => {
            try {
                await window.axios.post('/api/v1/notifications/read-all');
                toastSuccess('All notifications marked read');
                closeDetail();
                await load();
            } catch (error: any) {
                toastError(error?.response?.data?.message || 'Update failed');
            }
        })();
    });

    root.querySelector('[data-notif-refresh]')?.addEventListener('click', () => {
        void load(true);
    });

    searchInput?.addEventListener('input', () => {
        if (searchTimer) {
            window.clearTimeout(searchTimer);
        }
        searchTimer = window.setTimeout(() => {
            search = (searchInput.value || '').trim();
            page = 1;
            void load();
        }, 320);
    });

    prevBtn?.addEventListener('click', () => {
        if (page <= 1) return;
        page -= 1;
        void load();
    });

    nextBtn?.addEventListener('click', () => {
        if (page >= lastPage) return;
        page += 1;
        void load();
    });

    // Mobile sidebar toggle (Velzon pattern)
    root.querySelectorAll('.email-menu-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            root.querySelector('.email-menu-sidebar')?.classList.add('menubar-show');
            menuOpen = true;
        });
    });

    window.addEventListener('click', (event) => {
        const sidebar = root.querySelector('.email-menu-sidebar');
        if (!sidebar?.classList.contains('menubar-show')) return;
        if (menuOpen) {
            menuOpen = false;
            return;
        }
        if (!(event.target as HTMLElement).closest('.email-menu-sidebar')) {
            sidebar.classList.remove('menubar-show');
        }
    });

    templateForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            await window.axios.post('/api/v1/admin/notification-templates', {
                code: (document.getElementById('tpl-code') as HTMLInputElement).value.trim(),
                name: (document.getElementById('tpl-name') as HTMLInputElement).value.trim(),
                channel: (document.getElementById('tpl-channel') as HTMLSelectElement).value,
                subject: (document.getElementById('tpl-subject') as HTMLInputElement).value.trim() || undefined,
                body_template: (document.getElementById('tpl-body') as HTMLTextAreaElement).value.trim(),
                is_active: true,
            });
            toastSuccess('Template created');
            templateForm.reset();
            hideModal('modal-add-template');
            await loadTemplates();
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Create failed');
        }
    });

    templatesTable?.addEventListener('click', (event) => {
        const btn = (event.target as HTMLElement).closest<HTMLElement>('[data-tpl-delete]');
        if (!btn?.dataset.uuid) return;

        void (async () => {
            if (!(await confirmAction('Delete template?', 'This soft-deletes the template.'))) return;
            try {
                await window.axios.delete(`/api/v1/admin/notification-templates/${btn.dataset.uuid}`);
                toastSuccess('Template deleted');
                await loadTemplates();
            } catch (error: any) {
                toastError(error?.response?.data?.message || 'Delete failed');
            }
        })();
    });

    document.getElementById('modal-templates')?.addEventListener('show.bs.modal', () => {
        void loadTemplates();
    });

    void load();
}
