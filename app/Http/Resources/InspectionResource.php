<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Inspection */
class InspectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
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
            'compliance_sheet' => $this->compliance_sheet,
            'electrical_form' => $this->electrical_form,
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
