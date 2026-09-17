<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class DemoLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app()->environment('local') && (bool) config('apics_demo_users.enabled');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
