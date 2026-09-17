<?php

declare(strict_types=1);

namespace App\Http\Requests\Inspection;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleInspectionRequest extends FormRequest
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
        return [
            'type' => ['nullable', 'string', 'max:40'],
            'scheduled_at' => ['nullable', 'date'],
            'inspector_uuid' => ['nullable', 'uuid', 'exists:users,uuid'],
            'location' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'compliance_sheet' => ['nullable', 'array'],
            'electrical_form' => ['nullable', 'array'],
        ];
    }
}
