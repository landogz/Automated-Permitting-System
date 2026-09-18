<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PermitApplication */
class PermitApplicationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'application_no' => $this->application_no,
            'status' => $this->status,
            'classification' => $this->classification,
            'classified_by_rule' => $this->classified_by_rule,
            'classified_at' => $this->classified_at?->toIso8601String(),
            'project_title' => $this->project_title,
            'project_location' => $this->project_location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'payload' => $this->payload,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'applicant' => $this->whenLoaded('user', fn () => [
                'uuid' => $this->user?->uuid,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
            ]),
            'form' => $this->whenLoaded('formDefinition', fn () => [
                'uuid' => $this->formDefinition?->uuid,
                'code' => $this->formDefinition?->code,
                'title' => $this->formDefinition?->title,
                'schema' => $this->formDefinition?->schema,
                'required_attachments' => $this->formDefinition?->required_attachments,
            ]),
            'documents' => $this->whenLoaded('documents', fn () => ApplicationDocumentResource::collection($this->documents)),
            'routing_slips' => $this->whenLoaded('routingSlips', fn () => RoutingSlipResource::collection($this->routingSlips)),
            'evaluations' => $this->whenLoaded('evaluations', fn () => EvaluationResource::collection($this->evaluations)),
            'inspections' => $this->whenLoaded('inspections', fn () => InspectionResource::collection($this->inspections)),
            'orders_of_payment' => $this->whenLoaded(
                'ordersOfPayment',
                fn () => OrderOfPaymentResource::collection($this->ordersOfPayment),
            ),
            'compliance_notices' => $this->whenLoaded(
                'complianceNotices',
                fn () => ComplianceNoticeResource::collection($this->complianceNotices),
            ),
        ];
    }
}
