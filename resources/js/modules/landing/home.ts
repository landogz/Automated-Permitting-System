import {
    getApiUser,
    hasRole,
    isAuthenticated,
    logout,
    setApiUser,
    type ApicsUser,
} from '../../utils/auth';
import { toastError, toastSuccess } from '../../utils/toast';

const OFFICE_ROLES = [
    'admin',
    'building_official',
    'receiving',
    'evaluator',
    'inspector',
    'assessor',
    'compliance',
    'records',
    'staff',
];

type LandingAudience = 'guest' | 'applicant' | 'admin';

function prefersReducedMotion(): boolean {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function currentAudience(): LandingAudience {
    if (!isAuthenticated()) {
        return 'guest';
    }

    if (OFFICE_ROLES.some((role) => hasRole(role))) {
        return 'admin';
    }

    return 'applicant';
}

async function ensureLandingSession(): Promise<void> {
    if (!isAuthenticated()) {
        return;
    }

    const existing = getApiUser();
    if (existing?.roles?.length) {
        return;
    }

    try {
        const { data } = await window.axios.get('/api/v1/auth/me');
        if (data?.status && data?.data) {
            setApiUser(data.data as ApicsUser);
        }
    } catch {
        // Keep guest chrome if token is stale; shell logout handles admin pages.
    }
}

function applyLandingAuthVisibility(): void {
    const audience = currentAudience();
    const user = getApiUser();

    document.querySelectorAll<HTMLElement>('[data-auth-visible]').forEach((el) => {
        const allowed = (el.dataset.authVisible || '')
            .split(',')
            .map((part) => part.trim())
            .filter(Boolean);
        const show = allowed.includes(audience);
        el.classList.toggle('d-none', !show);

        // Avoid Bootstrap d-*-inline !important fighting inline display:none.
        if (el.hasAttribute('data-auth-user-chip')) {
            el.classList.toggle('d-lg-inline', show);
            el.style.display = '';
        } else {
            el.style.display = show ? '' : 'none';
        }
    });

    document.querySelectorAll<HTMLElement>('[data-auth-name]').forEach((el) => {
        el.textContent = audience === 'guest' ? '' : user?.name || '';
    });

    document.querySelectorAll<HTMLElement>('[data-auth-role]').forEach((el) => {
        if (audience === 'guest') {
            el.textContent = '';
            return;
        }
        if (audience === 'admin') {
            const role = (user?.roles || []).find((r) => OFFICE_ROLES.includes(r)) || 'Staff';
            el.textContent = role.replaceAll('_', ' ');
            return;
        }
        el.textContent = 'Applicant';
    });

    document.querySelectorAll<HTMLElement>('[data-auth-sep]').forEach((el) => {
        el.classList.toggle('d-none', audience === 'guest');
    });
}

function fixLandingClickTargets(): void {
    document.querySelectorAll<HTMLElement>('.hero-section > .bg-overlay, .hero-section .hero-shape-svg').forEach((el) => {
        el.style.pointerEvents = 'none';
    });

    document.querySelectorAll<HTMLElement>('.hero-section > .container').forEach((el) => {
        el.style.position = 'relative';
        el.style.zIndex = '2';
    });

    const overlay = document.querySelector<HTMLElement>('.layout-wrapper.landing > .vertical-overlay');
    if (overlay) {
        const syncOverlay = (): void => {
            const menu = document.getElementById('navbarSupportedContent');
            const open = Boolean(menu?.classList.contains('show'));
            overlay.style.pointerEvents = open ? 'auto' : 'none';
            if (!open) {
                overlay.style.display = 'none';
            }
        };
        syncOverlay();
        document.getElementById('navbarSupportedContent')?.addEventListener('shown.bs.collapse', syncOverlay);
        document.getElementById('navbarSupportedContent')?.addEventListener('hidden.bs.collapse', syncOverlay);
    }
}

async function handleLandingLogout(event: Event): Promise<void> {
    event.preventDefault();
    try {
        await logout();
        toastSuccess('Signed out successfully');
        window.setTimeout(() => {
            window.location.href = '/';
        }, 400);
    } catch (error: any) {
        toastError(error?.response?.data?.message || 'Sign out failed');
    }
}

function initScrollReveals(root: HTMLElement): void {
    const reveals = root.querySelectorAll<HTMLElement>('.landing-reveal');
    if (reveals.length === 0) {
        return;
    }

    if (prefersReducedMotion() || typeof IntersectionObserver === 'undefined') {
        reveals.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    // Opt-in: hide until visible (content stays readable without JS).
    root.dataset.motion = 'on';

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        },
        {
            rootMargin: '0px 0px -4% 0px',
            threshold: 0.08,
        },
    );

    // Stagger within each section group so siblings cascade.
    const groups = new Map<Element, HTMLElement[]>();
    reveals.forEach((el) => {
        const parent = el.parentElement || root;
        const list = groups.get(parent) || [];
        list.push(el);
        groups.set(parent, list);
    });

    groups.forEach((items) => {
        items.forEach((el, index) => {
            el.style.setProperty('--reveal-delay', `${Math.min(index, 7) * 70}ms`);
        });
    });

    // After layout: mark already-in-view items, observe the rest.
    window.requestAnimationFrame(() => {
        const viewportBottom = window.innerHeight * 0.96;
        reveals.forEach((el) => {
            if (el.offsetParent === null && getComputedStyle(el).display === 'none') {
                el.classList.add('is-visible');
                return;
            }
            const rect = el.getBoundingClientRect();
            if (rect.top < viewportBottom && rect.bottom > 0) {
                el.classList.add('is-visible');
                return;
            }
            observer.observe(el);
        });
    });
}

function initNavScroll(): void {
    const nav = document.getElementById('navbar');
    if (!nav) {
        return;
    }

    const sync = (): void => {
        nav.classList.toggle('is-scrolled', window.scrollY > 24);
    };

    sync();
    window.addEventListener('scroll', sync, { passive: true });
}

function initHeroParallax(): void {
    const shape = document.querySelector<HTMLElement>('.hero-section .hero-shape-svg');
    if (!shape || prefersReducedMotion()) {
        return;
    }

    let ticking = false;
    const update = (): void => {
        const y = Math.min(window.scrollY, 400) * 0.08;
        shape.style.setProperty('--hero-parallax', `${y.toFixed(1)}px`);
        ticking = false;
    };

    window.addEventListener(
        'scroll',
        () => {
            if (ticking) {
                return;
            }
            ticking = true;
            window.requestAnimationFrame(update);
        },
        { passive: true },
    );
}

function animateCount(el: HTMLElement, target: number, durationMs = 700): void {
    if (prefersReducedMotion() || target <= 0) {
        el.textContent = String(target);
        return;
    }

    const start = performance.now();
    const tick = (now: number): void => {
        const t = Math.min(1, (now - start) / durationMs);
        const eased = 1 - (1 - t) ** 3;
        el.textContent = String(Math.round(target * eased));
        if (t < 1) {
            window.requestAnimationFrame(tick);
        }
    };
    window.requestAnimationFrame(tick);
}

function initHeroLiveStats(root: HTMLElement): void {
    const apps = root.querySelector<HTMLElement>('[data-count-to="apps"]');
    const evalCount = root.querySelector<HTMLElement>('[data-count-to="eval"]');
    if (!apps && !evalCount) {
        return;
    }

    const run = (): void => {
        if (apps) {
            const target = Number(apps.dataset.countTarget || '3');
            apps.textContent = '0';
            animateCount(apps, target, 650);
        }
        if (evalCount) {
            const target = Number(evalCount.dataset.countTarget || '1');
            evalCount.textContent = '0';
            animateCount(evalCount, target, 650);
        }
        root.querySelectorAll<HTMLElement>('.landing-status-pulse').forEach((el, i) => {
            window.setTimeout(() => el.classList.add('is-pulsed'), 180 + i * 120);
        });
    };

    window.setTimeout(run, 280);
}

function initPipelineSequence(root: HTMLElement): void {
    const pipeline = root.querySelector<HTMLElement>('.landing-pipeline');
    if (!pipeline) {
        return;
    }

    const pills = [...pipeline.querySelectorAll<HTMLElement>('.apics-status')];
    if (pills.length === 0) {
        return;
    }

    if (prefersReducedMotion()) {
        pipeline.classList.add('is-pipeline-done');
        return;
    }

    let index = 0;
    const stepMs = 380;

    const tick = (): void => {
        pills.forEach((pill) => pill.classList.remove('is-pipeline-active'));
        if (index >= pills.length) {
            pipeline.classList.add('is-pipeline-done');
            return;
        }
        pills[index]?.classList.add('is-pipeline-active');
        index += 1;
        window.setTimeout(tick, stepMs);
    };

    window.setTimeout(tick, 500);
}

/**
 * Homepage landing interactions: auth chrome, motion, scroll polish.
 */
export async function initLandingHomePage(): Promise<void> {
    await ensureLandingSession();
    applyLandingAuthVisibility();
    fixLandingClickTargets();
    initNavScroll();

    document.querySelectorAll<HTMLElement>('[data-landing-logout]').forEach((btn) => {
        btn.addEventListener('click', (event) => {
            void handleLandingLogout(event);
        });
    });

    const root = document.querySelector<HTMLElement>('[data-landing-home]');
    if (!root) {
        return;
    }

    initScrollReveals(root);
    initHeroParallax();
    initHeroLiveStats(root);
    initPipelineSequence(root);
}

export const __landingHelpers = {
    currentAudience,
    applyLandingAuthVisibility,
};
