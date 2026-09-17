<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\OrderOfPayment */
class OrderOfPaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payload = is_array($this->application?->payload) ? $this->application->payload : [];

        return [
            'uuid' => $this->uuid,
            'oop_no' => $this->oop_no,
            'status' => $this->status?->value ?? $this->status,
            'total_amount' => $this->total_amount,
            'issued_at' => $this->issued_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'payment_reference' => $this->payment_reference,
            'cto_stub_reference' => $this->cto_stub_reference,
            'override_reason' => $this->override_reason,
            'line_count' => $this->whenLoaded('lines', fn () => $this->lines->count()),
            'application' => $this->whenLoaded('application', fn () => [
                'uuid' => $this->application?->uuid,
                'application_no' => $this->application?->application_no,
                'project_title' => $this->application?->project_title,
                'project_location' => $this->application?->project_location,
                'status' => $this->application?->status,
                'classification' => $this->application?->classification,
                'lot_area' => $payload['lot_area'] ?? null,
                'floor_area' => $payload['floor_area'] ?? null,
                'occupancy' => $payload['occupancy'] ?? null,
                'owner_name' => $payload['owner_name'] ?? null,
            ]),
            'lines' => $this->whenLoaded('lines', fn () => $this->lines->map(fn ($line) => [
                'uuid' => $line->uuid,
                'agency' => $line->agency?->value ?? $line->agency,
                'description' => $line->description,
                'amount' => $line->amount,
                'external_stub_reference' => $line->external_stub_reference,
                'line_order' => $line->line_order,
            ])),
            'assessed_by' => $this->whenLoaded('assessedByUser', fn () => [
                'uuid' => $this->assessedByUser?->uuid,
                'name' => $this->assessedByUser?->name,
            ]),
        ];
    }
}
