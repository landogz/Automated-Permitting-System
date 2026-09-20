/**
 * Open a government print HTML page that requires Sanctum Bearer auth.
 * Fetches with Axios (sends Authorization) then writes into a print window.
 */
import { toastError } from '../toast';

export async function openAuthenticatedPrint(url: string | undefined | null): Promise<void> {
    if (!url) {
        toastError('Print link unavailable. Reload and try again.');
        return;
    }

    try {
        const { data } = await window.axios.get<string>(url, {
            responseType: 'text',
            headers: {
                Accept: 'text/html, application/xhtml+xml',
            },
            // Print HTML is not JSON; skip global loading overlay spam for UX.
            // @ts-expect-error skipLoading is read by our Axios interceptor
            skipLoading: true,
        });

        const html = typeof data === 'string' ? data : String(data ?? '');
        if (!html.trim()) {
            toastError('Print document was empty.');
            return;
        }

        // Do not use "noopener" in the feature string — browsers return null for the window handle.
        const win = window.open('', '_blank', 'width=960,height=780');
        if (!win) {
            toastError('Pop-up blocked. Allow pop-ups for APICS to print.');
            return;
        }
        win.opener = null;
        win.document.open();
        win.document.write(html);
        win.document.close();
    } catch (error: any) {
        const status = error?.response?.status;
        if (status === 401) {
            toastError('Sign in again to open official prints.');
            return;
        }
        if (status === 403) {
            toastError('You are not allowed to open this print.');
            return;
        }
        toastError(error?.response?.data?.message || 'Unable to open print document.');
    }
}
