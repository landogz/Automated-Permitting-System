<?php

declare(strict_types=1);

namespace App\Http\Requests\Fee;

use App\Enums\FeeAgency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFeeRuleRequest extends FormRequest
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
        /** @var \App\Models\FeeRule $rule */
        $rule = $this->route('fee_rule');

        return [
            'code' => ['sometimes', 'string', 'max:50', Rule::unique('fee_rules', 'code')->ignore($rule->id)],
            'name' => ['sometimes', 'string', 'max:255'],
            'agency' => ['sometimes', Rule::enum(FeeAgency::class)],
            'basis' => ['sometimes', 'string', Rule::in(['fixed', 'area_rate'])],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'rate' => ['nullable', 'numeric', 'min:0'],
            'conditions' => ['nullable', 'array'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
