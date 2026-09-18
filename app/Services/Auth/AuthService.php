<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\RegistrationApprovalStatus;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class AuthService
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    /**
     * @return array{user: User, token: string}
     */
    public function login(string $email, string $password, string $deviceName = 'api'): array
    {
        $user = User::query()->where('email', $email)->first();
        $generic = __('Unable to sign in with those credentials.');

        if (! $user || ! Hash::check($password, $user->password)) {
            $this->audit->log('auth.login_failed', [
                'attempted_email' => $email,
                'reason' => 'invalid_credentials',
            ]);

            throw ValidationException::withMessages([
                'email' => [$generic],
            ]);
        }

        $status = $user->approval_status instanceof RegistrationApprovalStatus
            ? $user->approval_status
            : RegistrationApprovalStatus::tryFrom((string) $user->approval_status);

        if ($status === RegistrationApprovalStatus::Pending) {
            $this->audit->log('auth.login_failed', [
                'attempted_email' => $email,
                'user_id' => $user->uuid,
                'reason' => 'pending_approval',
            ], $user);

            throw ValidationException::withMessages([
                'email' => [__('Unable to sign in with those credentials. If your registration is pending approval, please wait for the email confirmation.')],
            ]);
        }

        if ($status === RegistrationApprovalStatus::Declined || ! $user->is_active || $status !== RegistrationApprovalStatus::Approved) {
            $this->audit->log('auth.login_failed', [
                'attempted_email' => $email,
                'user_id' => $user->uuid,
                'reason' => 'inactive_or_declined',
            ], $user);

            throw ValidationException::withMessages([
                'email' => [$generic],
            ]);
        }

        $token = $user->createToken($deviceName)->plainTextToken;

        $this->audit->log('auth.login', [
            'user_id' => $user->uuid,
            'email' => $user->email,
            'device' => $deviceName,
        ], $user);

        return compact('user', 'token');
    }

    /**
     * Local-only demo login: resolves password from config (never sent from the browser).
     *
     * @return array{user: User, token: string}
     */
    public function demoLogin(string $email, string $deviceName = 'web-demo'): array
    {
        if (! app()->environment('local') || ! config('apics_demo_users.enabled')) {
            throw ValidationException::withMessages([
                'email' => [__('Demo login is disabled.')],
            ]);
        }

        $demo = collect(config('apics_demo_users.users', []))
            ->first(fn (array $row): bool => ($row['email'] ?? '') === $email);

        if ($demo === null || empty($demo['password'])) {
            throw ValidationException::withMessages([
                'email' => [__('Unable to sign in with those credentials.')],
            ]);
        }

        return $this->login($email, (string) $demo['password'], $deviceName);
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();

        $this->audit->log('auth.logout', [
            'user_id' => $user->uuid,
            'email' => $user->email,
        ], $user);
    }
}
