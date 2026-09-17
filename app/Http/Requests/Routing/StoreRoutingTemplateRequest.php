<?php

declare(strict_types=1);

namespace App\Http\Requests\Routing;

use App\Enums\PermitClassification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoutingTemplateRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:50', 'unique:routing_templates,code'],
            'name' => ['required', 'string', 'max:255'],
            'classification' => ['required', Rule::enum(PermitClassification::class)],
            'is_active' => ['nullable', 'boolean'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.department_uuid' => ['required', 'uuid', 'exists:departments,uuid'],
            'steps.*.label' => ['required', 'string', 'max:255'],
            'steps.*.step_order' => ['nullable', 'integer', 'min:1'],
            'steps.*.sla_hours' => ['nullable', 'integer', 'min:1', 'max:8760'],
        ];
    }
}
