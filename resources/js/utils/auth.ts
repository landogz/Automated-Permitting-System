import { setApiToken } from '../bootstrap';

export type ApicsUser = {
    uuid?: string;
    name?: string;
    email?: string;
    roles?: string[];
    permissions?: string[];
    approval_status?: string;
};

const USER_KEY = 'apics_user';

export function setApiUser(user: ApicsUser | null): void {
    if (user) {
        localStorage.setItem(USER_KEY, JSON.stringify(user));
    } else {
        localStorage.removeItem(USER_KEY);
    }
}

export function getApiUser(): ApicsUser | null {
    const raw = localStorage.getItem(USER_KEY);
    if (!raw) {
        return null;
    }

    try {
        return JSON.parse(raw) as ApicsUser;
    } catch {
        return null;
    }
}

export function getApiToken(): string | null {
    return localStorage.getItem('apics_token');
}

export function isAuthenticated(): boolean {
    return Boolean(getApiToken());
}

export function hasRole(role: string): boolean {
    const user = getApiUser();
    return Boolean(user?.roles?.includes(role));
}

export function hasPermission(permission: string): boolean {
    const user = getApiUser();
    return Boolean(user?.permissions?.includes(permission));
}

export function hasAnyPermission(permissions: string[]): boolean {
    if (permissions.length === 0) {
        return true;
    }

    return permissions.some((permission) => hasPermission(permission));
}

export function isAdmin(): boolean {
    return hasRole('admin');
}

export async function logout(): Promise<void> {
    try {
        if (getApiToken()) {
            await window.axios.post('/api/v1/auth/logout');
        }
    } catch {
        // Always clear local session even if the API call fails (expired token, etc.).
    } finally {
        setApiToken(null);
        setApiUser(null);
    }
}
