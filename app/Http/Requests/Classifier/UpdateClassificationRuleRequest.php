<?php

declare(strict_types=1);

namespace App\Http\Requests\Classifier;

use App\Enums\PermitClassification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassificationRuleRequest extends FormRequest
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
        /** @var \App\Models\ClassificationRule $rule */
        $rule = $this->route('classification_rule');

        return [
            'code' => ['sometimes', 'string', 'max:50', Rule::unique('classification_rules', 'code')->ignore($rule->id)],
            'name' => ['sometimes', 'string', 'max:255'],
            'classification' => ['sometimes', Rule::enum(PermitClassification::class)],
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
