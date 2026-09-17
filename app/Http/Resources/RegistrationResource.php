<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class RegistrationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'approval_status' => $this->approval_status?->value ?? $this->approval_status,
            'approval_notes' => $this->approval_notes,
            'is_active' => $this->is_active,
            'registered_at' => $this->registered_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'declined_at' => $this->declined_at?->toIso8601String(),
            'reviewed_by' => $this->whenLoaded('reviewedBy', fn () => $this->reviewedBy ? [
                'uuid' => $this->reviewedBy->uuid,
                'name' => $this->reviewedBy->name,
                'email' => $this->reviewedBy->email,
            ] : null),
        ];
    }
}
