<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\RoutingSlip */
class RoutingSlipResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'slip_no' => $this->slip_no,
            'classification' => $this->classification?->value ?? $this->classification,
            'status' => $this->status,
            'generated_at' => $this->generated_at?->toIso8601String(),
            'template' => $this->whenLoaded('template', fn () => $this->template ? [
                'uuid' => $this->template->uuid,
                'code' => $this->template->code,
                'name' => $this->template->name,
                'classification' => $this->template->classification?->value ?? $this->template->classification,
            ] : null),
            'application' => $this->whenLoaded('application', fn () => [
                'uuid' => $this->application?->uuid,
                'application_no' => $this->application?->application_no,
                'project_title' => $this->application?->project_title,
                'status' => $this->application?->status,
            ]),
            'steps' => $this->whenLoaded('steps', fn () => $this->steps->map(fn ($step) => [
                'uuid' => $step->uuid,
                'step_order' => $step->step_order,
                'label' => $step->label,
                'status' => $step->status?->value ?? $step->status,
                'notes' => $step->notes,
                'started_at' => $step->started_at?->toIso8601String(),
                'completed_at' => $step->completed_at?->toIso8601String(),
                'department' => [
                    'uuid' => $step->department?->uuid,
                    'code' => $step->department?->code,
                    'name' => $step->department?->name,
                ],
            ])),
        ];
    }
}
