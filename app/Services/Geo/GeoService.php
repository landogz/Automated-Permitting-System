<?php

declare(strict_types=1);

namespace App\Services\Geo;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/**
 * OpenStreetMap Nominatim geocoding (search + reverse) for MapLibre location pickers.
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

        $response = Http::timeout(8)
            ->withHeaders([
                'User-Agent' => 'APICS-CSFP-OCBO/1.0 (automated-permitting; local-dev)',
                'Accept' => 'application/json',
            ])
            ->get(self::NOMINATIM.'/search', [
                'q' => $q.', City of San Fernando, Pampanga, Philippines',
                'format' => 'jsonv2',
                'addressdetails' => 1,
                'limit' => min(max($limit, 1), 10),
                'countrycodes' => 'ph',
                'viewbox' => self::DEFAULT_VIEWBOX,
                'bounded' => 0,
            ]);

        if (! $response->successful()) {
            throw ValidationException::withMessages([
                'search' => ['Location search is temporarily unavailable. Try again shortly.'],
            ]);
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = $response->json() ?? [];

        return array_values(array_map(static function (array $row): array {
            $address = is_array($row['address'] ?? null) ? $row['address'] : [];

            return [
                'label' => (string) ($row['display_name'] ?? ''),
                'latitude' => (float) ($row['lat'] ?? 0),
                'longitude' => (float) ($row['lon'] ?? 0),
                'barangay' => isset($address['suburb'])
                    ? (string) $address['suburb']
                    : (isset($address['village']) ? (string) $address['village'] : null),
                'city' => isset($address['city'])
                    ? (string) $address['city']
                    : (isset($address['town']) ? (string) $address['town'] : null),
            ];
        }, $rows));
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

        $response = Http::timeout(8)
            ->withHeaders([
                'User-Agent' => 'APICS-CSFP-OCBO/1.0 (automated-permitting; local-dev)',
                'Accept' => 'application/json',
            ])
            ->get(self::NOMINATIM.'/reverse', [
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'jsonv2',
                'addressdetails' => 1,
                'zoom' => 18,
            ]);

        if (! $response->successful()) {
            throw ValidationException::withMessages([
                'coordinates' => ['Reverse geocoding is temporarily unavailable.'],
            ]);
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
}
