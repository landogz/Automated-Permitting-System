<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Support\Inspection\InspectionFormCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

/** @mixin \App\Models\Inspection */
class InspectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $printUrls = null;
        if ($request->user()?->can('inspections.manage')) {
            $printUrls = [];
            foreach (InspectionFormCatalog::documentCodes() as $doc) {
                $printUrls[$doc] = URL::temporarySignedRoute(
                    'admin.inspections.print',
                    now()->addMinutes(15),
                    ['inspection' => $this->uuid, 'doc' => $doc],
                );
            }
        }

        return [
            'uuid' => $this->uuid,
            'inspection_no' => $this->inspection_no,
            'type' => $this->type,
            'status' => $this->status?->value ?? $this->status,
            'result' => $this->result?->value ?? $this->result,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'notes' => $this->notes,
            'team_inspectors' => InspectionFormCatalog::normalizeTeamInspectors($this->team_inspectors),
            'schedule_sheet' => is_array($this->schedule_sheet) ? $this->schedule_sheet : [],
            'inspector_notes' => InspectionFormCatalog::normalizeInspectorNotes($this->inspector_notes),
            'compliance_sheet' => InspectionFormCatalog::normalizeComplianceSheet($this->compliance_sheet),
            'electrical_form' => InspectionFormCatalog::normalizeElectricalForm($this->electrical_form),
            'requires_electrical_form' => InspectionFormCatalog::requiresElectricalForm(
                is_string($this->type) ? $this->type : null,
            ),
            'print_urls' => $printUrls,
            'application' => $this->whenLoaded('application', fn () => [
                'uuid' => $this->application?->uuid,
                'application_no' => $this->application?->application_no,
                'project_title' => $this->application?->project_title,
                'status' => $this->application?->status,
            ]),
            'inspector' => $this->whenLoaded('inspector', fn () => [
                'uuid' => $this->inspector?->uuid,
                'name' => $this->inspector?->name,
            ]),
        ];
    }
}
