<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ComplianceNotice */
class ComplianceNoticeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payload = is_array($this->application?->payload) ? $this->application->payload : [];

        return [
            'uuid' => $this->uuid,
            'notice_no' => $this->notice_no,
            'type' => $this->type?->value ?? $this->type,
            'status' => $this->status?->value ?? $this->status,
            'title' => $this->title,
            'body' => $this->body,
            'issued_at' => $this->issued_at?->toIso8601String(),
            'due_at' => $this->due_at?->toIso8601String(),
            'appeal_count' => $this->whenLoaded('appeals', fn () => $this->appeals->count()),
            'pending_appeal_uuid' => $this->whenLoaded('appeals', function () {
                $pending = $this->appeals->first(
                    fn ($appeal) => ($appeal->status?->value ?? $appeal->status) === 'pending'
                );

                return $pending?->uuid;
            }),
            'application' => $this->whenLoaded('application', fn () => [
                'uuid' => $this->application?->uuid,
                'application_no' => $this->application?->application_no,
                'project_title' => $this->application?->project_title,
                'project_location' => $this->application?->project_location,
                'status' => $this->application?->status,
                'classification' => $this->application?->classification,
                'owner_name' => $payload['owner_name'] ?? null,
                'occupancy' => $payload['occupancy'] ?? null,
            ]),
            'inspection' => $this->whenLoaded('inspection', fn () => [
                'uuid' => $this->inspection?->uuid,
                'inspection_no' => $this->inspection?->inspection_no,
                'type' => $this->inspection?->type,
                'result' => $this->inspection?->result?->value ?? $this->inspection?->result,
                'status' => $this->inspection?->status?->value ?? $this->inspection?->status,
                'notes' => $this->inspection?->notes,
                'completed_at' => $this->inspection?->completed_at?->toIso8601String(),
            ]),
            'issued_by' => $this->whenLoaded('issuedByUser', fn () => [
                'uuid' => $this->issuedByUser?->uuid,
                'name' => $this->issuedByUser?->name,
            ]),
            'appeals' => $this->whenLoaded('appeals', fn () => $this->appeals->map(fn ($appeal) => [
                'uuid' => $appeal->uuid,
                'status' => $appeal->status?->value ?? $appeal->status,
                'grounds' => $appeal->grounds,
                'resolution_notes' => $appeal->resolution_notes,
                'filed_at' => $appeal->created_at?->toIso8601String(),
                'resolved_at' => $appeal->resolved_at?->toIso8601String(),
                'filed_by' => $appeal->relationLoaded('filedByUser') ? [
                    'uuid' => $appeal->filedByUser?->uuid,
                    'name' => $appeal->filedByUser?->name,
                ] : null,
                'resolved_by' => $appeal->relationLoaded('resolvedByUser') ? [
                    'uuid' => $appeal->resolvedByUser?->uuid,
                    'name' => $appeal->resolvedByUser?->name,
                ] : null,
            ])),
        ];
    }
}
