import {
    getApiUser,
    hasAnyPermission,
    hasPermission,
    hasRole,
    isAuthenticated,
    logout,
    setApiUser,
    type ApicsUser,
} from '../../utils/auth';
import { toastError, toastSuccess } from '../../utils/toast';

const OFFICE_ROLES = [
    'admin',
    'building_official',
    'receiving',
    'evaluator',
    'inspector',
    'assessor',
    'compliance',
    'records',
    'staff',
];

/**
 * Admin page → required Spatie permissions (any one grants access).
 * Empty array = any authenticated office user.
 */
const ADMIN_PAGE_PERMISSIONS: Array<{ match: RegExp; permissions: string[] }> = [
    { match: /^\/admin\/registrations\/?$/, permissions: ['users.manage'] },
    { match: /^\/admin\/users\/?$/, permissions: ['users.manage'] },
    { match: /^\/admin\/departments\/?$/, permissions: ['departments.manage'] },
    { match: /^\/admin\/forms\/?$/, permissions: ['forms.manage'] },
    { match: /^\/admin\/classification-rules\/?$/, permissions: ['workflow.manage'] },
    { match: /^\/admin\/routing-templates\/?$/, permissions: ['workflow.manage'] },
    { match: /^\/admin\/fee-rules\/?$/, permissions: ['workflow.manage'] },
    { match: /^\/admin\/evaluation-queue\/?$/, permissions: ['evaluations.manage'] },
    { match: /^\/admin\/inspections\/?$/, permissions: ['inspections.manage'] },
    { match: /^\/admin\/orders-of-payment\/?$/, permissions: ['fees.manage'] },
    { match: /^\/admin\/compliance-notices\/?$/, permissions: ['compliance.manage'] },
    { match: /^\/admin\/logbooks(\/|$)/, permissions: ['records.manage'] },
    { match: /^\/admin\/archives\/?$/, permissions: ['records.manage'] },
    { match: /^\/admin\/notifications\/?$/, permissions: ['applications.manage'] },
    { match: /^\/admin\/audit\/?$/, permissions: ['audit.view'] },
    { match: /^\/admin\/?$/, permissions: [] },
];

function isOfficeUser(): boolean {
    return OFFICE_ROLES.some((role) => hasRole(role));
}

/**
 * data-nav-roles supports comma-separated: guest, applicant, admin
 * Office staff share the admin nav group; data-nav-permissions further filters items.
 */
function currentNavRoles(): string[] {
    if (!isAuthenticated()) {
        return ['guest'];
    }

    if (isOfficeUser()) {
        return ['admin'];
    }

    return ['applicant'];
}

function parseList(value: string | undefined): string[] {
    return (value || '')
        .split(',')
        .map((part) => part.trim())
        .filter(Boolean);
}

function applyNavVisibility(): void {
    const roles = currentNavRoles();

    document.querySelectorAll<HTMLElement>('[data-nav-roles]').forEach((el) => {
        const allowedRoles = parseList(el.dataset.navRoles);
        const requiredPermissions = parseList(el.dataset.navPermissions);
        const roleOk = allowedRoles.some((role) => roles.includes(role));
        const permissionOk = requiredPermissions.length === 0 || hasAnyPermission(requiredPermissions);
        el.classList.toggle('d-none', !(roleOk && permissionOk));
    });

    // Hide section titles when no sibling nav items in that group remain visible.
    const visibleTitles: HTMLElement[] = [];
    document.querySelectorAll<HTMLElement>('.menu-title[data-nav-roles]').forEach((title) => {
        let sibling = title.nextElementSibling;
        let hasVisibleItem = false;

        while (sibling && !sibling.classList.contains('menu-title')) {
            if (sibling.classList.contains('nav-item') && !sibling.classList.contains('d-none')) {
                hasVisibleItem = true;
                break;
            }
            sibling = sibling.nextElementSibling;
        }

        title.classList.toggle('d-none', !hasVisibleItem);
        title.classList.remove('is-first-visible');
        if (hasVisibleItem) {
            visibleTitles.push(title);
        }
    });

    visibleTitles[0]?.classList.add('is-first-visible');
}

function applyTopbarUser(): void {
    const user = getApiUser();
    const nameEl = document.querySelector<HTMLElement>('.user-name-text');
    const subEl = document.querySelector<HTMLElement>('.user-name-sub-text');
    const logoutBtn = document.getElementById('btn-logout');
    const loginLink = document.getElementById('btn-signin-link');

    if (nameEl) {
        nameEl.textContent = user?.name || (isAuthenticated() ? 'APICS User' : 'Guest');
    }

    if (subEl) {
        if (!isAuthenticated()) {
            subEl.textContent = 'Not signed in';
        } else if (isOfficeUser()) {
            const role = (user?.roles || []).find((r) => OFFICE_ROLES.includes(r)) || 'Staff';
            subEl.textContent = role.replaceAll('_', ' ');
        } else {
            subEl.textContent = 'Applicant';
        }
    }

    logoutBtn?.classList.toggle('d-none', !isAuthenticated());
    loginLink?.classList.toggle('d-none', isAuthenticated());
}

async function hydrateSession(): Promise<void> {
    if (!isAuthenticated()) {
        return;
    }

    // Always refresh roles/permissions so reseeded privileges apply without stale localStorage.
    try {
        const { data } = await window.axios.get('/api/v1/auth/me');
        if (data?.status && data?.data) {
            setApiUser(data.data as ApicsUser);
        }
    } catch {
        await logout();
    }
}

async function handleLogout(event: Event): Promise<void> {
    event.preventDefault();
    try {
        await logout();
        toastSuccess('Signed out successfully');
        window.setTimeout(() => {
            window.location.href = '/login';
        }, 400);
    } catch (error: any) {
        toastError(error?.response?.data?.message || 'Sign out failed');
        window.location.href = '/login';
    }
}

function permissionsForAdminPath(pathname: string): string[] | null {
    const rule = ADMIN_PAGE_PERMISSIONS.find((entry) => entry.match.test(pathname));
    return rule ? rule.permissions : null;
}

function firstAccessibleAdminPath(): string {
    const roleHomes: Array<{ role: string; path: string }> = [
        { role: 'inspector', path: '/admin/inspections' },
        { role: 'evaluator', path: '/admin/evaluation-queue' },
        { role: 'receiving', path: '/admin/evaluation-queue' },
        { role: 'assessor', path: '/admin/orders-of-payment' },
        { role: 'compliance', path: '/admin/compliance-notices' },
        { role: 'records', path: '/admin/logbooks' },
        { role: 'admin', path: '/admin' },
        { role: 'building_official', path: '/admin' },
    ];

    for (const entry of roleHomes) {
        if (hasRole(entry.role)) {
            return entry.path;
        }
    }

    const candidates: Array<{ path: string; permissions: string[] }> = [
        { path: '/admin', permissions: [] },
        { path: '/admin/evaluation-queue', permissions: ['evaluations.manage'] },
        { path: '/admin/inspections', permissions: ['inspections.manage'] },
        { path: '/admin/orders-of-payment', permissions: ['fees.manage'] },
        { path: '/admin/compliance-notices', permissions: ['compliance.manage'] },
        { path: '/admin/logbooks', permissions: ['records.manage'] },
        { path: '/admin/archives', permissions: ['records.manage'] },
        { path: '/admin/notifications', permissions: ['applications.manage'] },
        { path: '/admin/registrations', permissions: ['users.manage'] },
        { path: '/admin/users', permissions: ['users.manage'] },
        { path: '/admin/classification-rules', permissions: ['workflow.manage'] },
        { path: '/admin/routing-templates', permissions: ['workflow.manage'] },
        { path: '/admin/fee-rules', permissions: ['workflow.manage'] },
        { path: '/admin/departments', permissions: ['departments.manage'] },
        { path: '/admin/forms', permissions: ['forms.manage'] },
        { path: '/admin/audit', permissions: ['audit.view'] },
    ];

    const match = candidates.find((item) => hasAnyPermission(item.permissions));
    return match?.path || '/applications';
}

function applyOpsStepLocks(): void {
    document.querySelectorAll<HTMLAnchorElement>('[data-ops-permission]').forEach((link) => {
        const permission = link.dataset.opsPermission || '';
        const allowed = !permission || hasPermission(permission);

        link.classList.toggle('is-locked', !allowed);
        if (allowed) {
            link.removeAttribute('aria-disabled');
            link.removeAttribute('title');
            link.onclick = null;
            return;
        }

        link.setAttribute('aria-disabled', 'true');
        link.title = 'Assigned to another office role';
        link.onclick = (event) => {
            event.preventDefault();
            toastError('This Operations step is handled by another office role.');
        };
    });
}

function setNavBadge(key: string, count: number): void {
    document.querySelectorAll<HTMLElement>(`[data-nav-badge="${key}"]`).forEach((badge) => {
        const safe = Number.isFinite(count) ? Math.max(0, Math.floor(count)) : 0;
        badge.textContent = safe > 99 ? '99+' : String(safe);
        badge.classList.toggle('is-empty', safe <= 0);
        badge.hidden = safe <= 0;
    });
}

async function loadNavQueueBadges(): Promise<void> {
    if (!isAuthenticated() || !isOfficeUser()) {
        return;
    }

    if (!document.querySelector('[data-nav-badge]')) {
        return;
    }

    try {
        const { data } = await window.axios.get('/api/v1/staff/dashboard-stats');
        if (!data?.status || !data?.data) {
            return;
        }

        const stats = data.data as {
            pending_registrations?: number;
            evaluation_queue?: number;
            applications?: { submitted?: number; under_evaluation?: number };
            compliance?: { open_notices?: number };
        };

        const evaluationQueue =
            typeof stats.evaluation_queue === 'number'
                ? stats.evaluation_queue
                : (stats.applications?.submitted ?? 0) + (stats.applications?.under_evaluation ?? 0);

        setNavBadge('pending_registrations', stats.pending_registrations ?? 0);
        setNavBadge('evaluation_queue', evaluationQueue);
        setNavBadge('open_compliance', stats.compliance?.open_notices ?? 0);
    } catch {
        // Non-blocking: badge counts are polish, not required for navigation.
    }
}

function guardAdminPages(): void {
    if (!window.location.pathname.startsWith('/admin')) {
        return;
    }

    if (!isAuthenticated() || !isOfficeUser()) {
        toastError('Office staff sign-in required');
        window.setTimeout(() => {
            window.location.href = '/login';
        }, 600);
        return;
    }

    const required = permissionsForAdminPath(window.location.pathname);
    if (required === null) {
        return;
    }

    if (required.length > 0 && !hasAnyPermission(required)) {
        toastError('You do not have access to this page');
        window.setTimeout(() => {
            window.location.href = firstAccessibleAdminPath();
        }, 600);
    }
}

export function initAppShell(): void {
    void (async () => {
        await hydrateSession();
        applyNavVisibility();
        applyOpsStepLocks();
        applyTopbarUser();
        guardAdminPages();
        await loadNavQueueBadges();

        try {
            const { initNotificationBell } = await import('../notifications/bell');
            initNotificationBell();
        } catch (error) {
            console.error('[APICS] Failed to boot notification bell', error);
        }
    })();

    document.getElementById('btn-logout')?.addEventListener('click', (event) => {
        void handleLogout(event);
    });
}

// Exported for unit-style checks in the browser console during QA.
export const __navHelpers = {
    hasPermission,
    hasAnyPermission,
    permissionsForAdminPath,
};
