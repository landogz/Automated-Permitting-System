import { getApiUser, isAuthenticated, setApiUser, type ApicsUser } from '../../utils/auth';
import { hideModal, showModal } from '../../utils/bootstrap-modal';
import { bindPasswordToggles } from '../../utils/password-toggle';
import { toastError, toastSuccess } from '../../utils/toast';
import { paintUserAvatar } from '../../utils/user-avatar';

function notifyTopbarRefresh(): void {
    window.dispatchEvent(new CustomEvent('apics:user-updated'));
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

function paintAvatarElement(el: HTMLElement, name: string, avatarUrl?: string | null): void {
    paintUserAvatar(el, name, avatarUrl);
}

function syncProfilePreview(): void {
    const nameInput = document.getElementById('profile-name') as HTMLInputElement | null;
    const emailInput = document.getElementById('profile-email') as HTMLInputElement | null;
    const name = nameInput?.value.trim() || getApiUser()?.name || 'Your name';
    const email = emailInput?.value.trim() || getApiUser()?.email || 'email@example.com';
    const avatarUrl = getApiUser()?.avatar_url || null;

    document.querySelectorAll<HTMLElement>('[data-profile-preview-name]').forEach((el) => {
        el.textContent = name;
    });
    document.querySelectorAll<HTMLElement>('[data-profile-preview-email]').forEach((el) => {
        el.textContent = email;
    });
    document.querySelectorAll<HTMLElement>('[data-profile-avatar]').forEach((el) => {
        paintAvatarElement(el, name, avatarUrl);
    });

    const removeBtn = document.getElementById('btn-profile-avatar-remove');
    removeBtn?.classList.toggle('d-none', !avatarUrl);
}

function fillProfileForm(): void {
    const user = getApiUser();
    const name = document.getElementById('profile-name') as HTMLInputElement | null;
    const email = document.getElementById('profile-email') as HTMLInputElement | null;
    const phone = document.getElementById('profile-phone') as HTMLInputElement | null;
    const avatarInput = document.getElementById('profile-avatar-input') as HTMLInputElement | null;
    if (name) {
        name.value = user?.name || '';
    }
    if (email) {
        email.value = user?.email || '';
    }
    if (phone) {
        phone.value = user?.phone || '';
    }
    if (avatarInput) {
        avatarInput.value = '';
    }
    const avatarError = document.querySelector<HTMLElement>('#form-edit-profile [data-error-for="avatar"]');
    if (avatarError) {
        avatarError.textContent = '';
    }
    syncProfilePreview();
}

async function uploadAvatarFile(file: File): Promise<void> {
    if (!file.type.match(/^image\/(jpeg|png|webp)$/)) {
        toastError('Use a JPEG, PNG, or WebP image.');
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        toastError('Profile photo must be 2 MB or smaller.');
        return;
    }

    const formData = new FormData();
    formData.append('avatar', file);

    try {
        const { data } = await window.axios.post('/api/v1/auth/profile/avatar', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        if (!data?.status) {
            throw Object.assign(new Error(data?.message || 'Upload failed'), { response: { data } });
        }
        const updated = data.data as ApicsUser;
        setApiUser({
            ...getApiUser(),
            ...updated,
        });
        notifyTopbarRefresh();
        syncProfilePreview();
        toastSuccess(data.message || 'Profile photo updated');
    } catch (error: any) {
        const form = document.getElementById('form-edit-profile') as HTMLFormElement | null;
        if (form) {
            applyFieldErrors(form, error?.response?.data?.errors);
        }
        toastError(error?.response?.data?.message || error?.response?.data?.errors?.avatar?.[0] || 'Unable to upload photo');
    }
}

async function removeAvatar(): Promise<void> {
    try {
        const { data } = await window.axios.delete('/api/v1/auth/profile/avatar');
        if (!data?.status) {
            throw Object.assign(new Error(data?.message || 'Remove failed'), { response: { data } });
        }
        const updated = data.data as ApicsUser;
        setApiUser({
            ...getApiUser(),
            ...updated,
            avatar_url: null,
        });
        notifyTopbarRefresh();
        syncProfilePreview();
        toastSuccess(data.message || 'Profile photo removed');
    } catch (error: any) {
        toastError(error?.response?.data?.message || 'Unable to remove photo');
    }
}

function bindAvatarControls(): void {
    const pickBtn = document.getElementById('btn-profile-avatar-pick');
    const uploadBtn = document.getElementById('btn-profile-avatar-upload');
    const removeBtn = document.getElementById('btn-profile-avatar-remove');
    const input = document.getElementById('profile-avatar-input') as HTMLInputElement | null;
    if (!input) {
        return;
    }

    const openPicker = (): void => {
        input.click();
    };

    pickBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        openPicker();
    });
    uploadBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        openPicker();
    });
    removeBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        void removeAvatar();
    });

    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file) {
            return;
        }

        // Local preview while upload runs
        const previewUrl = URL.createObjectURL(file);
        document.querySelectorAll<HTMLElement>('[data-profile-avatar]').forEach((el) => {
            paintAvatarElement(el, getApiUser()?.name || 'You', previewUrl);
        });

        void uploadAvatarFile(file).finally(() => {
            URL.revokeObjectURL(previewUrl);
            input.value = '';
        });
    });
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
    bindAvatarControls();
    bindProfileForm();
    bindPasswordForm();
}
