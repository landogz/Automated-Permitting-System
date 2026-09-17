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

export function initRegisterPage(): void {
    const form = document.getElementById('register-form') as HTMLFormElement | null;
    if (!form) {
        return;
    }

    const submitBtn = document.getElementById('register-submit') as HTMLButtonElement | null;
    const addon = document.getElementById('password-addon');
    const passwordInput = document.getElementById('password') as HTMLInputElement | null;

    addon?.addEventListener('click', () => {
        if (!passwordInput) return;
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const name = (document.getElementById('name') as HTMLInputElement).value.trim();
        const email = (document.getElementById('email') as HTMLInputElement).value.trim();
        const phone = (document.getElementById('phone') as HTMLInputElement).value.trim();
        const password = (document.getElementById('password') as HTMLInputElement).value;
        const passwordConfirmation = (document.getElementById('password_confirmation') as HTMLInputElement).value;

        if (submitBtn) {
            submitBtn.disabled = true;
        }

        try {
            const { data } = await window.axios.post('/api/v1/auth/register', {
                name,
                email,
                phone: phone || null,
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
