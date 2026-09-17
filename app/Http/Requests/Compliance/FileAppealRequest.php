<?php

declare(strict_types=1);

namespace App\Http\Requests\Compliance;

use App\Models\ComplianceNotice;
use Illuminate\Foundation\Http\FormRequest;

class FileAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        if ($user->can('compliance.manage')) {
            return true;
        }

        /** @var ComplianceNotice|null $notice */
        $notice = $this->route('notice');
        if (! $notice instanceof ComplianceNotice) {
            return false;
        }

        $notice->loadMissing('application');

        return (int) ($notice->application?->user_id ?? 0) === (int) $user->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'grounds' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'grounds.required' => 'Please explain why you are appealing this notice.',
            'grounds.min' => 'Appeal grounds must be at least 10 characters.',
        ];
    }
}
