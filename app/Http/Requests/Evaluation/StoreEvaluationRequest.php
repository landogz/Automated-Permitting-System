<?php

declare(strict_types=1);

namespace App\Http\Requests\Evaluation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEvaluationRequest extends FormRequest
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
        // Accept legacy flat findings and structured QMS-63/64 shapes; service normalizes.
        return [
            'routing_slip_step_uuid' => ['nullable', 'uuid', 'exists:routing_slip_steps,uuid'],
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
            'findings.*.item' => ['nullable', 'string', 'max:255'],
            'findings.*.label' => ['nullable', 'string', 'max:255'],
            'findings.*.code' => ['nullable', 'string', 'max:40'],
            'findings.*.status' => ['nullable', 'string', Rule::in(['ok', 'fail', 'na'])],
            'findings.*.remarks' => ['nullable', 'string', 'max:1000'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated($key, $default);
        if ($key !== null) {
            return $data;
        }

        // Preserve legacy list findings when structured keys are absent.
        $rawFindings = $this->input('findings');
        if (is_array($rawFindings) && ! isset($rawFindings['completeness']) && ! isset($rawFindings['technical'])) {
            $data['findings'] = $rawFindings;
        }

        return $data;
    }
}
