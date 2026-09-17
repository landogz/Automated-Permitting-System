<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ApplicationDocument */
class ApplicationDocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $mime = (string) ($this->mime_type ?? '');

        return [
            'uuid' => $this->uuid,
            'label' => $this->label,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'is_pdf' => str_contains($mime, 'pdf'),
            'is_image' => str_starts_with($mime, 'image/'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
