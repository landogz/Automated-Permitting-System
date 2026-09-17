<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Evaluation */
class EvaluationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'result' => $this->result?->value ?? $this->result,
            'findings' => $this->findings,
            'remarks' => $this->remarks,
            'decided_at' => $this->decided_at?->toIso8601String(),
            'evaluator' => $this->whenLoaded('evaluator', fn () => [
                'uuid' => $this->evaluator?->uuid,
                'name' => $this->evaluator?->name,
                'email' => $this->evaluator?->email,
            ]),
            'application' => $this->whenLoaded('application', fn () => [
                'uuid' => $this->application?->uuid,
                'application_no' => $this->application?->application_no,
            ]),
            'step' => $this->whenLoaded('step', fn () => $this->step ? [
                'uuid' => $this->step->uuid,
                'label' => $this->step->label,
            ] : null),
        ];
    }
}
