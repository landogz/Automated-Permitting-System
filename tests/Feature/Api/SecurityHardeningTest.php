<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\LogbookEntry;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_unsigned_logbook_print_is_forbidden(): void
    {
        $recordsToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'records@csfp.local',
            'password' => 'Records@123',
        ])->assertOk()->json('data.token');

        $uuid = $this->withToken($recordsToken)
            ->postJson('/api/v1/staff/logbook-entries', [
                'book_type' => 'g01_releasing',
                'subject' => 'Security print test',
            ])
            ->assertCreated()
            ->json('data.uuid');

        $this->get("/admin/logbooks/{$uuid}/print")
            ->assertForbidden();
    }

    public function test_signed_logbook_print_is_allowed(): void
    {
        $entry = LogbookEntry::query()->create([
            'entry_no' => 'G01-TEST-0001',
            'book_type' => 'g01_releasing',
            'subject' => 'Signed print smoke',
            'recorded_by' => User::query()->where('email', 'records@csfp.local')->value('id'),
            'recorded_at' => now(),
        ]);

        $url = URL::temporarySignedRoute(
            'admin.logbooks.print',
            now()->addMinutes(5),
            ['logbook' => $entry->uuid],
        );

        $this->get($url)->assertOk();
    }

    public function test_login_failures_use_generic_message(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'WrongPass@1',
        ])->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'Unable to sign in with those credentials.');
    }

    public function test_evaluator_cannot_manage_departments(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'evaluator@csfp.local',
            'password' => 'Evaluate@123',
        ])->assertOk()->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/departments')
            ->assertForbidden();
    }

    public function test_demo_login_is_disabled_outside_explicit_local_flag(): void
    {
        config(['apics_demo_users.enabled' => false]);

        $this->postJson('/api/v1/auth/demo-login', [
            'email' => 'admin@csfp.local',
        ])->assertForbidden();
    }

    public function test_applicant_cannot_read_another_applicants_application(): void
    {
        $ownerToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->assertOk()->json('data.token');

        $uuid = $this->withToken($ownerToken)
            ->postJson('/api/v1/applications', [
                'project_title' => 'Private project',
                'project_location' => 'San Fernando',
                'payload' => ['owner' => 'A'],
            ])
            ->assertCreated()
            ->json('data.uuid');

        $otherToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant2@csfp.local',
            'password' => 'Applicant@123',
        ])->assertOk()->json('data.token');

        $this->app['auth']->forgetGuards();

        $this->withToken($otherToken)
            ->getJson("/api/v1/applications/{$uuid}")
            ->assertNotFound();
    }
}
