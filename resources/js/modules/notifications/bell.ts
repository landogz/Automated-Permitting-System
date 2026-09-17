import { isAuthenticated, hasPermission } from '../../utils/auth';
import { toastError, toastSuccess } from '../../utils/toast';

type BellNotification = {
    uuid: string;
    title: string;
    body: string;
    data?: { url?: string; event?: string } | null;
    created_at?: string | null;
    is_read?: boolean;
    read_at?: string | null;
};

function timeAgo(iso?: string | null): string {
    if (!iso) return '';
    const then = new Date(iso).getTime();
    if (Number.isNaN(then)) return '';
    const seconds = Math.max(0, Math.floor((Date.now() - then) / 1000));
    if (seconds < 60) return 'Just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)} min ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)} hr ago`;
    return `${Math.floor(seconds / 86400)} d ago`;
}

function iconFor(event?: string): { bg: string; text: string; icon: string } {
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

function escapeHtml(value: string): string {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function resolveItems(payload: unknown): BellNotification[] {
    if (!payload || typeof payload !== 'object') {
        return [];
    }

    const root = payload as Record<string, unknown>;
    const data = (root.data && typeof root.data === 'object' ? root.data : root) as Record<string, unknown>;
    const raw = data.items ?? data.data ?? [];

    if (!Array.isArray(raw)) {
        return [];
    }

    return raw.map((row) => {
        const item = (row && typeof row === 'object' && 'data' in row && typeof (row as { data: unknown }).data === 'object'
            && !('uuid' in row)
            ? (row as { data: BellNotification }).data
            : row) as BellNotification;

        return item;
    }).filter((item) => Boolean(item?.uuid));
}

export function initNotificationBell(): void {
    const root = document.querySelector<HTMLElement>('[data-apics-notif-bell]');
    if (!root || !isAuthenticated()) {
        return;
    }

    root.classList.remove('d-none');

    const badge = root.querySelector<HTMLElement>('[data-notif-badge]');
    const newCount = root.querySelector<HTMLElement>('[data-notif-new-count]');
    const list = root.querySelector<HTMLElement>('[data-notif-list]');
    const detail = root.querySelector<HTMLElement>('[data-notif-detail]');
    const markAllBtn = root.querySelector<HTMLButtonElement>('[data-notif-mark-all]');
    const viewAll = root.querySelector<HTMLAnchorElement>('[data-notif-view-all]');
    const menu = root.querySelector<HTMLElement>('.dropdown-menu');

    if (!list || !menu) {
        return;
    }

    if (viewAll && hasPermission('applications.manage')) {
        viewAll.classList.remove('d-none');
    }

    let cache: BellNotification[] = [];

    const setBadge = (count: number): void => {
        const safe = Math.max(0, count);
        if (!badge || !newCount) return;
        badge.classList.toggle('d-none', safe <= 0);
        badge.innerHTML = `${safe > 99 ? '99+' : String(safe)}<span class="visually-hidden">unread notifications</span>`;
        newCount.textContent = `${safe} New`;
    };

    const hideDetail = (): void => {
        if (!detail) return;
        detail.classList.add('d-none');
        detail.innerHTML = '';
        list.classList.remove('d-none');
    };

    const showDetail = (item: BellNotification): void => {
        if (!detail) return;

        const style = iconFor(item.data?.event);
        const url = (item.data?.url || '').trim();
        const when = item.created_at
            ? new Date(item.created_at).toLocaleString(undefined, {
                dateStyle: 'medium',
                timeStyle: 'short',
            })
            : timeAgo(item.created_at);

        list.classList.add('d-none');
        detail.classList.remove('d-none');
        detail.innerHTML = `
            <div class="apics-notif-detail p-3">
                <button type="button" class="btn btn-sm btn-ghost-secondary mb-2 px-1" data-notif-back>
                    <i class="ri-arrow-left-line align-middle"></i> Back to list
                </button>
                <div class="d-flex gap-3 mb-3">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title ${style.bg} ${style.text} rounded-circle fs-18">
                            <i class="${style.icon}"></i>
                        </span>
                    </div>
                    <div class="min-w-0">
                        <h6 class="mb-1 fs-14 fw-semibold">${escapeHtml(item.title || 'Notification')}</h6>
                        <p class="mb-0 fs-11 text-muted text-uppercase">
                            <i class="mdi mdi-clock-outline"></i> ${escapeHtml(when)}
                        </p>
                    </div>
                </div>
                <div class="apics-notif-detail__body fs-13 text-body mb-3">${escapeHtml(item.body || 'No additional details.')}</div>
                <div class="d-grid gap-2">
                    ${url && url !== '#'
                        ? `<button type="button" class="btn btn-primary btn-sm" data-notif-open data-url="${escapeHtml(url)}">
                                Open related page
                           </button>`
                        : ''}
                    ${item.is_read
                        ? ''
                        : `<button type="button" class="btn btn-soft-secondary btn-sm" data-notif-mark-one data-uuid="${escapeHtml(item.uuid)}">
                                Mark as read
                           </button>`}
                </div>
            </div>
        `;
    };

    const render = (items: BellNotification[], unread: number): void => {
        cache = items;
        setBadge(unread);
        hideDetail();

        if (items.length === 0) {
            list.innerHTML = '<div class="text-center text-muted py-4 px-3 fs-13">No notifications yet.</div>';
            return;
        }

        list.innerHTML = items
            .map((item) => {
                const event = item.data?.event;
                const style = iconFor(event);
                const unreadClass = item.is_read ? '' : 'is-unread';
                const preview = escapeHtml((item.body || '').replace(/\s+/g, ' ').slice(0, 140));

                return `
                    <button type="button"
                        class="apics-notif-item ${unreadClass}"
                        data-notif-item
                        data-uuid="${escapeHtml(item.uuid)}"
                        aria-label="View notification: ${escapeHtml(item.title || 'Notification')}">
                        <span class="avatar-xs me-3 flex-shrink-0">
                            <span class="avatar-title ${style.bg} ${style.text} rounded-circle fs-16">
                                <i class="${style.icon}"></i>
                            </span>
                        </span>
                        <span class="apics-notif-item__content text-start min-w-0">
                            <span class="apics-notif-item__title">${escapeHtml(item.title || 'Notification')}</span>
                            <span class="apics-notif-item__preview">${preview}${preview.length >= 140 ? '…' : ''}</span>
                            <span class="apics-notif-item__meta">
                                <i class="mdi mdi-clock-outline"></i> ${escapeHtml(timeAgo(item.created_at))}
                                <span class="apics-notif-item__hint">Tap for details</span>
                            </span>
                        </span>
                        <i class="ri-arrow-right-s-line apics-notif-item__chevron text-muted"></i>
                    </button>
                `;
            })
            .join('');
    };

    const markRead = async (uuid: string): Promise<void> => {
        await window.axios.post(`/api/v1/notifications/${uuid}/read`);
        cache = cache.map((item) => (
            item.uuid === uuid
                ? { ...item, is_read: true, read_at: new Date().toISOString() }
                : item
        ));
        setBadge(cache.filter((item) => !item.is_read).length);
    };

    const load = async (): Promise<void> => {
        try {
            const { data } = await window.axios.get('/api/v1/notifications', {
                params: { per_page: 12 },
            });
            if (!data?.status) return;
            const items = resolveItems(data);
            const unread = Number(
                data.data?.unread_count
                ?? items.filter((n) => !n.is_read).length,
            );
            render(items, unread);
        } catch {
            // Non-blocking
        }
    };

    // Delegate from the dropdown menu so SimpleBar / re-renders cannot detach handlers.
    menu.addEventListener('click', (event) => {
        const target = event.target as HTMLElement;

        if (target.closest('[data-notif-back]')) {
            event.preventDefault();
            event.stopPropagation();
            hideDetail();
            return;
        }

        const openBtn = target.closest<HTMLElement>('[data-notif-open]');
        if (openBtn) {
            event.preventDefault();
            event.stopPropagation();
            const url = openBtn.dataset.url || '';
            if (url && url !== '#') {
                window.location.href = url;
            }
            return;
        }

        const markOne = target.closest<HTMLElement>('[data-notif-mark-one]');
        if (markOne?.dataset.uuid) {
            event.preventDefault();
            event.stopPropagation();
            void (async () => {
                try {
                    await markRead(markOne.dataset.uuid as string);
                    toastSuccess('Marked as read');
                    const item = cache.find((row) => row.uuid === markOne.dataset.uuid);
                    if (item) {
                        showDetail({ ...item, is_read: true });
                    }
                } catch (error: any) {
                    toastError(error?.response?.data?.message || 'Unable to mark as read');
                }
            })();
            return;
        }

        const itemEl = target.closest<HTMLElement>('[data-notif-item]');
        if (itemEl?.dataset.uuid) {
            event.preventDefault();
            event.stopPropagation();

            const item = cache.find((row) => row.uuid === itemEl.dataset.uuid);
            if (!item) {
                return;
            }

            showDetail(item);

            if (!item.is_read) {
                void markRead(item.uuid).then(() => {
                    const updated = cache.find((row) => row.uuid === item.uuid);
                    if (updated && !detail?.classList.contains('d-none')) {
                        showDetail(updated);
                    }
                }).catch(() => {
                    // Keep detail open even if mark-read fails.
                });
            }
        }
    });

    markAllBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        void (async () => {
            try {
                await window.axios.post('/api/v1/notifications/read-all');
                toastSuccess('All notifications marked read');
                await load();
            } catch (error: any) {
                toastError(error?.response?.data?.message || 'Unable to mark notifications read');
            }
        })();
    });

    root.addEventListener('show.bs.dropdown', () => {
        void load();
    });

    void load();
    window.setInterval(() => {
        if (!root.classList.contains('show') && !menu.classList.contains('show')) {
            void load();
        }
    }, 60000);
}
