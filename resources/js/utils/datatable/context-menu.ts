export type ContextMenuItem = {
    id: string;
    label: string;
    danger?: boolean;
    dividerBefore?: boolean;
    disabled?: boolean;
    onClick: () => void | Promise<void>;
};

let menuEl: HTMLDivElement | null = null;
let dismissBound = false;

function ensureMenu(): HTMLDivElement {
    if (menuEl) {
        return menuEl;
    }

    menuEl = document.createElement('div');
    menuEl.className = 'apics-dt-context-menu dropdown-menu shadow';
    menuEl.setAttribute('role', 'menu');
    menuEl.setAttribute('aria-label', 'Row actions');
    document.body.appendChild(menuEl);

    if (!dismissBound) {
        dismissBound = true;
        document.addEventListener('click', () => hideContextMenu());
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                hideContextMenu();
            }
        });
        window.addEventListener('scroll', () => hideContextMenu(), true);
        window.addEventListener('resize', () => hideContextMenu());
    }

    return menuEl;
}

export function hideContextMenu(): void {
    if (!menuEl) {
        return;
    }
    menuEl.classList.remove('show');
    menuEl.style.display = 'none';
    menuEl.innerHTML = '';
}

export function showContextMenu(x: number, y: number, items: ContextMenuItem[]): void {
    const menu = ensureMenu();
    menu.innerHTML = '';

    if (!items.length) {
        hideContextMenu();
        return;
    }

    items.forEach((item) => {
        if (item.dividerBefore) {
            const hr = document.createElement('div');
            hr.className = 'dropdown-divider';
            menu.appendChild(hr);
        }

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = `dropdown-item ${item.danger ? 'text-danger' : ''}`.trim();
        btn.textContent = item.label;
        btn.setAttribute('role', 'menuitem');
        btn.disabled = Boolean(item.disabled);
        btn.addEventListener('click', async (event) => {
            event.preventDefault();
            event.stopPropagation();
            hideContextMenu();
            if (!item.disabled) {
                await item.onClick();
            }
        });
        menu.appendChild(btn);
    });

    menu.style.display = 'block';
    menu.classList.add('show');

    const pad = 8;
    const rect = menu.getBoundingClientRect();
    let left = x;
    let top = y;
    if (left + rect.width > window.innerWidth - pad) {
        left = Math.max(pad, window.innerWidth - rect.width - pad);
    }
    if (top + rect.height > window.innerHeight - pad) {
        top = Math.max(pad, window.innerHeight - rect.height - pad);
    }

    menu.style.left = `${left}px`;
    menu.style.top = `${top}px`;
}
