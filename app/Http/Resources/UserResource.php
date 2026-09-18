<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
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
            'is_active' => $this->is_active,
            'approval_status' => $this->approval_status?->value ?? $this->approval_status,
            'roles' => $this->getRoleNames()->values()->all(),
            'permissions' => $this->getAllPermissions()->pluck('name')->values()->all(),
            'department' => $this->whenLoaded('department', fn () => [
                'uuid' => $this->department?->uuid,
                'code' => $this->department?->code,
                'name' => $this->department?->name,
            ]),
        ];
    }
}
