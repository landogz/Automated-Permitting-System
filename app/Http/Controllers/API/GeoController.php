<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\Geo\GeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GeoController extends Controller
{
    public function __construct(private readonly GeoService $geo)
    {
    }

    /**
     * Search places via Nominatim (CSFP-biased) for MapLibre location pickers.
     */
    public function search(Request $request): JsonResponse
    {
        abort_unless($request->user() !== null, 401);

        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:200'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        try {
            $items = $this->geo->search(
                (string) $validated['q'],
                (int) ($validated['limit'] ?? 8),
            );
        } catch (ValidationException $e) {
            return ApiResponse::error($e->getMessage(), $e->errors(), 422);
        }

        return ApiResponse::success('Locations found', ['items' => $items]);
    }

    /**
     * Reverse-geocode a map pin to a display address.
     */
    public function reverse(Request $request): JsonResponse
    {
        abort_unless($request->user() !== null, 401);

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        try {
            $place = $this->geo->reverse(
                (float) $validated['latitude'],
                (float) $validated['longitude'],
            );
        } catch (ValidationException $e) {
            return ApiResponse::error($e->getMessage(), $e->errors(), 422);
        }

        return ApiResponse::success('Location resolved', $place);
    }
}
