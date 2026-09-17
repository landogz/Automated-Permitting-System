/**
 * Read-only MapLibre site map for application detail modals.
 * Shows the pinned project location with Directions / Open / Copy actions.
 */

import * as maplibregl from 'maplibre-gl';
import type { Map, Marker, StyleSpecification } from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';
import { escapeHtml } from '../../utils/bootstrap-modal';
import { toastError, toastSuccess } from '../../utils/toast';

export type SiteMapPoint = {
    latitude?: number | null;
    longitude?: number | null;
    address?: string | null;
    label?: string | null;
};

export type SiteMapViewerApi = {
    destroy: () => void;
    invalidateSize: () => void;
};

const CSFP_CENTER: [number, number] = [120.689, 15.0286];

const OSM_RASTER_STYLE: StyleSpecification = {
    version: 8,
    sources: {
        osm: {
            type: 'raster',
            tiles: [
                'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
                'https://b.tile.openstreetmap.org/{z}/{x}/{y}.png',
                'https://c.tile.openstreetmap.org/{z}/{x}/{y}.png',
            ],
            tileSize: 256,
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxzoom: 19,
        },
    },
    layers: [{ id: 'osm', type: 'raster', source: 'osm' }],
};

function waitForVisibleSize(el: HTMLElement, attempts = 30): Promise<void> {
    return new Promise((resolve) => {
        let left = attempts;
        const tick = (): void => {
            if (el.clientWidth > 40 && el.clientHeight > 40) {
                resolve();
                return;
            }
            left -= 1;
            if (left <= 0) {
                resolve();
                return;
            }
            window.setTimeout(tick, 50);
        };
        tick();
    });
}

/** Reject null/empty and Null-Island (0,0) which Number(null) falsely produces. */
export function parseCoords(point: SiteMapPoint): { lat: number; lng: number } | null {
    const rawLat = point.latitude;
    const rawLng = point.longitude;
    if (rawLat === null || rawLat === undefined || rawLat === '') return null;
    if (rawLng === null || rawLng === undefined || rawLng === '') return null;

    const lat = typeof rawLat === 'number' ? rawLat : Number(rawLat);
    const lng = typeof rawLng === 'number' ? rawLng : Number(rawLng);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null;
    if (Math.abs(lat) > 90 || Math.abs(lng) > 180) return null;
    // Unset / Null Island — never a valid CSFP project pin
    if (Math.abs(lat) < 0.0001 && Math.abs(lng) < 0.0001) return null;
    return { lat, lng };
}

export function googleDirectionsUrl(lat: number, lng: number): string {
    return `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(`${lat},${lng}`)}`;
}

export function appleMapsDirectionsUrl(lat: number, lng: number, label?: string | null): string {
    const q = label?.trim() || `${lat},${lng}`;
    return `https://maps.apple.com/?daddr=${encodeURIComponent(`${lat},${lng}`)}&q=${encodeURIComponent(q)}`;
}

export function openInMapsUrl(lat: number, lng: number, label?: string | null): string {
    const q = label?.trim() ? `${label} @${lat},${lng}` : `${lat},${lng}`;
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(q)}`;
}

function actionsHtml(lat: number, lng: number, label: string, address: string): string {
    return `<div class="apics-site-map__actions d-flex flex-wrap gap-1">
        <a class="btn btn-sm btn-primary"
            href="${escapeHtml(googleDirectionsUrl(lat, lng))}"
            target="_blank"
            rel="noopener noreferrer"
            data-site-map-directions>
            <i class="ri-guide-line align-bottom me-1"></i>Get directions
        </a>
        <div class="btn-group">
            <button type="button"
                class="btn btn-sm btn-soft-secondary dropdown-toggle"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label="More map actions">
                Open in…
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="${escapeHtml(openInMapsUrl(lat, lng, label))}" target="_blank" rel="noopener noreferrer">
                        <i class="ri-google-fill me-1"></i> Google Maps
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="${escapeHtml(appleMapsDirectionsUrl(lat, lng, label))}" target="_blank" rel="noopener noreferrer">
                        <i class="ri-apple-fill me-1"></i> Apple Maps
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="${escapeHtml(`https://www.openstreetmap.org/?mlat=${lat}&mlon=${lng}#map=17/${lat}/${lng}`)}" target="_blank" rel="noopener noreferrer">
                        <i class="ri-road-map-line me-1"></i> OpenStreetMap
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <button type="button" class="dropdown-item" data-site-map-copy>
                        <i class="ri-file-copy-line me-1"></i> Copy coordinates
                    </button>
                </li>
                ${
                    address
                        ? `<li>
                            <button type="button" class="dropdown-item" data-site-map-copy-address>
                                <i class="ri-clipboard-line me-1"></i> Copy address
                            </button>
                           </li>`
                        : ''
                }
            </ul>
        </div>
    </div>`;
}

/**
 * HTML block for project site map + direction actions.
 * Call {@link mountSiteMapViewer} after inserting into the DOM.
 */
export function siteMapSectionHtml(point: SiteMapPoint): string {
    const coords = parseCoords(point);
    const address = (point.address || '').trim();
    const label = (point.label || 'Project site').trim();

    if (!coords && !address) {
        return `<div class="apics-site-map apics-site-map--empty border rounded p-3 mb-0">
            <div class="d-flex align-items-start gap-2">
                <span class="avatar-xs flex-shrink-0">
                    <span class="avatar-title rounded-circle bg-light text-muted">
                        <i class="ri-map-pin-line"></i>
                    </span>
                </span>
                <div>
                    <p class="fw-medium mb-1">Project site map</p>
                    <p class="text-muted small mb-0">No project location pin recorded yet.</p>
                </div>
            </div>
        </div>`;
    }

    if (!coords && address) {
        // Mount will geocode the address and paint the map.
        return `<div class="apics-site-map border rounded overflow-hidden"
            data-site-map
            data-needs-geocode="1"
            data-label="${escapeHtml(label)}"
            data-address="${escapeHtml(address)}">
            <div class="apics-site-map__toolbar d-flex flex-wrap align-items-start justify-content-between gap-2 px-3 py-3 border-bottom bg-light-subtle">
                <div class="apics-site-map__meta min-w-0 flex-grow-1">
                    <p class="apics-site-map__title fw-medium mb-1">${escapeHtml(label)}</p>
                    <p class="apics-site-map__address text-muted small mb-0" title="${escapeHtml(address)}">${escapeHtml(address)}</p>
                    <p class="apics-site-map__coords text-muted small mb-0" data-site-map-coords>Locating pin…</p>
                </div>
                <div class="apics-site-map__actions d-flex flex-wrap gap-1" data-site-map-actions></div>
            </div>
            <div class="apics-site-map__canvas" data-site-map-canvas role="img" aria-label="Map of ${escapeHtml(label)}">
                <div class="apics-site-map__loading text-muted small">Resolving map location…</div>
            </div>
        </div>`;
    }

    const { lat, lng } = coords!;
    const coordText = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;

    return `<div class="apics-site-map border rounded overflow-hidden"
        data-site-map
        data-lat="${escapeHtml(String(lat))}"
        data-lng="${escapeHtml(String(lng))}"
        data-label="${escapeHtml(label)}"
        data-address="${escapeHtml(address)}">
        <div class="apics-site-map__toolbar d-flex flex-wrap align-items-start justify-content-between gap-2 px-3 py-3 border-bottom bg-light-subtle">
            <div class="apics-site-map__meta min-w-0 flex-grow-1">
                <p class="apics-site-map__title fw-medium mb-1">${escapeHtml(label)}</p>
                ${
                    address
                        ? `<p class="apics-site-map__address text-muted small mb-1" title="${escapeHtml(address)}">${escapeHtml(address)}</p>`
                        : ''
                }
                <p class="apics-site-map__coords text-muted small font-monospace mb-0" data-site-map-coords>${escapeHtml(coordText)}</p>
            </div>
            ${actionsHtml(lat, lng, label, address)}
        </div>
        <div class="apics-site-map__canvas" data-site-map-canvas role="img" aria-label="Map of ${escapeHtml(label)}"></div>
    </div>`;
}

/**
 * Compact map thumbnail (≈40% column) with Expand overlay for the application detail Overview tab.
 */
export function siteMapThumbnailHtml(point: SiteMapPoint): string {
    const coords = parseCoords(point);
    const address = (point.address || '').trim();
    const label = (point.label || 'Project site').trim();

    if (!coords && !address) {
        return `<div class="apics-site-map apics-site-map--thumb apics-site-map--empty border rounded p-3 h-100 d-flex align-items-center">
            <div>
                <p class="fw-medium mb-1">Map preview</p>
                <p class="text-muted small mb-0">No project location pin recorded yet.</p>
            </div>
        </div>`;
    }

    const latAttr = coords ? `data-lat="${escapeHtml(String(coords.lat))}"` : '';
    const lngAttr = coords ? `data-lng="${escapeHtml(String(coords.lng))}"` : '';
    const needsGeocode = !coords && address ? 'data-needs-geocode="1"' : '';

    return `<div class="apics-site-map apics-site-map--thumb border rounded overflow-hidden h-100"
        data-site-map
        ${latAttr}
        ${lngAttr}
        ${needsGeocode}
        data-label="${escapeHtml(label)}"
        data-address="${escapeHtml(address)}">
        <div class="apics-site-map__canvas apics-site-map__canvas--thumb position-relative" data-site-map-canvas role="img" aria-label="Map of ${escapeHtml(label)}">
            ${!coords ? '<div class="apics-site-map__loading text-muted small">Resolving map location…</div>' : ''}
            <button type="button" class="btn btn-sm btn-light apics-site-map__expand shadow-sm" data-ops-expand-map aria-label="Expand map">
                <i class="ri-fullscreen-line align-bottom me-1"></i> Expand Map
            </button>
        </div>
        <p class="apics-site-map__coords d-none" data-site-map-coords></p>
        <div class="d-none" data-site-map-actions></div>
    </div>`;
}

async function geocodeAddress(address: string): Promise<{ lat: number; lng: number } | null> {
    try {
        const { data } = await window.axios.get('/api/v1/geo/search', {
            params: { q: address, limit: 1 },
        });
        const hit = data?.data?.items?.[0] ?? data?.data?.[0];
        if (!hit) return null;
        const lat = Number(hit.latitude ?? hit.lat);
        const lng = Number(hit.longitude ?? hit.lon ?? hit.lng);
        return parseCoords({ latitude: lat, longitude: lng });
    } catch {
        return null;
    }
}

function bindCopyActions(root: HTMLElement, lat: number, lng: number): void {
    const copyText = async (text: string, successMsg: string): Promise<void> => {
        try {
            await navigator.clipboard.writeText(text);
            toastSuccess(successMsg);
        } catch {
            toastError('Unable to copy to clipboard');
        }
    };

    root.querySelectorAll<HTMLElement>('[data-site-map-copy]').forEach((btn) => {
        btn.addEventListener('click', () => {
            void copyText(`${lat.toFixed(6)}, ${lng.toFixed(6)}`, 'Coordinates copied');
        });
    });
    root.querySelectorAll<HTMLElement>('[data-site-map-copy-address]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const address = root.dataset.address || '';
            if (!address) {
                toastError('No address on file');
                return;
            }
            void copyText(address, 'Address copied');
        });
    });
}

function paintResolvedUi(
    root: HTMLElement,
    lat: number,
    lng: number,
    label: string,
    address: string,
): void {
    root.dataset.lat = String(lat);
    root.dataset.lng = String(lng);
    delete root.dataset.needsGeocode;

    const coordsEl = root.querySelector<HTMLElement>('[data-site-map-coords]');
    if (coordsEl) {
        coordsEl.classList.add('font-monospace');
        coordsEl.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    }

    const actionsHost = root.querySelector<HTMLElement>('[data-site-map-actions]');
    if (actionsHost) {
        actionsHost.outerHTML = actionsHtml(lat, lng, label, address);
    }

    bindCopyActions(root, lat, lng);
}

/**
 * Mount MapLibre on the first `[data-site-map]` inside `scope` (or the element itself).
 */
export function mountSiteMapViewer(scope: HTMLElement): SiteMapViewerApi | null {
    const root =
        scope.matches('[data-site-map]')
            ? scope
            : scope.querySelector<HTMLElement>('[data-site-map]');
    if (!root) return null;

    const canvas = root.querySelector<HTMLElement>('[data-site-map-canvas]');
    if (!canvas) return null;

    let map: Map | null = null;
    let marker: Marker | null = null;
    let destroyed = false;

    const forceResize = (): void => {
        if (!map || destroyed) return;
        map.resize();
        window.requestAnimationFrame(() => {
            map?.resize();
            // Trigger a tiny pan so raster tiles request after modal layout settles.
            const c = map?.getCenter();
            if (c) map?.jumpTo({ center: [c.lng, c.lat] });
        });
    };

    const initMap = async (lat: number, lng: number): Promise<void> => {
        await waitForVisibleSize(canvas);
        if (destroyed) return;

        canvas.querySelector('.apics-site-map__loading')?.remove();

        map = new maplibregl.Map({
            container: canvas,
            style: OSM_RASTER_STYLE,
            center: [lng, lat],
            zoom: 15,
            attributionControl: { compact: true },
        });
        map.addControl(new maplibregl.NavigationControl({ showCompass: false }), 'top-right');
        marker = new maplibregl.Marker({ color: '#405189', draggable: false })
            .setLngLat([lng, lat])
            .addTo(map);

        map.on('load', () => {
            forceResize();
            map?.easeTo({ center: [lng, lat], zoom: 15, duration: 0 });
            window.setTimeout(forceResize, 50);
            window.setTimeout(forceResize, 250);
            window.setTimeout(forceResize, 600);
        });

        const modal = root.closest('.modal');
        const onShown = (): void => forceResize();
        modal?.addEventListener('shown.bs.modal', onShown);
        (root as HTMLElement & { __apicsSiteMapCleanup?: () => void }).__apicsSiteMapCleanup = () => {
            modal?.removeEventListener('shown.bs.modal', onShown);
        };
    };

    const label = root.dataset.label || 'Project site';
    const address = root.dataset.address || '';
    const needsGeocode = root.dataset.needsGeocode === '1';
    let lat = Number(root.dataset.lat);
    let lng = Number(root.dataset.lng);
    const existing = parseCoords({ latitude: lat, longitude: lng });

    if (existing) {
        bindCopyActions(root, existing.lat, existing.lng);
        void initMap(existing.lat, existing.lng);
    } else if (needsGeocode && address) {
        void (async () => {
            const found = await geocodeAddress(address);
            if (destroyed) return;
            if (!found) {
                const coordsEl = root.querySelector<HTMLElement>('[data-site-map-coords]');
                if (coordsEl) coordsEl.textContent = 'Could not resolve map pin for this address.';
                canvas.innerHTML = `<div class="apics-site-map__loading text-muted small px-3 py-5 text-center">
                    Address on file, but no map coordinates yet. Ask the applicant to re-pin the site when editing the application.
                </div>`;
                return;
            }
            lat = found.lat;
            lng = found.lng;
            paintResolvedUi(root, lat, lng, label, address);
            await initMap(lat, lng);
        })();
    }

    return {
        invalidateSize: forceResize,
        destroy: () => {
            destroyed = true;
            const cleanup = (root as HTMLElement & { __apicsSiteMapCleanup?: () => void }).__apicsSiteMapCleanup;
            cleanup?.();
            marker?.remove();
            marker = null;
            map?.remove();
            map = null;
        },
    };
}

/** Convenience: render HTML then mount; destroys any previous viewer stored on scope. */
export function renderAndMountSiteMap(
    host: HTMLElement,
    point: SiteMapPoint,
    previous?: SiteMapViewerApi | null,
): SiteMapViewerApi | null {
    previous?.destroy();
    host.innerHTML = siteMapSectionHtml(point);
    return mountSiteMapViewer(host);
}

export { CSFP_CENTER };
