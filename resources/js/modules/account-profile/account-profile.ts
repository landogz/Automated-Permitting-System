import { getApiUser, isAuthenticated, setApiUser, type ApicsUser } from '../../utils/auth';
import { hideModal, showModal } from '../../utils/bootstrap-modal';
import { toastError, toastSuccess } from '../../utils/toast';

function notifyTopbarRefresh(): void {
    window.dispatchEvent(new CustomEvent('apics:user-updated'));
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

function clearFieldErrors(form: HTMLFormElement): void {
    form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
    form.querySelectorAll<HTMLElement>('[data-error-for]').forEach((el) => {
        el.textContent = '';
        el.style.display = '';
    });
}

function applyFieldErrors(form: HTMLFormElement, errors: Record<string, string[] | string> | undefined): void {
    if (!errors) {
        return;
    }

    Object.entries(errors).forEach(([field, messages]) => {
        const input = form.querySelector<HTMLElement>(`[name="${field}"]`);
        const feedback = form.querySelector<HTMLElement>(`[data-error-for="${field}"]`);
        const message = Array.isArray(messages) ? messages[0] : messages;
        input?.classList.add('is-invalid');
        if (feedback && message) {
            feedback.textContent = message;
            feedback.style.display = 'block';
        }
    });
}

function setSubmitting(button: HTMLButtonElement | null, busy: boolean, idleLabel: string, busyLabel = 'Saving…'): void {
    if (!button) {
        return;
    }
    button.disabled = busy;
    const label = button.querySelector('.btn-label');
    if (label) {
        label.textContent = busy ? busyLabel : idleLabel;
    }
}

function bindPasswordToggles(root: ParentNode): void {
    root.querySelectorAll<HTMLButtonElement>('[data-password-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.passwordToggle || '';
            const input = document.getElementById(id) as HTMLInputElement | null;
            if (!input) {
                return;
            }
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = show ? 'ri-eye-off-line align-middle' : 'ri-eye-line align-middle';
            }
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    });
}

function syncProfilePreview(): void {
    const nameInput = document.getElementById('profile-name') as HTMLInputElement | null;
    const emailInput = document.getElementById('profile-email') as HTMLInputElement | null;
    const name = nameInput?.value.trim() || getApiUser()?.name || 'Your name';
    const email = emailInput?.value.trim() || getApiUser()?.email || 'email@example.com';

    document.querySelectorAll<HTMLElement>('[data-profile-preview-name]').forEach((el) => {
        el.textContent = name;
    });
    document.querySelectorAll<HTMLElement>('[data-profile-preview-email]').forEach((el) => {
        el.textContent = email;
    });
    document.querySelectorAll<HTMLElement>('[data-profile-avatar]').forEach((el) => {
        el.textContent = userInitials(name);
    });
}

function fillProfileForm(): void {
    const user = getApiUser();
    const name = document.getElementById('profile-name') as HTMLInputElement | null;
    const email = document.getElementById('profile-email') as HTMLInputElement | null;
    const phone = document.getElementById('profile-phone') as HTMLInputElement | null;
    if (name) {
        name.value = user?.name || '';
    }
    if (email) {
        email.value = user?.email || '';
    }
    if (phone) {
        phone.value = user?.phone || '';
    }
    syncProfilePreview();
}

function evaluatePassword(value: string): {
    score: number;
    label: string;
    level: '' | 'is-weak' | 'is-fair' | 'is-strong';
    rules: Record<string, boolean>;
} {
    const rules = {
        length: value.length >= 8,
        case: /[a-z]/.test(value) && /[A-Z]/.test(value),
        number: /\d/.test(value),
        symbol: /[^A-Za-z0-9]/.test(value),
    };
    const score = Object.values(rules).filter(Boolean).length;

    if (!value) {
        return { score: 0, label: 'Password strength', level: '', rules };
    }
    if (score <= 2) {
        return { score, label: 'Weak — keep going', level: 'is-weak', rules };
    }
    if (score === 3) {
        return { score, label: 'Fair — almost there', level: 'is-fair', rules };
    }
    return { score, label: 'Strong password', level: 'is-strong', rules };
}

function updatePasswordMeter(value: string): void {
    const bar = document.querySelector<HTMLElement>('[data-pass-meter-bar]');
    const label = document.querySelector<HTMLElement>('[data-pass-meter-label]');
    const { label: text, level, rules } = evaluatePassword(value);

    if (bar) {
        bar.classList.remove('is-weak', 'is-fair', 'is-strong');
        if (level) {
            bar.classList.add(level);
        } else {
            bar.style.width = '0%';
        }
        if (level) {
            bar.style.width = '';
        }
    }
    if (label) {
        label.textContent = text;
    }

    document.querySelectorAll<HTMLElement>('[data-pass-rules] [data-rule]').forEach((el) => {
        const key = el.dataset.rule || '';
        const met = Boolean(rules[key as keyof typeof rules]);
        el.classList.toggle('is-met', met);
        const icon = el.querySelector('i');
        if (icon) {
            icon.className = met ? 'ri-checkbox-circle-fill' : 'ri-checkbox-blank-circle-line';
        }
    });
}

function resetPasswordMeter(): void {
    updatePasswordMeter('');
}

async function ensureUserHydrated(): Promise<ApicsUser | null> {
    if (!isAuthenticated()) {
        toastError('Sign in to manage your account');
        return null;
    }

    const existing = getApiUser();
    if (existing?.email) {
        return existing;
    }

    try {
        const { data } = await window.axios.get('/api/v1/auth/me');
        if (data?.status && data?.data) {
            setApiUser(data.data as ApicsUser);
            notifyTopbarRefresh();
            return data.data as ApicsUser;
        }
    } catch {
        toastError('Unable to load your profile');
    }

    return null;
}

function openEditProfile(): void {
    void (async () => {
        const user = await ensureUserHydrated();
        if (!user) {
            return;
        }
        const form = document.getElementById('form-edit-profile') as HTMLFormElement | null;
        if (form) {
            clearFieldErrors(form);
            fillProfileForm();
        }
        showModal('modal-edit-profile');
    })();
}

function openChangePassword(): void {
    void (async () => {
        if (!(await ensureUserHydrated())) {
            return;
        }
        const form = document.getElementById('form-change-password') as HTMLFormElement | null;
        if (form) {
            form.reset();
            clearFieldErrors(form);
            resetPasswordMeter();
        }
        showModal('modal-change-password');
    })();
}

function bindProfileForm(): void {
    const form = document.getElementById('form-edit-profile') as HTMLFormElement | null;
    const submitBtn = document.getElementById('btn-save-profile') as HTMLButtonElement | null;
    if (!form) {
        return;
    }

    form.querySelectorAll<HTMLInputElement>('#profile-name, #profile-email').forEach((input) => {
        input.addEventListener('input', () => syncProfilePreview());
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        void (async () => {
            clearFieldErrors(form);
            setSubmitting(submitBtn, true, 'Save changes');
            try {
                const payload = {
                    name: (document.getElementById('profile-name') as HTMLInputElement).value.trim(),
                    email: (document.getElementById('profile-email') as HTMLInputElement).value.trim(),
                    phone: (document.getElementById('profile-phone') as HTMLInputElement).value.trim() || null,
                };
                const { data } = await window.axios.put('/api/v1/auth/profile', payload);
                if (!data?.status) {
                    throw Object.assign(new Error(data?.message || 'Update failed'), { response: { data } });
                }
                const updated = data.data as ApicsUser;
                setApiUser({
                    ...getApiUser(),
                    ...updated,
                });
                notifyTopbarRefresh();
                hideModal('modal-edit-profile');
                toastSuccess(data.message || 'Profile updated successfully');
            } catch (error: any) {
                const errors = error?.response?.data?.errors;
                applyFieldErrors(form, errors);
                toastError(error?.response?.data?.message || 'Unable to update profile');
            } finally {
                setSubmitting(submitBtn, false, 'Save changes');
            }
        })();
    });
}

function bindPasswordForm(): void {
    const form = document.getElementById('form-change-password') as HTMLFormElement | null;
    const submitBtn = document.getElementById('btn-save-password') as HTMLButtonElement | null;
    const newPassword = document.getElementById('password-new') as HTMLInputElement | null;
    if (!form) {
        return;
    }

    newPassword?.addEventListener('input', () => {
        updatePasswordMeter(newPassword.value);
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        void (async () => {
            clearFieldErrors(form);
            setSubmitting(submitBtn, true, 'Update password', 'Updating…');
            try {
                const payload = {
                    current_password: (document.getElementById('password-current') as HTMLInputElement).value,
                    password: (document.getElementById('password-new') as HTMLInputElement).value,
                    password_confirmation: (document.getElementById('password-confirm') as HTMLInputElement).value,
                };
                const { data } = await window.axios.put('/api/v1/auth/password', payload);
                if (!data?.status) {
                    throw Object.assign(new Error(data?.message || 'Update failed'), { response: { data } });
                }
                form.reset();
                resetPasswordMeter();
                hideModal('modal-change-password');
                toastSuccess(data.message || 'Password changed successfully');
            } catch (error: any) {
                const errors = error?.response?.data?.errors;
                applyFieldErrors(form, errors);
                toastError(error?.response?.data?.message || 'Unable to change password');
            } finally {
                setSubmitting(submitBtn, false, 'Update password');
            }
        })();
    });
}

/**
 * Account menu → Edit profile / Change password modals (Axios SPA).
 */
export function initAccountProfile(): void {
    if (!document.getElementById('modal-edit-profile')) {
        return;
    }

    document.querySelectorAll<HTMLElement>('[data-action="edit-profile"]').forEach((el) => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            openEditProfile();
        });
    });

    document.querySelectorAll<HTMLElement>('[data-action="change-password"]').forEach((el) => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            openChangePassword();
        });
    });

    bindPasswordToggles(document);
    bindProfileForm();
    bindPasswordForm();
}
