<?php

declare(strict_types=1);

namespace App\Http\Requests\Evaluation;

use App\Enums\EvaluationResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('evaluations.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'result' => ['required', Rule::enum(EvaluationResult::class)],
            'findings' => ['nullable', 'array'],
            'findings.completeness' => ['nullable', 'array', 'max:40'],
            'findings.completeness.*.code' => ['nullable', 'string', 'max:40'],
            'findings.completeness.*.label' => ['nullable', 'string', 'max:255'],
            'findings.completeness.*.status' => ['nullable', 'string', Rule::in(['ok', 'fail', 'na'])],
            'findings.completeness.*.remarks' => ['nullable', 'string', 'max:1000'],
            'findings.technical' => ['nullable', 'array', 'max:40'],
            'findings.technical.*.code' => ['nullable', 'string', 'max:40'],
            'findings.technical.*.label' => ['nullable', 'string', 'max:255'],
            'findings.technical.*.status' => ['nullable', 'string', Rule::in(['ok', 'fail', 'na'])],
            'findings.technical.*.remarks' => ['nullable', 'string', 'max:1000'],
            'findings.overall_remarks' => ['nullable', 'string', 'max:5000'],
            'findings.discipline_remarks' => ['nullable', 'string', 'max:5000'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
