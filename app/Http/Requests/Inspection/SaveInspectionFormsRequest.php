<?php

declare(strict_types=1);

namespace App\Http\Requests\Inspection;

use App\Enums\InspectionResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveInspectionFormsRequest extends FormRequest
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
            'result' => ['nullable', Rule::enum(InspectionResult::class)],
            'notes' => ['nullable', 'string', 'max:5000'],
            'inspector_notes' => ['nullable', 'array'],
            'inspector_notes.weather' => ['nullable', 'string', 'max:255'],
            'inspector_notes.site_conditions' => ['nullable', 'string', 'max:2000'],
            'inspector_notes.findings' => ['nullable', 'string', 'max:5000'],
            'inspector_notes.observed_defects' => ['nullable', 'string', 'max:5000'],
            'inspector_notes.recommendations' => ['nullable', 'string', 'max:5000'],
            'compliance_sheet' => ['nullable', 'array'],
            'compliance_sheet.items' => ['nullable', 'array', 'max:40'],
            'compliance_sheet.items.*.code' => ['nullable', 'string', 'max:40'],
            'compliance_sheet.items.*.label' => ['nullable', 'string', 'max:255'],
            'compliance_sheet.items.*.status' => ['nullable', 'string', Rule::in(['ok', 'fail', 'na'])],
            'compliance_sheet.items.*.remarks' => ['nullable', 'string', 'max:1000'],
            'compliance_sheet.overall_remarks' => ['nullable', 'string', 'max:5000'],
            'electrical_form' => ['nullable', 'array'],
            'electrical_form.service_entrance' => ['nullable', 'string', Rule::in(['ok', 'fail', 'na'])],
            'electrical_form.grounding' => ['nullable', 'string', Rule::in(['ok', 'fail', 'na'])],
            'electrical_form.panel_boards' => ['nullable', 'string', Rule::in(['ok', 'fail', 'na'])],
            'electrical_form.wiring_methods' => ['nullable', 'string', Rule::in(['ok', 'fail', 'na'])],
            'electrical_form.fixtures_devices' => ['nullable', 'string', Rule::in(['ok', 'fail', 'na'])],
            'electrical_form.load_schedule' => ['nullable', 'string', Rule::in(['ok', 'fail', 'na'])],
            'electrical_form.remarks' => ['nullable', 'string', 'max:5000'],
            'electrical_form.result' => ['nullable', 'string', Rule::in(['passed', 'failed', 'conditional', 'na', 'ok', 'fail'])],
            'team_inspectors' => ['nullable', 'array', 'max:20'],
            'team_inspectors.*.name' => ['required_with:team_inspectors', 'string', 'max:120'],
            'team_inspectors.*.role' => ['nullable', 'string', 'max:80'],
            'team_inspectors.*.discipline' => ['nullable', 'string', 'max:40'],
        ];
    }
}
