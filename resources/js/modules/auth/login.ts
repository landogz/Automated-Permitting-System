import { setApiToken } from '../../bootstrap';
import { setApiUser } from '../../utils/auth';
import { bindPasswordToggles } from '../../utils/password-toggle';
import { toastError, toastSuccess } from '../../utils/toast';

function firstErrorMessage(error: any, fallback: string): string {
    const errors = error?.response?.data?.errors;
    if (errors?.email?.[0]) {
        return String(errors.email[0]);
    }

    return error?.response?.data?.message || fallback;
}

function resolveRedirect(roles: string[], preferredRedirect?: string): string {
    if (preferredRedirect) {
        return preferredRedirect;
    }

    const isApplicantOnly = roles.length > 0 && roles.every((role) => role === 'applicant');
    if (isApplicantOnly) {
        return '/applications';
    }

    const roleHomes: Record<string, string> = {
        inspector: '/admin/inspections',
        evaluator: '/admin/evaluation-queue',
        receiving: '/admin/evaluation-queue',
        assessor: '/admin/orders-of-payment',
        compliance: '/admin/compliance-notices',
        records: '/admin/logbooks',
        admin: '/admin',
        building_official: '/admin',
    };

    for (const role of roles) {
        if (roleHomes[role]) {
            return roleHomes[role];
        }
    }

    return '/admin';
}

function completeLogin(payload: any, successMessage: string, preferredRedirect?: string): void {
    setApiToken(payload.token);
    setApiUser(payload.user || null);
    toastSuccess(successMessage);

    const roles: string[] = payload.user?.roles || [];
    const redirect = preferredRedirect || payload.redirect || resolveRedirect(roles);
    window.location.href = redirect;
}

async function loginAndRedirect(
    email: string,
    password: string,
    preferredRedirect?: string,
    successMessage = 'Login successful',
): Promise<void> {
    const { data } = await window.axios.post('/api/v1/auth/login', {
        email,
        password,
        device_name: 'web',
    });

    if (!data.status) {
        throw { response: { data: { message: data.message || 'Login failed' } } };
    }

    completeLogin(data.data, successMessage, preferredRedirect);
}

export function initLoginPage(): void {
    const form = document.getElementById('login-form') as HTMLFormElement | null;
    if (!form) {
        return;
    }

    const passwordInput = document.getElementById('password-input') as HTMLInputElement | null;
    bindPasswordToggles(form);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const email = (document.getElementById('email') as HTMLInputElement).value.trim();
        const password = (document.getElementById('password-input') as HTMLInputElement).value;
        const submitBtn = document.getElementById('login-submit') as HTMLButtonElement | null;

        if (submitBtn) submitBtn.disabled = true;

        try {
            await loginAndRedirect(email, password);
        } catch (error: any) {
            toastError(firstErrorMessage(error, 'Login failed'));
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });

    document.querySelectorAll<HTMLButtonElement>('.quick-login-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const email = btn.dataset.email || '';
            const password = btn.dataset.password || '';
            const preferredRedirect = btn.dataset.redirect || undefined;
            const label = btn.querySelector('.fw-semibold')?.textContent || email;

            (document.getElementById('email') as HTMLInputElement).value = email;
            if (passwordInput) {
                passwordInput.value = password;
            }

            const buttons = document.querySelectorAll<HTMLButtonElement>('.quick-login-btn');
            buttons.forEach((b) => {
                b.disabled = true;
            });

            try {
                await loginAndRedirect(email, password, preferredRedirect, `Signed in as ${label}`);
            } catch (error: any) {
                toastError(firstErrorMessage(error, 'Quick login failed'));
                buttons.forEach((b) => {
                    b.disabled = false;
                });
            }
        });
    });
}
