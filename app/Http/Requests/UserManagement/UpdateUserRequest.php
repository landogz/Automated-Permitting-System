<?php

declare(strict_types=1);

namespace App\Http\Requests\UserManagement;

use App\Enums\RegistrationApprovalStatus;
use App\Services\UserManagement\UserManagementService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var \App\Models\User $user */
        $user = $this->route('user');

        $assignableRoles = UserManagementService::STAFF_ROLES;
        if (! $this->user()?->hasRole('admin')) {
            $assignableRoles = array_values(array_filter(
                $assignableRoles,
                static fn (string $role): bool => $role !== 'admin',
            ));
        }

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => ['sometimes', 'required', 'string', Rule::in($assignableRoles)],
            'department_uuid' => ['nullable', 'uuid', 'exists:departments,uuid'],
            'is_active' => ['sometimes', 'boolean'],
            'approval_status' => ['sometimes', 'string', Rule::in(array_column(RegistrationApprovalStatus::cases(), 'value'))],
        ];
    }
}
