<?php

declare(strict_types=1);

namespace App\Http\Requests\Notification;

use App\Enums\NotificationChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        return $user->can('applications.manage')
            || $user->can('evaluations.manage')
            || $user->can('compliance.manage')
            || $user->can('records.manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_uuid' => ['required', 'uuid', 'exists:users,uuid'],
            'template_code' => ['nullable', 'string', 'max:50'],
            'channel' => ['nullable', Rule::enum(NotificationChannel::class)],
            'message' => ['nullable', 'string', 'max:5000'],
            'vars' => ['nullable', 'array'],
            'url' => ['nullable', 'string', 'max:255'],
        ];
    }
}
