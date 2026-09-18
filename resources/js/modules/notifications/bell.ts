import { isAuthenticated, hasPermission } from '../../utils/auth';
import { toastError, toastSuccess } from '../../utils/toast';
import {
    escapeHtml,
    iconFor,
    resolveNotificationItems,
    timeAgo,
    type InboxNotification,
} from './shared';

type BellNotification = InboxNotification;

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
                skipLoading: true,
            });
            if (!data?.status) return;
            const items = resolveNotificationItems(data);
            const unread = Number(
                data.data?.unread_count
                ?? data.data?.counts?.unread
                ?? items.filter((n) => !n.is_read).length,
            );
            render(items, unread);
        } catch {
            // Non-blocking
        }
    };

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
