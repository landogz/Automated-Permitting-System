<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class ManagedUserResource extends JsonResource
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
            'avatar_url' => $this->avatarUrl(),
            'is_active' => (bool) $this->is_active,
            'approval_status' => $this->approval_status?->value ?? $this->approval_status,
            'roles' => $this->getRoleNames()->values()->all(),
            'role' => $this->getRoleNames()->first(),
            'department' => $this->whenLoaded('department', fn () => $this->department ? [
                'uuid' => $this->department->uuid,
                'code' => $this->department->code,
                'name' => $this->department->name,
            ] : null),
            'registered_at' => $this->registered_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
        ];
    }
}
