<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class ProfileService
{
    private const AVATAR_DISK = 'public';

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
     * Store a new profile photo and replace any previous avatar.
     */
    public function updateAvatar(User $user, UploadedFile $file): User
    {
        $extension = strtolower((string) ($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg'));
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw ValidationException::withMessages([
                'avatar' => ['Use a JPEG, PNG, or WebP image.'],
            ]);
        }

        $directory = 'avatars/'.$user->uuid;
        $filename = ((string) Str::ulid()).'.'.$extension;
        $path = $file->storeAs($directory, $filename, self::AVATAR_DISK);

        if (! is_string($path) || $path === '') {
            throw ValidationException::withMessages([
                'avatar' => ['Unable to store the profile photo. Try again.'],
            ]);
        }

        $previous = $user->avatar_path;
        $user->forceFill(['avatar_path' => $path])->save();
        $this->deleteAvatarFile($previous);

        $this->audit->log('user.avatar_updated', [
            'user_id' => $user->uuid,
        ]);

        return $user->fresh()->load('department');
    }

    /**
     * Remove the authenticated user's profile photo.
     */
    public function removeAvatar(User $user): User
    {
        $previous = $user->avatar_path;
        if ($previous) {
            $user->forceFill(['avatar_path' => null])->save();
            $this->deleteAvatarFile($previous);

            $this->audit->log('user.avatar_removed', [
                'user_id' => $user->uuid,
            ]);
        }

        return $user->fresh()->load('department');
    }

    public function avatarUrl(?User $user): ?string
    {
        if (! $user?->avatar_path) {
            return null;
        }

        return Storage::disk(self::AVATAR_DISK)->url($user->avatar_path);
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

    private function deleteAvatarFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk(self::AVATAR_DISK)->exists($path)) {
            Storage::disk(self::AVATAR_DISK)->delete($path);
        }
    }
}
