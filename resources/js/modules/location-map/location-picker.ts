/**
 * Shared MapLibre location picker: search (Nominatim via API) + click/drag pin.
 * Defaults centered on City of San Fernando, Pampanga.
 *
 * Uses an OSM raster style (no API key) so tiles render reliably in modals / local XAMPP.
 */

import * as maplibregl from 'maplibre-gl';
import type { Map, Marker, StyleSpecification } from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

export type LocationValue = {
    address: string;
    latitude: number | null;
    longitude: number | null;
};

export type LocationPickerApi = {
    getValue: () => LocationValue;
    setValue: (value: Partial<LocationValue>) => void;
    destroy: () => void;
    invalidateSize: () => void;
};

type Options = {
    root: HTMLElement;
    addressInput: HTMLInputElement;
    latInput?: HTMLInputElement | null;
    lngInput?: HTMLInputElement | null;
    required?: boolean;
    defaultCenter?: [number, number];
    defaultZoom?: number;
};

const CSFP_CENTER: [number, number] = [120.689, 15.0286]; // lng, lat

/** Reliable no-key raster basemap (OSM). Avoids blank maps when vector tile CDNs fail. */
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
    layers: [
        {
            id: 'osm',
            type: 'raster',
            source: 'osm',
        },
    ],
};

function debounce<T extends (...args: never[]) => void>(fn: T, ms: number): (...args: Parameters<T>) => void {
    let timer: ReturnType<typeof setTimeout> | null = null;
    return (...args: Parameters<T>) => {
        if (timer) clearTimeout(timer);
        timer = setTimeout(() => fn(...args), ms);
    };
}

function waitForVisibleSize(el: HTMLElement, attempts = 20): Promise<void> {
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

export function createLocationPicker(options: Options): LocationPickerApi {
    const {
        root,
        addressInput,
        latInput = null,
        lngInput = null,
        defaultCenter = CSFP_CENTER,
        defaultZoom = 13,
    } = options;

    const mapEl = root.querySelector<HTMLElement>('[data-location-map]');
    const resultsEl = root.querySelector<HTMLElement>('[data-location-results]');
    const searchInput = root.querySelector<HTMLInputElement>('[data-location-search]') || addressInput;
    const coordHint = root.querySelector<HTMLElement>('[data-location-coords]');

    if (!mapEl) {
        throw new Error('Location picker map container missing');
    }

    let map: Map | null = null;
    let marker: Marker | null = null;
    let destroyed = false;
    let pendingCenter: { lng: number; lat: number } | null = null;

    const writeCoords = (lat: number | null, lng: number | null): void => {
        if (latInput) latInput.value = lat != null ? String(lat) : '';
        if (lngInput) lngInput.value = lng != null ? String(lng) : '';
        if (coordHint) {
            coordHint.textContent =
                lat != null && lng != null ? `${lat.toFixed(6)}, ${lng.toFixed(6)}` : 'No pin yet — search or click the map';
        }
    };

    const forceResize = (): void => {
        if (!map || destroyed) return;
        map.resize();
        // Second pass after Bootstrap modal transition / flex layout settles.
        window.requestAnimationFrame(() => map?.resize());
    };

    const placeMarker = (lng: number, lat: number, pan = true): void => {
        if (!map) {
            pendingCenter = { lng, lat };
            writeCoords(lat, lng);
            return;
        }
        if (!marker) {
            marker = new maplibregl.Marker({ draggable: true, color: '#0ab39c' })
                .setLngLat([lng, lat])
                .addTo(map);
            marker.on('dragend', () => {
                const pos = marker?.getLngLat();
                if (!pos) return;
                writeCoords(pos.lat, pos.lng);
                void reverseFill(pos.lat, pos.lng);
            });
        } else {
            marker.setLngLat([lng, lat]);
        }
        writeCoords(lat, lng);
        if (pan) {
            map.flyTo({ center: [lng, lat], zoom: Math.max(map.getZoom(), 16) });
        }
    };

    const reverseFill = async (lat: number, lng: number): Promise<void> => {
        try {
            const { data } = await window.axios.get('/api/v1/geo/reverse', {
                params: { latitude: lat, longitude: lng },
                skipLoading: true,
            });
            const label = String(data.data?.label || '');
            if (label) {
                addressInput.value = label;
                if (searchInput !== addressInput) {
                    searchInput.value = label;
                }
            }
        } catch {
            // Keep coordinates even if reverse geocode fails.
        }
    };

    const renderResults = (
        items: Array<{ label: string; latitude: number; longitude: number }>,
    ): void => {
        if (!resultsEl) return;
        if (!items.length) {
            resultsEl.innerHTML = '';
            resultsEl.classList.add('d-none');
            return;
        }
        resultsEl.classList.remove('d-none');
        resultsEl.innerHTML = items
            .map(
                (item, index) =>
                    `<button type="button" class="list-group-item list-group-item-action py-2 px-3 fs-13" data-result-index="${index}">
                        ${item.label.replace(/</g, '&lt;')}
                    </button>`,
            )
            .join('');

        resultsEl.querySelectorAll<HTMLButtonElement>('[data-result-index]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const idx = Number(btn.dataset.resultIndex);
                const hit = items[idx];
                if (!hit) return;
                addressInput.value = hit.label;
                if (searchInput !== addressInput) searchInput.value = hit.label;
                placeMarker(hit.longitude, hit.latitude);
                resultsEl.classList.add('d-none');
                resultsEl.innerHTML = '';
            });
        });
    };

    const runSearch = debounce(async () => {
        const q = searchInput.value.trim();
        if (q.length < 2) {
            renderResults([]);
            return;
        }
        try {
            const { data } = await window.axios.get('/api/v1/geo/search', {
                params: { q, limit: 6 },
                skipLoading: true,
            });
            renderResults(data.data?.items || []);
        } catch {
            renderResults([]);
        }
    }, 350);

    searchInput.addEventListener('input', () => {
        if (searchInput === addressInput) {
            if (!searchInput.value.trim()) {
                writeCoords(null, null);
                marker?.remove();
                marker = null;
            }
        }
        runSearch();
    });

    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && resultsEl) {
            resultsEl.classList.add('d-none');
        }
    });

    document.addEventListener(
        'click',
        (event) => {
            if (!resultsEl || resultsEl.classList.contains('d-none')) return;
            if (!root.contains(event.target as Node)) {
                resultsEl.classList.add('d-none');
            }
        },
        true,
    );

    const initMap = async (): Promise<void> => {
        if (destroyed || map) return;
        await waitForVisibleSize(mapEl);
        if (destroyed || map) return;

        map = new maplibregl.Map({
            container: mapEl,
            style: OSM_RASTER_STYLE,
            center: defaultCenter,
            zoom: defaultZoom,
            attributionControl: true,
            failIfMajorPerformanceCaveat: false,
        });
        map.addControl(new maplibregl.NavigationControl({ showCompass: false }), 'top-right');
        map.on('click', (event) => {
            const { lng, lat } = event.lngLat;
            placeMarker(lng, lat, false);
            void reverseFill(lat, lng);
        });
        map.on('load', () => {
            forceResize();
            const lat = latInput?.value ? Number(latInput.value) : NaN;
            const lng = lngInput?.value ? Number(lngInput.value) : NaN;
            if (pendingCenter) {
                placeMarker(pendingCenter.lng, pendingCenter.lat);
                pendingCenter = null;
            } else if (Number.isFinite(lat) && Number.isFinite(lng)) {
                placeMarker(lng, lat);
            } else {
                writeCoords(null, null);
            }
            // Modal/flex layouts often settle after the first paint.
            window.setTimeout(forceResize, 100);
            window.setTimeout(forceResize, 350);
        });
        map.on('error', () => {
            // Keep picker usable; address search still works without tiles.
        });
    };

    void initMap();

    return {
        getValue: () => ({
            address: addressInput.value.trim(),
            latitude: latInput?.value ? Number(latInput.value) : null,
            longitude: lngInput?.value ? Number(lngInput.value) : null,
        }),
        setValue: (value) => {
            if (value.address != null) {
                addressInput.value = value.address;
                if (searchInput !== addressInput) searchInput.value = value.address;
            }
            const lat = value.latitude;
            const lng = value.longitude;
            if (lat != null && lng != null && Number.isFinite(lat) && Number.isFinite(lng)) {
                writeCoords(lat, lng);
                placeMarker(lng, lat);
            } else {
                writeCoords(null, null);
                marker?.remove();
                marker = null;
                pendingCenter = null;
            }
        },
        destroy: () => {
            destroyed = true;
            marker?.remove();
            marker = null;
            map?.remove();
            map = null;
        },
        invalidateSize: () => {
            forceResize();
            window.setTimeout(forceResize, 120);
            window.setTimeout(forceResize, 400);
        },
    };
}

export function mountLocationPickers(scope: ParentNode = document): LocationPickerApi[] {
    const apis: LocationPickerApi[] = [];
    scope.querySelectorAll<HTMLElement>('[data-location-picker]').forEach((root) => {
        if (root.dataset.locationMounted === '1') return;
        const addressId = root.dataset.addressInput;
        const latId = root.dataset.latInput;
        const lngId = root.dataset.lngInput;
        const addressInput = addressId
            ? (document.getElementById(addressId) as HTMLInputElement | null)
            : root.querySelector<HTMLInputElement>('[data-location-address]');
        if (!addressInput) return;
        const latInput = latId
            ? (document.getElementById(latId) as HTMLInputElement | null)
            : root.querySelector<HTMLInputElement>('[data-location-lat]');
        const lngInput = lngId
            ? (document.getElementById(lngId) as HTMLInputElement | null)
            : root.querySelector<HTMLInputElement>('[data-location-lng]');

        const api = createLocationPicker({
            root,
            addressInput,
            latInput,
            lngInput,
            required: root.dataset.required === '1',
        });
        root.dataset.locationMounted = '1';
        (root as HTMLElement & { __locationPicker?: LocationPickerApi }).__locationPicker = api;
        apis.push(api);
    });
    return apis;
}

export function getLocationPicker(root: HTMLElement): LocationPickerApi | null {
    return (root as HTMLElement & { __locationPicker?: LocationPickerApi }).__locationPicker || null;
}
