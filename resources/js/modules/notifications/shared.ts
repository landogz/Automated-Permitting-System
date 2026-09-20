export type InboxNotification = {
    uuid: string;
    title: string;
    body: string;
    channel?: string;
    status?: string;
    template_code?: string | null;
    data?: { url?: string; event?: string } | null;
    created_at?: string | null;
    read_at?: string | null;
    is_read?: boolean;
};

export function escapeHtml(value: string): string {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

/** Same-origin relative paths only — blocks https://evil and //. */
export function isSafeInternalPath(url: string | undefined | null): boolean {
    const value = (url || '').trim();
    if (!value || value.startsWith('//')) return false;
    if (/^[a-z][a-z0-9+.-]*:/i.test(value)) return false;
    return /^\/[A-Za-z0-9/_\-.?=&%]*$/.test(value);
}

export function navigateInternal(url: string | undefined | null): boolean {
    const value = (url || '').trim();
    if (!isSafeInternalPath(value)) {
        return false;
    }
    window.location.href = value;
    return true;
}

export function timeAgo(iso?: string | null): string {
    if (!iso) return '';
    const then = new Date(iso).getTime();
    if (Number.isNaN(then)) return '';
    const seconds = Math.max(0, Math.floor((Date.now() - then) / 1000));
    if (seconds < 60) return 'Just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)} min ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)} hr ago`;
    return `${Math.floor(seconds / 86400)} d ago`;
}

export function formatWhen(iso?: string | null): string {
    if (!iso) return '—';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '—';
    return d.toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

export function iconFor(event?: string): { bg: string; text: string; icon: string } {
    const key = (event || '').toLowerCase();
    if (key.includes('fail') || key.includes('declined') || key.includes('compliance')) {
        return { bg: 'bg-danger-subtle', text: 'text-danger', icon: 'bx bx-error-circle' };
    }
    if (key.includes('payment') || key.includes('order')) {
        return { bg: 'bg-success-subtle', text: 'text-success', icon: 'bx bx-wallet' };
    }
    if (key.includes('inspection')) {
        return { bg: 'bg-warning-subtle', text: 'text-warning', icon: 'bx bx-hard-hat' };
    }
    if (key.includes('document') || key.includes('correction')) {
        return { bg: 'bg-info-subtle', text: 'text-info', icon: 'bx bx-file' };
    }
    if (key.includes('release') || key.includes('approved') || key.includes('passed')) {
        return { bg: 'bg-success-subtle', text: 'text-success', icon: 'bx bx-badge-check' };
    }
    return { bg: 'bg-primary-subtle', text: 'text-primary', icon: 'bx bx-bell' };
}

export function resolveNotificationItems(payload: unknown): InboxNotification[] {
    if (!payload || typeof payload !== 'object') {
        return [];
    }

    const root = payload as Record<string, unknown>;
    const data = (root.data && typeof root.data === 'object' ? root.data : root) as Record<string, unknown>;
    const raw = data.items ?? data.data ?? [];

    if (!Array.isArray(raw)) {
        return [];
    }

    return raw
        .map((row) => {
            const item = (
                row
                && typeof row === 'object'
                && 'data' in row
                && typeof (row as { data: unknown }).data === 'object'
                && !('uuid' in row)
                    ? (row as { data: InboxNotification }).data
                    : row
            ) as InboxNotification;

            return item;
        })
        .filter((item) => Boolean(item?.uuid));
}
