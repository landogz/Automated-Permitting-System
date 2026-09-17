<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndDepartmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_login_and_manage_departments(): void
    {
        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@csfp.local',
            'password' => 'Admin@12345',
            'device_name' => 'test',
        ]);

        $login->assertOk()
            ->assertJsonPath('status', true);

        $token = $login->json('data.token');

        $create = $this->withToken($token)->postJson('/api/v1/admin/departments', [
            'code' => 'TEST-DEPT',
            'name' => 'Test Department',
            'is_active' => true,
        ]);

        $create->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.code', 'TEST-DEPT');

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'department.created',
        ]);
    }

    public function test_applicant_can_create_and_submit_application(): void
    {
        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->assertOk();

        $token = $login->json('data.token');

        $draft = $this->withToken($token)->postJson('/api/v1/applications', [
            'project_title' => 'Warehouse',
            'project_location' => 'San Fernando, Pampanga',
            'payload' => ['owner_name' => 'Juan'],
        ])->assertCreated();

        $uuid = $draft->json('data.uuid');

        $this->withToken($token)
            ->postJson("/api/v1/applications/{$uuid}/submit")
            ->assertOk()
            ->assertJsonPath('data.status', 'submitted');
    }

    public function test_unauthenticated_admin_route_is_rejected(): void
    {
        $this->getJson('/api/v1/admin/departments')
            ->assertUnauthorized()
            ->assertJsonPath('status', false);
    }
}
