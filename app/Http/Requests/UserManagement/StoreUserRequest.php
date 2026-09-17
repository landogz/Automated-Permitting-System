<?php

declare(strict_types=1);

namespace App\Http\Requests\UserManagement;

use App\Services\UserManagement\UserManagementService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => ['required', 'string', Rule::in(UserManagementService::STAFF_ROLES)],
            'department_uuid' => ['nullable', 'uuid', 'exists:departments,uuid'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
