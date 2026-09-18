import { escapeHtml } from './bootstrap-modal';

/**
 * Paint circular user avatars (initials fallback or profile photo).
 */
export function userInitials(name: string): string {
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) {
        return 'AP';
    }
    if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase();
    }
    return `${parts[0].charAt(0)}${parts[parts.length - 1].charAt(0)}`.toUpperCase();
}

export function paintUserAvatar(el: HTMLElement, name: string, avatarUrl?: string | null): void {
    const initials = userInitials(name || 'User');
    const hasPhoto = Boolean(avatarUrl);

    el.classList.toggle('has-photo', hasPhoto);
    el.style.backgroundImage = '';
    el.setAttribute('title', name || 'User');
    el.setAttribute('aria-label', name || 'User');

    let initialsEl = el.querySelector<HTMLElement>('[data-avatar-initials]');
    if (!initialsEl) {
        el.textContent = '';
        initialsEl = document.createElement('span');
        initialsEl.dataset.avatarInitials = '1';
        el.appendChild(initialsEl);
    }
    initialsEl.textContent = initials;

    let img = el.querySelector<HTMLImageElement>('img[data-avatar-img]');
    if (hasPhoto && avatarUrl) {
        if (!img) {
            img = document.createElement('img');
            img.dataset.avatarImg = '1';
            img.alt = '';
            img.decoding = 'async';
            img.loading = 'lazy';
            el.appendChild(img);
        }
        img.src = avatarUrl;
        img.hidden = false;
        initialsEl.hidden = true;
    } else {
        if (img) {
            img.remove();
        }
        initialsEl.hidden = false;
    }
}

/** HTML chip for DataTables / static lists (initials or photo). */
export function userAvatarHtml(
    name: string,
    avatarUrl?: string | null,
    className = 'apics-user-avatar',
): string {
    const label = name || 'User';
    const initials = userInitials(label);
    if (avatarUrl) {
        return `<span class="${escapeHtml(className)} has-photo" title="${escapeHtml(label)}">
            <img src="${escapeHtml(avatarUrl)}" alt="" decoding="async" loading="lazy" data-avatar-img>
            <span data-avatar-initials hidden>${escapeHtml(initials)}</span>
        </span>`;
    }

    return `<span class="${escapeHtml(className)}" title="${escapeHtml(label)}">
        <span data-avatar-initials>${escapeHtml(initials)}</span>
    </span>`;
}
