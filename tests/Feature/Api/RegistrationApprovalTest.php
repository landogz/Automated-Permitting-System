<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\RegistrationApprovalStatus;
use App\Mail\Registration\RegistrationApprovedMail;
use App\Mail\Registration\RegistrationDeclinedMail;
use App\Mail\Registration\RegistrationPendingAdminMail;
use App\Mail\Registration\RegistrationReceivedMail;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_applicant_can_self_register_and_cannot_login_until_approved(): void
    {
        config(['mail.enabled' => true]);
        Mail::fake();

        $register = $this->postJson('/api/v1/auth/register', [
            'name' => 'New Applicant',
            'email' => 'new.applicant@example.com',
            'phone' => '09171234567',
            'password' => 'Secure@123',
            'password_confirmation' => 'Secure@123',
        ]);

        $register->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.approval_status', 'pending');

        $this->assertDatabaseHas('users', [
            'email' => 'new.applicant@example.com',
            'approval_status' => 'pending',
            'is_active' => false,
        ]);

        Mail::assertSent(RegistrationReceivedMail::class);
        Mail::assertSent(RegistrationPendingAdminMail::class);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'new.applicant@example.com',
            'password' => 'Secure@123',
        ])->assertStatus(422)
            ->assertJsonPath('status', false);

        $adminToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@csfp.local',
            'password' => 'Admin@12345',
        ])->json('data.token');

        $uuid = $register->json('data.uuid');

        $this->withToken($adminToken)
            ->postJson("/api/v1/admin/registrations/{$uuid}/approve")
            ->assertOk()
            ->assertJsonPath('data.approval_status', 'approved');

        Mail::assertSent(RegistrationApprovedMail::class);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'new.applicant@example.com',
            'password' => 'Secure@123',
        ])->assertOk()
            ->assertJsonPath('status', true);
    }

    public function test_admin_can_decline_registration_and_login_stays_blocked(): void
    {
        config(['mail.enabled' => true]);
        Mail::fake();

        $uuid = $this->postJson('/api/v1/auth/register', [
            'name' => 'Declined Applicant',
            'email' => 'declined@example.com',
            'password' => 'Secure@123',
            'password_confirmation' => 'Secure@123',
        ])->json('data.uuid');

        $adminToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@csfp.local',
            'password' => 'Admin@12345',
        ])->json('data.token');

        $this->withToken($adminToken)
            ->postJson("/api/v1/admin/registrations/{$uuid}/decline", [
                'reason' => 'Incomplete identity documents',
            ])
            ->assertOk()
            ->assertJsonPath('data.approval_status', 'declined');

        Mail::assertSent(RegistrationDeclinedMail::class);

        $user = User::query()->where('email', 'declined@example.com')->firstOrFail();
        $this->assertSame(RegistrationApprovalStatus::Declined, $user->approval_status);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'declined@example.com',
            'password' => 'Secure@123',
        ])->assertStatus(422);
    }

    public function test_non_admin_cannot_list_registrations(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/registrations')
            ->assertForbidden();
    }

    public function test_mail_disabled_skips_registration_emails(): void
    {
        config(['mail.enabled' => false]);
        Mail::fake();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'No Mail Applicant',
            'email' => 'nomail@example.com',
            'password' => 'Secure@123',
            'password_confirmation' => 'Secure@123',
        ])->assertCreated();

        Mail::assertNothingSent();
    }
}
