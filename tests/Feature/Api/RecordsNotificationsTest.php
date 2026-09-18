<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RecordsNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Sanctum RequestGuard caches the user across in-process HTTP test requests.
     */
    private function actingAsApiToken(string $token): static
    {
        $this->app['auth']->forgetGuards();
        $this->flushSession();

        return $this->withToken($token);
    }

    public function test_records_can_create_logbook_archive_and_send_notification(): void
    {
        Mail::fake();

        $recordsToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'records@csfp.local',
            'password' => 'Records@123',
        ])->assertOk()->json('data.token');

        $applicant = User::query()->where('email', 'applicant@csfp.local')->firstOrFail();

        $logbook = $this->actingAsApiToken($recordsToken)
            ->postJson('/api/v1/staff/logbook-entries', [
                'book_type' => 'g01_releasing',
                'subject' => 'Release building permit package',
                'recipient_name' => 'Demo Applicant',
                'notes' => 'Claimed at releasing window',
            ])
            ->assertCreated()
            ->assertJsonPath('data.book_type', 'g01_releasing');

        $this->assertNotEmpty($logbook->json('data.entry_no'));
        $this->assertNotEmpty($logbook->json('data.print_url'));

        $forReleasing = \App\Models\PermitApplication::query()
            ->where('status', 'for_releasing')
            ->first();
        $this->assertNotNull($forReleasing, 'Demo data should include a for_releasing application');

        $this->actingAsApiToken($recordsToken)
            ->postJson('/api/v1/staff/logbook-entries', [
                'book_type' => 'g01_releasing',
                'application_uuid' => $forReleasing->uuid,
                'subject' => 'Release paid permit at window',
                'recipient_name' => 'Demo Applicant',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('permit_applications', [
            'uuid' => $forReleasing->uuid,
            'status' => 'released',
        ]);

        $this->actingAsApiToken($recordsToken)
            ->getJson('/api/v1/staff/logbook-entries?book_type=g01_releasing')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.summary.g01_releasing', 2);

        $archive = $this->actingAsApiToken($recordsToken)
            ->postJson('/api/v1/staff/archive-records', [
                'title' => 'CICTO digital vault backup stub',
                'storage_location' => 'Vault A / Shelf 1',
                'media_type' => 'digital',
                'notes' => 'Phase 6 archive smoke test',
            ])
            ->assertCreated();

        $this->assertNotEmpty($archive->json('data.archive_no'));
        $this->assertNotEmpty($archive->json('data.checksum'));

        $this->actingAsApiToken($recordsToken)
            ->getJson('/api/v1/staff/archive-records?media_type=digital')
            ->assertOk()
            ->assertJsonPath('data.summary.digital', 1)
            ->assertJsonPath('data.items.0.media_type', 'digital');

        $notification = $this->actingAsApiToken($recordsToken)
            ->postJson('/api/v1/staff/notifications/send', [
                'user_uuid' => $applicant->uuid,
                'channel' => 'in_app',
                'template_code' => 'status.update',
                'message' => 'Your records package is ready for claim.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.is_read', false);

        $notifUuid = $notification->json('data.uuid');

        $applicantToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->assertOk()->json('data.token');

        $this->actingAsApiToken($applicantToken)
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonFragment(['uuid' => $notifUuid])
            ->assertJsonStructure([
                'data' => [
                    'items',
                    'unread_count',
                    'counts' => ['total', 'unread', 'read'],
                    'meta',
                ],
            ]);

        $this->actingAsApiToken($applicantToken)
            ->getJson('/api/v1/notifications?status=unread')
            ->assertOk()
            ->assertJsonFragment(['uuid' => $notifUuid]);

        $this->actingAsApiToken($applicantToken)
            ->postJson("/api/v1/notifications/{$notifUuid}/read")
            ->assertOk()
            ->assertJsonPath('data.is_read', true);

        $this->actingAsApiToken($applicantToken)
            ->getJson('/api/v1/notifications?status=unread')
            ->assertOk()
            ->assertJsonMissing(['uuid' => $notifUuid]);

        $this->actingAsApiToken($applicantToken)
            ->getJson('/api/v1/notifications?status=read&search=records')
            ->assertOk()
            ->assertJsonFragment(['uuid' => $notifUuid]);

        $this->actingAsApiToken($recordsToken)
            ->getJson('/api/v1/staff/dashboard-stats')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'data' => [
                    'applications',
                    'inspections',
                    'evaluation_queue',
                    'records' => ['logbook_entries', 'archives'],
                    'notifications',
                    'pending_registrations',
                    'compliance' => ['open_notices'],
                    'pipeline',
                    'attention',
                    'recent_applications',
                    'recent_activity',
                    'meta',
                ],
            ]);

        $logbookUuid = $logbook->json('data.uuid');
        $printUrl = $logbook->json('data.print_url');
        $this->assertNotEmpty($printUrl);
        $this->assertStringContainsString('signature=', $printUrl);

        $this->get($printUrl)->assertOk();

        $this->get("/admin/logbooks/{$logbookUuid}/print")
            ->assertForbidden();
    }

    public function test_admin_can_manage_notification_templates(): void
    {
        $adminToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@csfp.local',
            'password' => 'Admin@12345',
        ])->assertOk()->json('data.token');

        $created = $this->actingAsApiToken($adminToken)
            ->postJson('/api/v1/admin/notification-templates', [
                'code' => 'test.custom',
                'name' => 'Custom test template',
                'channel' => 'in_app',
                'subject' => 'Hi {{name}}',
                'body_template' => 'Body: {{message}}',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'test.custom');

        $uuid = $created->json('data.uuid');

        $this->actingAsApiToken($adminToken)
            ->getJson('/api/v1/admin/notification-templates?search=status.update&per_page=50')
            ->assertOk()
            ->assertJsonFragment(['code' => 'status.update']);

        $this->actingAsApiToken($adminToken)
            ->deleteJson("/api/v1/admin/notification-templates/{$uuid}")
            ->assertOk();
    }

    public function test_application_submit_creates_in_app_and_email_notifications(): void
    {
        config(['mail.enabled' => true]);
        \Illuminate\Support\Facades\Mail::fake();

        $applicantToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->assertOk()->json('data.token');

        $draft = $this->actingAsApiToken($applicantToken)->postJson('/api/v1/applications', [
            'project_title' => 'Notify Me Building',
            'project_location' => 'San Fernando, Pampanga',
            'payload' => ['owner_name' => 'Demo'],
        ])->assertCreated();

        $uuid = $draft->json('data.uuid');

        $this->actingAsApiToken($applicantToken)
            ->postJson("/api/v1/applications/{$uuid}/submit")
            ->assertOk();

        $this->assertDatabaseHas('user_notifications', [
            'template_code' => 'application.submitted',
        ]);

        $list = $this->actingAsApiToken($applicantToken)
            ->getJson('/api/v1/notifications')
            ->assertOk();

        $this->assertGreaterThanOrEqual(1, (int) $list->json('data.unread_count'));
        $this->assertNotEmpty($list->json('data.items'));

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\Notifications\StatusUpdateMail::class);
    }

    public function test_applicant_cannot_create_logbook_entries(): void
    {
        $applicantToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->assertOk()->json('data.token');

        $this->actingAsApiToken($applicantToken)
            ->postJson('/api/v1/staff/logbook-entries', [
                'book_type' => 'g05',
                'subject' => 'Unauthorized',
            ])
            ->assertForbidden();
    }
}
