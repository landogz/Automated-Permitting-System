<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\UserNotification */
class UserNotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'template_code' => $this->template_code,
            'channel' => $this->channel?->value ?? $this->channel,
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
            'status' => $this->status?->value ?? $this->status,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'is_read' => $this->read_at !== null,
        ];
    }
}
