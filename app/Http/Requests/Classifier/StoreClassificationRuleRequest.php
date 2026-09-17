<?php

declare(strict_types=1);

namespace App\Http\Requests\Classifier;

use App\Enums\PermitClassification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassificationRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('workflow.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:classification_rules,code'],
            'name' => ['required', 'string', 'max:255'],
            'classification' => ['required', Rule::enum(PermitClassification::class)],
            'priority' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'conditions' => ['nullable', 'array'],
            'conditions.*.field' => ['required_with:conditions', 'string', 'max:100'],
            'conditions.*.operator' => ['required_with:conditions', 'string', 'max:20'],
            'conditions.*.value' => ['nullable'],
            'sla_hours' => ['nullable', 'integer', 'min:1', 'max:8760'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
