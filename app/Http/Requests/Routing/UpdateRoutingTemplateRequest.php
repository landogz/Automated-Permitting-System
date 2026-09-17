<?php

declare(strict_types=1);

namespace App\Http\Requests\Routing;

use App\Enums\PermitClassification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoutingTemplateRequest extends FormRequest
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
        /** @var \App\Models\RoutingTemplate $template */
        $template = $this->route('routing_template');

        return [
            'code' => ['sometimes', 'string', 'max:50', Rule::unique('routing_templates', 'code')->ignore($template->id)],
            'name' => ['sometimes', 'string', 'max:255'],
            'classification' => ['sometimes', Rule::enum(PermitClassification::class)],
            'is_active' => ['nullable', 'boolean'],
            'steps' => ['sometimes', 'array', 'min:1'],
            'steps.*.department_uuid' => ['required_with:steps', 'uuid', 'exists:departments,uuid'],
            'steps.*.label' => ['required_with:steps', 'string', 'max:255'],
            'steps.*.step_order' => ['nullable', 'integer', 'min:1'],
            'steps.*.sla_hours' => ['nullable', 'integer', 'min:1', 'max:8760'],
        ];
    }
}
