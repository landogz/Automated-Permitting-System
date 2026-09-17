<?php

declare(strict_types=1);

namespace App\Http\Requests\Inspection;

use App\Enums\InspectionResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('inspections.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isFailed = $this->input('result') === InspectionResult::Failed->value;

        return [
            'result' => ['required', Rule::enum(InspectionResult::class)],
            'notes' => array_values(array_filter([
                $isFailed ? 'required' : 'nullable',
                'string',
                $isFailed ? 'min:10' : null,
                'max:5000',
            ])),
            'compliance_sheet' => ['nullable', 'array'],
            'electrical_form' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'notes.required' => 'Please describe what failed during the inspection so Compliance and the applicant know the problem.',
            'notes.min' => 'Please provide a clearer failure reason (at least 10 characters).',
        ];
    }
}
