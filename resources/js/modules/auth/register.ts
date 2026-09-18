import { bindPasswordToggles } from '../../utils/password-toggle';
import { toastError, toastSuccess } from '../../utils/toast';

function firstValidationMessage(error: any): string {
    const errors = error?.response?.data?.errors;
    if (errors && typeof errors === 'object') {
        const first = Object.values(errors)[0];
        if (Array.isArray(first) && first[0]) {
            return String(first[0]);
        }
    }
    return error?.response?.data?.message || 'Registration failed';
}

function normalizePhone(raw: string): string | null {
    const digits = raw.replace(/\D/g, '');
    if (!digits) {
        return null;
    }

    if (digits.startsWith('63') && digits.length >= 12) {
        return `0${digits.slice(2, 12)}`;
    }

    if (digits.startsWith('09') && digits.length >= 11) {
        return digits.slice(0, 11);
    }

    if (digits.length === 9) {
        return `09${digits}`;
    }

    if (digits.length === 10 && digits.startsWith('9')) {
        return `0${digits}`;
    }

    return digits.length >= 11 ? digits.slice(0, 11) : `09${digits}`;
}

export function initRegisterPage(): void {
    const form = document.getElementById('register-form') as HTMLFormElement | null;
    if (!form) {
        return;
    }

    const submitBtn = document.getElementById('register-submit') as HTMLButtonElement | null;
    const phoneInput = document.getElementById('phone') as HTMLInputElement | null;

    bindPasswordToggles(form);

    phoneInput?.addEventListener('input', () => {
        phoneInput.value = phoneInput.value.replace(/\D/g, '').slice(0, 9);
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const name = (document.getElementById('name') as HTMLInputElement).value.trim();
        const email = (document.getElementById('email') as HTMLInputElement).value.trim();
        const phoneRaw = (document.getElementById('phone') as HTMLInputElement).value.trim();
        const password = (document.getElementById('password') as HTMLInputElement).value;
        const passwordConfirmation = (document.getElementById('password_confirmation') as HTMLInputElement).value;

        if (submitBtn) {
            submitBtn.disabled = true;
        }

        try {
            const { data } = await window.axios.post('/api/v1/auth/register', {
                name,
                email,
                phone: normalizePhone(phoneRaw),
                password,
                password_confirmation: passwordConfirmation,
            });

            if (!data.status) {
                toastError(data.message || 'Registration failed');
                return;
            }

            toastSuccess(data.message || 'Registration submitted');
            form.reset();
            window.setTimeout(() => {
                window.location.href = '/login';
            }, 1200);
        } catch (error: any) {
            toastError(firstValidationMessage(error));
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        }
    });
}
