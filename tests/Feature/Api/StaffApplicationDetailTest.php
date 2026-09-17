<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\PermitApplication;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffApplicationDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_evaluator_can_view_application_details(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'evaluator@csfp.local',
            'password' => 'Evaluate@123',
            'device_name' => 'test',
        ])->assertOk()->json('data.token');

        $application = PermitApplication::query()
            ->where('status', 'submitted')
            ->orWhere('status', 'under_evaluation')
            ->first()
            ?? PermitApplication::query()->firstOrFail();

        $this->withToken($token)
            ->getJson("/api/v1/staff/applications/{$application->uuid}")
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.uuid', $application->uuid)
            ->assertJsonStructure([
                'data' => [
                    'application_no',
                    'project_title',
                    'payload',
                    'form',
                    'documents',
                    'applicant',
                ],
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'permit_application.staff_viewed',
        ]);
    }

    public function test_applicant_cannot_use_staff_detail_endpoint(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
            'device_name' => 'test',
        ])->assertOk()->json('data.token');

        $application = PermitApplication::query()->firstOrFail();

        $this->withToken($token)
            ->getJson("/api/v1/staff/applications/{$application->uuid}")
            ->assertForbidden();
    }
}
