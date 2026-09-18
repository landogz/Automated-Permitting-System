<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function adminToken(): string
    {
        return $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@csfp.local',
            'password' => 'Admin@12345',
        ])->assertOk()->json('data.token');
    }

    public function test_admin_can_list_users_and_meta(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)
            ->getJson('/api/v1/admin/users/meta')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonStructure(['data' => ['roles', 'staff_roles', 'departments']]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/users')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'data' => [
                    'items',
                    'summary' => [
                        'total',
                        'active',
                        'inactive',
                        'staff',
                        'applicants',
                        'pending',
                    ],
                    'meta',
                ],
            ]);
    }

    public function test_admin_can_create_and_update_staff_user(): void
    {
        $token = $this->adminToken();

        $create = $this->withToken($token)->postJson('/api/v1/admin/users', [
            'name' => 'New Evaluator',
            'email' => 'new.evaluator@csfp.local',
            'password' => 'Evaluate@999',
            'password_confirmation' => 'Evaluate@999',
            'role' => 'evaluator',
            'is_active' => true,
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.email', 'new.evaluator@csfp.local')
            ->assertJsonPath('data.role', 'evaluator');

        $uuid = $create->json('data.uuid');

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.created',
        ]);

        $this->withToken($token)
            ->putJson("/api/v1/admin/users/{$uuid}", [
                'name' => 'Updated Evaluator',
                'role' => 'inspector',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Evaluator')
            ->assertJsonPath('data.role', 'inspector');

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.updated',
        ]);
    }

    public function test_admin_cannot_deactivate_self(): void
    {
        $token = $this->adminToken();
        $admin = User::query()->where('email', 'admin@csfp.local')->firstOrFail();

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/users/{$admin->uuid}")
            ->assertUnprocessable();
    }

    public function test_applicant_cannot_manage_users(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->assertOk()->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/users')
            ->assertForbidden();
    }

    public function test_cannot_create_applicant_via_staff_create(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)
            ->postJson('/api/v1/admin/users', [
                'name' => 'Fake Applicant',
                'email' => 'fake.applicant@csfp.local',
                'password' => 'Applicant@999',
                'password_confirmation' => 'Applicant@999',
                'role' => 'applicant',
            ])
            ->assertUnprocessable();
    }
}
