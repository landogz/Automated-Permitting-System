import axios from 'axios';

declare global {
    interface Window {
        axios: typeof axios;
    }
}

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content') || '';
}

const apiToken = localStorage.getItem('apics_token');
if (apiToken) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${apiToken}`;
}

export function setApiToken(value: string | null): void {
    if (value) {
        localStorage.setItem('apics_token', value);
        window.axios.defaults.headers.common['Authorization'] = `Bearer ${value}`;
    } else {
        localStorage.removeItem('apics_token');
        delete window.axios.defaults.headers.common['Authorization'];
    }
}
