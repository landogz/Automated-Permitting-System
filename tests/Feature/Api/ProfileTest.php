<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@csfp.local',
            'password' => 'Admin@12345',
            'device_name' => 'test',
        ])->json('data.token');

        $response = $this->withToken($token)->putJson('/api/v1/auth/profile', [
            'name' => 'Updated Admin',
            'email' => 'admin@csfp.local',
            'phone' => '09171234567',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.name', 'Updated Admin')
            ->assertJsonPath('data.phone', '09171234567');

        $this->assertDatabaseHas('users', [
            'email' => 'admin@csfp.local',
            'name' => 'Updated Admin',
            'phone' => '09171234567',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.profile_updated',
        ]);
    }

    public function test_authenticated_user_can_change_password(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
            'device_name' => 'test',
        ])->json('data.token');

        $this->withToken($token)->putJson('/api/v1/auth/password', [
            'current_password' => 'Applicant@123',
            'password' => 'NewPass@12345',
            'password_confirmation' => 'NewPass@12345',
        ])->assertOk()->assertJsonPath('status', true);

        $user = User::query()->where('email', 'applicant@csfp.local')->firstOrFail();
        $this->assertTrue(Hash::check('NewPass@12345', $user->password));

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.password_changed',
        ]);
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
            'device_name' => 'test',
        ])->json('data.token');

        $this->withToken($token)->putJson('/api/v1/auth/password', [
            'current_password' => 'WrongPass@1',
            'password' => 'NewPass@12345',
            'password_confirmation' => 'NewPass@12345',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_authenticated_user_can_upload_and_remove_avatar(): void
    {
        Storage::fake('public');

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@csfp.local',
            'password' => 'Admin@12345',
            'device_name' => 'test',
        ])->json('data.token');

        $file = UploadedFile::fake()->image('avatar.jpg', 240, 240);

        $upload = $this->withToken($token)->post('/api/v1/auth/profile/avatar', [
            'avatar' => $file,
        ], ['Accept' => 'application/json']);

        $upload->assertOk()
            ->assertJsonPath('status', true);

        $avatarUrl = $upload->json('data.avatar_url');
        $this->assertNotEmpty($avatarUrl);

        $user = User::query()->where('email', 'admin@csfp.local')->firstOrFail();
        $this->assertNotEmpty($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.avatar_updated',
        ]);

        $this->withToken($token)->deleteJson('/api/v1/auth/profile/avatar')
            ->assertOk()
            ->assertJsonPath('data.avatar_url', null);

        $user->refresh();
        $this->assertNull($user->avatar_path);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.avatar_removed',
        ]);
    }

    public function test_avatar_upload_rejects_non_image(): void
    {
        Storage::fake('public');

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@csfp.local',
            'password' => 'Admin@12345',
            'device_name' => 'test',
        ])->json('data.token');

        $this->withToken($token)->post('/api/v1/auth/profile/avatar', [
            'avatar' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
        ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }
}
