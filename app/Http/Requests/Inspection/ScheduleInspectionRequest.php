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
            'schedule_sheet' => ['nullable', 'array'],
            'schedule_sheet.purpose' => ['nullable', 'string', 'max:2000'],
            'schedule_sheet.meeting_point' => ['nullable', 'string', 'max:500'],
            'schedule_sheet.disciplines' => ['nullable', 'array'],
            'schedule_sheet.disciplines.*' => ['string', 'max:40'],
            'schedule_sheet.remarks' => ['nullable', 'string', 'max:2000'],
            'schedule_sheet.coordination_notes' => ['nullable', 'string', 'max:2000'],
            'team_inspectors' => ['nullable', 'array', 'max:20'],
            'team_inspectors.*.name' => ['required_with:team_inspectors', 'string', 'max:120'],
            'team_inspectors.*.role' => ['nullable', 'string', 'max:80'],
            'team_inspectors.*.discipline' => ['nullable', 'string', 'max:40'],
            'compliance_sheet' => ['nullable', 'array'],
            'electrical_form' => ['nullable', 'array'],
        ];
    }
}
