<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\RoutingTemplate */
class RoutingTemplateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'classification' => $this->classification?->value ?? $this->classification,
            'is_active' => $this->is_active,
            'step_count' => $this->whenLoaded('steps', fn () => $this->steps->count()),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'steps' => $this->whenLoaded('steps', fn () => $this->steps->map(fn ($step) => [
                'uuid' => $step->uuid,
                'step_order' => $step->step_order,
                'label' => $step->label,
                'sla_hours' => $step->sla_hours,
                'department' => $step->relationLoaded('department') ? [
                    'uuid' => $step->department?->uuid,
                    'code' => $step->department?->code,
                    'name' => $step->department?->name,
                ] : null,
            ])),
        ];
    }
}
