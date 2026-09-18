<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Support\Evaluation\EvaluationFormCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

/** @mixin \App\Models\Evaluation */
class EvaluationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $printUrls = null;
        if ($request->user()?->can('evaluations.manage')) {
            $printUrls = [
                'qms-63' => URL::temporarySignedRoute(
                    'admin.evaluations.print',
                    now()->addMinutes(15),
                    ['evaluation' => $this->uuid, 'doc' => 'qms-63'],
                ),
                'qms-64' => URL::temporarySignedRoute(
                    'admin.evaluations.print',
                    now()->addMinutes(15),
                    ['evaluation' => $this->uuid, 'doc' => 'qms-64'],
                ),
            ];
        }

        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'result' => $this->result?->value ?? $this->result,
            'findings' => EvaluationFormCatalog::normalizeFindings($this->findings),
            'remarks' => $this->remarks,
            'decided_at' => $this->decided_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'print_urls' => $printUrls,
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
                'department' => $this->step->relationLoaded('department') && $this->step->department ? [
                    'uuid' => $this->step->department->uuid,
                    'code' => $this->step->department->code,
                    'name' => $this->step->department->name,
                ] : null,
            ] : null),
        ];
    }
}
