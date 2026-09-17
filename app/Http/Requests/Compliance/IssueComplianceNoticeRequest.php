<?php

declare(strict_types=1);

namespace App\Http\Requests\Compliance;

use App\Enums\ComplianceNoticeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IssueComplianceNoticeRequest extends FormRequest
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
            'type' => ['required', Rule::enum(ComplianceNoticeType::class)],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10', 'max:10000'],
            'inspection_uuid' => ['nullable', 'uuid', 'exists:inspections,uuid'],
            'due_at' => ['nullable', 'date'],
        ];
    }
}
