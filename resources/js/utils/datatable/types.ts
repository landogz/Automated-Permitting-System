export type ApicsFilterOption = {
    value: string;
    label: string;
};

export type ApicsTableFilter<T> = {
    id: string;
    label: string;
    options: ApicsFilterOption[];
    /** Initial value (default ''). */
    value?: string;
    /**
     * Client-side matcher. If omitted and fetchData is used, filter values
     * are passed to fetchData for server-side filtering.
     */
    match?: (row: T, value: string) => boolean;
};

export type ApicsColumn<T> = {
    data?: keyof T & string;
    title: string;
    name?: string;
    orderable?: boolean;
    searchable?: boolean;
    className?: string;
    /** Lower = kept longer on small screens. Actions use priority 1 automatically. */
    responsivePriority?: number;
    visible?: boolean;
    /** Exclude from export buttons (Actions always excluded). */
    exportable?: boolean;
    /** Exclude from Columns visibility toggle. */
    toggleable?: boolean;
    render?: (data: unknown, type: string, row: T, meta: unknown) => string | number;
};

export type ApicsRowAction<T> = {
    id: string;
    label: string;
    icon?: string;
    className?: string;
    danger?: boolean;
    dividerBefore?: boolean;
    /** Show as a visible primary button beside the ⋮ menu (first matching wins). */
    primary?: boolean;
    visible?: (row: T) => boolean;
    disabled?: (row: T) => boolean;
    onClick: (row: T) => void | Promise<void>;
};

export type ApicsFetchContext = {
    search: string;
    filters: Record<string, string>;
};

export type ApicsDataTableConfig<T extends object> = {
    /** CSS selector or table element. */
    table: string | HTMLTableElement;
    columns: ApicsColumn<T>[];
    /** Static rows (optional if fetchData provided). */
    data?: T[];
    /** Load/reload rows from API. Preferred for live admin lists. */
    fetchData?: (ctx: ApicsFetchContext) => Promise<T[]>;
    rowId?: keyof T & string | ((row: T) => string);
    actions?: ApicsRowAction<T>[];
    filters?: ApicsTableFilter<T>[];
    exportFileName?: string;
    pageLength?: number;
    order?: Array<[number, 'asc' | 'desc']>;
    /** Extra toolbar HTML host (optional). Factory creates default filter bar. */
    toolbarHost?: string | HTMLElement | null;
    emptyMessage?: string;
    noResultsMessage?: string;
    /**
     * `client` (default): DataTables search on loaded rows.
     * `server`: reload via fetchData using ctx.search (debounce).
     */
    searchMode?: 'client' | 'server';
};

export type ApicsDataTableApi<T extends object> = {
    reload: (resetPaging?: boolean) => Promise<void>;
    getFilters: () => Record<string, string>;
    setFilter: (id: string, value: string) => void;
    clearFilters: () => void;
    getSearch: () => string;
    raw: unknown;
    destroy: () => void;
};
