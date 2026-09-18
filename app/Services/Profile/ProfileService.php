<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class ProfileService
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    /**
     * Update the authenticated user's display profile (name, email, phone).
     *
     * @param  array{name: string, email: string, phone?: string|null}  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);
        $user->save();

        $this->audit->log('user.profile_updated', [
            'user_id' => $user->uuid,
            'email' => $user->email,
        ]);

        return $user->fresh()->load('department');
    }

    /**
     * Change password after verifying the current password.
     *
     * @param  array{current_password: string, password: string}  $data
     */
    public function changePassword(User $user, array $data): void
    {
        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('The current password is incorrect.')],
            ]);
        }

        if (Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => [__('Choose a new password that is different from your current password.')],
            ]);
        }

        $user->forceFill([
            'password' => $data['password'],
        ])->save();

        $this->audit->log('user.password_changed', [
            'user_id' => $user->uuid,
        ]);
    }
}
