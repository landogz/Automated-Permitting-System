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
const ADMIN_PAGE_PERMISSIONS: Array<{ match: RegExp; permissions: string[]; requireRoles?: string[] }> = [
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
    { match: /^\/admin\/audit\/?$/, permissions: ['audit.view'], requireRoles: ['admin'] },
    { match: /^\/admin\/project-plan\/?$/, permissions: ['audit.view'] },
    { match: /^\/admin\/?$/, permissions: [] },
];

function isOfficeUser(): boolean {
    return OFFICE_ROLES.some((role) => hasRole(role));
}

/**
 * data-nav-roles supports comma-separated: guest, applicant, admin
 * Office staff share the admin nav group; data-nav-permissions further filters items.
 * Only true applicants get the applicant nav group — never office / unknown roles.
 */
function currentNavRoles(): string[] {
    if (!isAuthenticated()) {
        return ['guest'];
    }

    if (isOfficeUser()) {
        return ['admin'];
    }

    if (hasRole('applicant')) {
        return ['applicant'];
    }

    return [];
}

function parseList(value: string | undefined): string[] {
    return (value || '')
        .split(',')
        .map((part) => part.trim())
        .filter(Boolean);
}

function applyNavVisibility(): void {
    const roles = currentNavRoles();
    const isApplicantOnly = isAuthenticated() && hasRole('applicant') && !isOfficeUser();

    document.querySelectorAll<HTMLElement>('[data-nav-roles]').forEach((el) => {
        const allowedRoles = parseList(el.dataset.navRoles);
        const requiredPermissions = parseList(el.dataset.navPermissions);
        const requiredSpatieRoles = parseList(el.dataset.navRequireRole);
        const roleOk = allowedRoles.some((role) => roles.includes(role));
        const permissionOk = requiredPermissions.length === 0 || hasAnyPermission(requiredPermissions);
        const spatieRoleOk = requiredSpatieRoles.length === 0 || requiredSpatieRoles.some((role) => hasRole(role));

        // "My Applications" and other applicant-only chrome never show for office roles,
        // even if the user also happens to have the applicant Spatie role.
        const applicantRestricted = allowedRoles.includes('applicant') && !allowedRoles.includes('admin') && !allowedRoles.includes('guest');
        const applicantOk = !applicantRestricted || isApplicantOnly;

        el.classList.toggle('d-none', !(roleOk && permissionOk && spatieRoleOk && applicantOk));
    });

    // Hide account-menu body when no role links are visible (avoids empty gap).
    document.querySelectorAll<HTMLElement>('[data-account-menu-body]').forEach((body) => {
        const hasVisible = Array.from(body.querySelectorAll<HTMLElement>('[data-nav-roles]')).some(
            (item) => !item.classList.contains('d-none'),
        );
        body.classList.toggle('d-none', !hasVisible);
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

function titleCaseRole(role: string): string {
    const labels: Record<string, string> = {
        admin: 'Administrator',
        building_official: 'Building Official',
        receiving: 'Receiving',
        evaluator: 'Evaluator',
        inspector: 'Inspector',
        assessor: 'Assessor',
        compliance: 'Compliance',
        records: 'Records',
        staff: 'Staff',
        applicant: 'Applicant',
    };

    if (labels[role]) {
        return labels[role];
    }

    return role
        .split(/[_\s-]+/)
        .filter(Boolean)
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}

function userInitials(name: string): string {
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) {
        return 'AP';
    }
    if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase();
    }

    return `${parts[0].charAt(0)}${parts[parts.length - 1].charAt(0)}`.toUpperCase();
}

function applyTopbarUser(): void {
    const user = getApiUser();
    const nameEls = document.querySelectorAll<HTMLElement>('.user-name-text');
    const subEls = document.querySelectorAll<HTMLElement>('.user-name-sub-text');
    const emailEl = document.querySelector<HTMLElement>('[data-user-email]');
    const avatarEls = document.querySelectorAll<HTMLElement>('[data-user-avatar], [data-user-avatar-menu]');
    const logoutBtn = document.getElementById('btn-logout');
    const loginLink = document.getElementById('btn-signin-link');
    const trigger = document.getElementById('page-header-user-dropdown');

    const displayName = user?.name || (isAuthenticated() ? 'APICS User' : 'Guest');
    let roleLabel = 'Not signed in';

    if (isAuthenticated()) {
        if (isOfficeUser()) {
            const role = (user?.roles || []).find((r) => OFFICE_ROLES.includes(r)) || 'staff';
            roleLabel = titleCaseRole(role);
        } else {
            roleLabel = 'Applicant';
        }
    }

    nameEls.forEach((el) => {
        el.textContent = displayName;
    });

    subEls.forEach((el) => {
        el.textContent = roleLabel;
    });

    if (emailEl) {
        emailEl.textContent = isAuthenticated()
            ? (user?.email || 'Signed in')
            : 'Sign in to continue';
    }

    const initials = userInitials(displayName);
    avatarEls.forEach((el) => {
        el.textContent = initials;
        el.setAttribute('title', displayName);
    });

    trigger?.classList.toggle('is-authenticated', isAuthenticated());
    logoutBtn?.classList.toggle('d-none', !isAuthenticated());
    loginLink?.classList.toggle('d-none', isAuthenticated());
    loginLink?.toggleAttribute('hidden', isAuthenticated());
    loginLink?.setAttribute('aria-hidden', isAuthenticated() ? 'true' : 'false');
}

/** Refresh topbar identity after profile updates. */
export function refreshTopbarUser(): void {
    applyTopbarUser();
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

function requireRolesForAdminPath(pathname: string): string[] {
    const rule = ADMIN_PAGE_PERMISSIONS.find((entry) => entry.match.test(pathname));
    return rule?.requireRoles ?? [];
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

    const candidates: Array<{ path: string; permissions: string[]; requireRoles?: string[] }> = [
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
        { path: '/admin/project-plan', permissions: ['audit.view'] },
        { path: '/admin/audit', permissions: ['audit.view'], requireRoles: ['admin'] },
    ];

    const match = candidates.find((item) => {
        const roleOk = !item.requireRoles?.length || item.requireRoles.some((role) => hasRole(role));
        return roleOk && hasAnyPermission(item.permissions);
    });
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

    const requiredRoles = requireRolesForAdminPath(window.location.pathname);
    const roleOk = requiredRoles.length === 0 || requiredRoles.some((role) => hasRole(role));
    const permissionOk = required.length === 0 || hasAnyPermission(required);

    if (!roleOk || !permissionOk) {
        toastError('You do not have access to this page');
        window.setTimeout(() => {
            window.location.href = firstAccessibleAdminPath();
        }, 600);
    }
}

/**
 * My Applications is applicant-only. Office staff use Evaluation Queue / ops modules.
 * Guests may land here and see the in-page sign-in gate.
 */
function guardApplicantApplicationsPage(): void {
    if (!/^\/applications\/?$/.test(window.location.pathname)) {
        return;
    }

    if (!isAuthenticated()) {
        return;
    }

    if (isOfficeUser() || !hasRole('applicant')) {
        toastError('My Applications is for applicants only');
        window.setTimeout(() => {
            window.location.href = isOfficeUser() ? firstAccessibleAdminPath() : '/';
        }, 600);
    }
}

export function initAppShell(): void {
    window.addEventListener('apics:user-updated', () => {
        applyTopbarUser();
    });

    void (async () => {
        await hydrateSession();
        applyNavVisibility();
        applyOpsStepLocks();
        applyTopbarUser();
        guardAdminPages();
        guardApplicantApplicationsPage();
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
