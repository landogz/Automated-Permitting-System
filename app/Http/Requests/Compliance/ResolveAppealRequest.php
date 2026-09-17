<?php

declare(strict_types=1);

namespace App\Http\Requests\Compliance;

use App\Enums\AppealStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResolveAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('compliance.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([AppealStatus::Upheld->value, AppealStatus::Denied->value])],
            'resolution_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
