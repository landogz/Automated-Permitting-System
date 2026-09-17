<?php

declare(strict_types=1);

namespace App\Http\Requests\Fee;

use App\Enums\FeeAgency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeeRuleRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:50', 'unique:fee_rules,code'],
            'name' => ['required', 'string', 'max:255'],
            'agency' => ['required', Rule::enum(FeeAgency::class)],
            'basis' => ['required', 'string', Rule::in(['fixed', 'area_rate'])],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'rate' => ['nullable', 'numeric', 'min:0'],
            'conditions' => ['nullable', 'array'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
