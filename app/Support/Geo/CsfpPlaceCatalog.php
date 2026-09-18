<?php

declare(strict_types=1);

namespace App\Support\Geo;

/**
 * Local CSFP place catalog for location search when Nominatim is slow/blocked.
 *
 * Coordinates are approximate barangay centroids for map pinning (not survey-grade).
 */
final class CsfpPlaceCatalog
{
    /**
     * @return list<array{label: string, latitude: float, longitude: float, barangay: string, city: string}>
     */
    public static function barangays(): array
    {
        $city = 'City of San Fernando';
        $rows = [
            ['Alasas', 15.0612, 120.6614],
            ['Baliti', 15.0554, 120.6652],
            ['Bulaon', 15.0478, 120.6521],
            ['Calulut', 15.0802, 120.6898],
            ['Del Carmen', 15.0335, 120.6789],
            ['Del Pilar', 15.0298, 120.6952],
            ['Del Rosario', 15.0224, 120.6815],
            ['Dela Paz Norte', 15.0412, 120.7018],
            ['Dela Paz Sur', 15.0358, 120.7054],
            ['Dolores', 15.0405, 120.6548],
            ['Juliana', 15.0186, 120.6892],
            ['Lara', 15.0146, 120.7104],
            ['Lourdes', 15.0268, 120.6754],
            ['Maimpis', 15.0589, 120.6824],
            ['Malino', 15.0724, 120.7012],
            ['Malpitic', 15.0651, 120.7125],
            ['Pandaras', 15.0486, 120.7184],
            ['Panipuan', 15.0894, 120.6758],
            ['Pulung Bulu', 15.0198, 120.7026],
            ['Quebiawan', 15.0448, 120.6705],
            ['Saguin', 15.0526, 120.6938],
            ['San Agustin', 15.0372, 120.6884],
            ['San Felipe', 15.0245, 120.6988],
            ['San Isidro', 15.0318, 120.6624],
            ['San Jose', 15.0352, 120.6848],
            ['San Juan', 15.0282, 120.6865],
            ['San Nicolas', 15.0305, 120.6908],
            ['San Pedro', 15.0256, 120.6932],
            ['Santa Lucia', 15.0214, 120.6856],
            ['Santa Teresita', 15.0168, 120.6784],
            ['Santo Niño', 15.0338, 120.6722],
            ['Santo Rosario', 15.0286, 120.6921],
            ['Sindalan', 15.0681, 120.6784],
            ['Telabastagan', 15.0945, 120.6589],
        ];

        return array_map(static function (array $row) use ($city): array {
            [$name, $lat, $lng] = $row;

            return [
                'label' => "Brgy. {$name}, {$city}, Pampanga, Philippines",
                'latitude' => (float) $lat,
                'longitude' => (float) $lng,
                'barangay' => (string) $name,
                'city' => $city,
            ];
        }, $rows);
    }

    /**
     * @return list<array{label: string, latitude: float, longitude: float, barangay: ?string, city: ?string}>
     */
    public static function search(string $query, int $limit = 6): array
    {
        $needle = mb_strtolower(trim($query));
        if ($needle === '' || mb_strlen($needle) < 2) {
            return [];
        }

        $hits = [];
        foreach (self::barangays() as $place) {
            $hay = mb_strtolower($place['barangay'].' '.$place['label']);
            if (! str_contains($hay, $needle)) {
                continue;
            }
            $hits[] = $place;
            if (count($hits) >= $limit) {
                break;
            }
        }

        return $hits;
    }
}
