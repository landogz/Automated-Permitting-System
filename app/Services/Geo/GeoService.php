<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Support\Geo\CsfpPlaceCatalog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * OpenStreetMap Nominatim geocoding (search + reverse) for MapLibre location pickers,
 * with a local CSFP barangay catalog fallback when the upstream provider is unavailable.
 */
final class GeoService
{
    private const NOMINATIM = 'https://nominatim.openstreetmap.org';

    /** City of San Fernando, Pampanga bias. */
    private const DEFAULT_VIEWBOX = '120.60,14.98,120.78,15.12';

    /**
     * @return list<array{label: string, latitude: float, longitude: float, barangay: ?string, city: ?string}>
     */
    public function search(string $query, int $limit = 8): array
    {
        $q = trim($query);
        if ($q === '' || mb_strlen($q) < 2) {
            return [];
        }

        $limit = min(max($limit, 1), 10);
        $local = CsfpPlaceCatalog::search($q, $limit);

        try {
            $remote = $this->nominatimSearch($q, $limit);
        } catch (ValidationException $e) {
            if ($local !== []) {
                return $local;
            }

            throw $e;
        }

        return $this->mergePlaces($local, $remote, $limit);
    }

    /**
     * @return array{label: string, latitude: float, longitude: float, barangay: ?string, city: ?string}
     */
    public function reverse(float $latitude, float $longitude): array
    {
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            throw ValidationException::withMessages([
                'coordinates' => ['Invalid map coordinates.'],
            ]);
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders($this->nominatimHeaders())
                ->get(self::NOMINATIM.'/reverse', [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                    'zoom' => 18,
                ]);
        } catch (\Throwable $e) {
            Log::warning('geo.reverse_failed', ['message' => $e->getMessage()]);

            return $this->coordinateFallback($latitude, $longitude);
        }

        if (! $response->successful()) {
            Log::warning('geo.reverse_http', ['status' => $response->status()]);

            return $this->coordinateFallback($latitude, $longitude);
        }

        /** @var array<string, mixed> $row */
        $row = $response->json() ?? [];
        $address = is_array($row['address'] ?? null) ? $row['address'] : [];

        return [
            'label' => (string) ($row['display_name'] ?? sprintf('%.6f, %.6f', $latitude, $longitude)),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'barangay' => isset($address['suburb'])
                ? (string) $address['suburb']
                : (isset($address['village']) ? (string) $address['village'] : null),
            'city' => isset($address['city'])
                ? (string) $address['city']
                : (isset($address['town']) ? (string) $address['town'] : null),
        ];
    }

    /**
     * @return list<array{label: string, latitude: float, longitude: float, barangay: ?string, city: ?string}>
     */
    private function nominatimSearch(string $q, int $limit): array
    {
        $biased = $this->alreadyMentionsCsfp($q)
            ? $q
            : $q.', City of San Fernando, Pampanga, Philippines';

        try {
            $response = Http::timeout(8)
                ->withHeaders($this->nominatimHeaders())
                ->get(self::NOMINATIM.'/search', [
                    'q' => $biased,
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                    'limit' => $limit,
                    'countrycodes' => 'ph',
                    'viewbox' => self::DEFAULT_VIEWBOX,
                    'bounded' => 0,
                ]);
        } catch (\Throwable $e) {
            Log::warning('geo.search_failed', ['message' => $e->getMessage()]);

            throw ValidationException::withMessages([
                'search' => ['Location search is temporarily unavailable. Try a barangay name (e.g. Sindalan) or click the map.'],
            ]);
        }

        if (! $response->successful()) {
            Log::warning('geo.search_http', ['status' => $response->status()]);

            throw ValidationException::withMessages([
                'search' => ['Location search is temporarily unavailable. Try a barangay name (e.g. Sindalan) or click the map.'],
            ]);
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = $response->json() ?? [];

        return array_values(array_filter(array_map(static function (array $row): ?array {
            $label = trim((string) ($row['display_name'] ?? ''));
            $lat = (float) ($row['lat'] ?? 0);
            $lng = (float) ($row['lon'] ?? 0);
            if ($label === '' || ($lat === 0.0 && $lng === 0.0)) {
                return null;
            }

            $address = is_array($row['address'] ?? null) ? $row['address'] : [];

            return [
                'label' => $label,
                'latitude' => $lat,
                'longitude' => $lng,
                'barangay' => isset($address['suburb'])
                    ? (string) $address['suburb']
                    : (isset($address['village']) ? (string) $address['village'] : null),
                'city' => isset($address['city'])
                    ? (string) $address['city']
                    : (isset($address['town']) ? (string) $address['town'] : null),
            ];
        }, $rows)));
    }

    /**
     * @param  list<array{label: string, latitude: float, longitude: float, barangay: ?string, city: ?string}>  $primary
     * @param  list<array{label: string, latitude: float, longitude: float, barangay: ?string, city: ?string}>  $secondary
     * @return list<array{label: string, latitude: float, longitude: float, barangay: ?string, city: ?string}>
     */
    private function mergePlaces(array $primary, array $secondary, int $limit): array
    {
        $seen = [];
        $merged = [];

        foreach (array_merge($primary, $secondary) as $place) {
            $key = mb_strtolower(trim($place['label']));
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $merged[] = $place;
            if (count($merged) >= $limit) {
                break;
            }
        }

        return $merged;
    }

    /**
     * @return array{label: string, latitude: float, longitude: float, barangay: ?string, city: ?string}
     */
    private function coordinateFallback(float $latitude, float $longitude): array
    {
        return [
            'label' => sprintf('Pin at %.6f, %.6f (City of San Fernando area)', $latitude, $longitude),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'barangay' => null,
            'city' => 'City of San Fernando',
        ];
    }

    private function alreadyMentionsCsfp(string $q): bool
    {
        $lower = mb_strtolower($q);

        return str_contains($lower, 'san fernando')
            || str_contains($lower, 'pampanga')
            || str_contains($lower, 'csfp');
    }

    /**
     * @return array{User-Agent: string, Accept: string, Accept-Language: string}
     */
    private function nominatimHeaders(): array
    {
        $contact = (string) config('app.url', 'https://apics.local');

        return [
            'User-Agent' => 'APICS-CSFP-OCBO/1.0 ('.$contact.'; permitting-geocoder)',
            'Accept' => 'application/json',
            'Accept-Language' => 'en',
        ];
    }
}
