<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\AuditLog;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTrailTest extends TestCase
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

    public function test_login_records_actor_name_and_failed_attempts_include_email(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'nobody@csfp.local',
            'password' => 'WrongPass@1',
        ])->assertStatus(422);

        $failed = AuditLog::query()->where('event', 'auth.login_failed')->latest('id')->first();
        $this->assertNotNull($failed);
        $this->assertSame('nobody@csfp.local', $failed->actor_name);
        $this->assertSame('nobody@csfp.local', $failed->meta['attempted_email'] ?? null);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@csfp.local',
            'password' => 'Admin@12345',
        ])->assertOk();

        $login = AuditLog::query()->where('event', 'auth.login')->latest('id')->first();
        $this->assertNotNull($login);
        $this->assertNotNull($login->actor_name);
        $this->assertNotSame('', $login->actor_name);
        $this->assertSame('admin@csfp.local', $login->meta['email'] ?? null);
    }

    public function test_admin_can_filter_audit_logs_and_inspect_resource_fields(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@csfp.local',
            'password' => 'Admin@12345',
        ])->assertOk()->json('data.token');

        $list = $this->actingAsApiToken($token)
            ->getJson('/api/v1/admin/audit-logs?range=7d&per_page=50')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'data' => [
                    'items',
                    'summary' => [
                        'events_24h',
                        'active_sessions',
                        'fee_overrides_24h',
                        'security_anomalies_24h',
                    ],
                    'filters' => [
                        'categories',
                        'actors',
                    ],
                    'meta',
                ],
            ]);

        $this->assertNotEmpty($list->json('data.items'));

        $loginItem = collect($list->json('data.items'))
            ->first(fn (array $row): bool => ($row['event'] ?? '') === 'auth.login');

        $this->assertNotNull($loginItem);
        $this->assertNotEmpty($loginItem['actor_name']);
        $this->assertSame('authentication', $loginItem['category']);
        $this->assertArrayHasKey('resource', $loginItem);
        $this->assertArrayHasKey('badge_tone', $loginItem);

        $uuid = $loginItem['uuid'];
        $this->actingAsApiToken($token)
            ->getJson("/api/v1/admin/audit-logs/{$uuid}")
            ->assertOk()
            ->assertJsonPath('data.uuid', $uuid)
            ->assertJsonPath('data.event', 'auth.login');

        $this->actingAsApiToken($token)
            ->getJson('/api/v1/admin/audit-logs/summary')
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->actingAsApiToken($token)
            ->getJson('/api/v1/admin/audit-logs?category=authentication&severity=failure')
            ->assertOk();
    }

    public function test_staff_cannot_access_audit_summary(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'evaluator@csfp.local',
            'password' => 'Evaluate@123',
        ])->assertOk()->json('data.token');

        $this->actingAsApiToken($token)
            ->getJson('/api/v1/admin/audit-logs/summary')
            ->assertForbidden();
    }
}
