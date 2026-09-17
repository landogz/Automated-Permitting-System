<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeoLocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function actingAsApiToken(string $token): static
    {
        $this->app['auth']->forgetGuards();
        $this->flushSession();

        return $this->withToken($token);
    }

    public function test_authenticated_user_can_search_and_reverse_geocode(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/search*' => Http::response([
                [
                    'display_name' => 'Sindalan, City of San Fernando, Pampanga, Philippines',
                    'lat' => '15.0500',
                    'lon' => '120.6800',
                    'address' => ['suburb' => 'Sindalan', 'city' => 'City of San Fernando'],
                ],
            ], 200),
            'nominatim.openstreetmap.org/reverse*' => Http::response([
                'display_name' => 'Sindalan, City of San Fernando, Pampanga, Philippines',
                'address' => ['suburb' => 'Sindalan', 'city' => 'City of San Fernando'],
            ], 200),
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->assertOk()->json('data.token');

        $this->actingAsApiToken($token)
            ->getJson('/api/v1/geo/search?q=Sindalan')
            ->assertOk()
            ->assertJsonPath('data.items.0.latitude', 15.05)
            ->assertJsonPath('data.items.0.longitude', 120.68);

        $this->actingAsApiToken($token)
            ->getJson('/api/v1/geo/reverse?latitude=15.05&longitude=120.68')
            ->assertOk()
            ->assertJsonPath('status', true);
    }

    public function test_guest_cannot_use_geo_endpoints(): void
    {
        $this->getJson('/api/v1/geo/search?q=Sindalan')->assertUnauthorized();
    }
}
