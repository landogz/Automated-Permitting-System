<?php

declare(strict_types=1);

namespace App\Http\Requests\Evaluation;

use App\Enums\EvaluationResult;
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
        return [
            'routing_slip_step_uuid' => ['nullable', 'uuid', 'exists:routing_slip_steps,uuid'],
            'findings' => ['nullable', 'array'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
