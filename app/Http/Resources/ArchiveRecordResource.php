<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ArchiveRecord */
class ArchiveRecordResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'archive_no' => $this->archive_no,
            'title' => $this->title,
            'storage_location' => $this->storage_location,
            'media_type' => $this->media_type,
            'checksum' => $this->checksum,
            'notes' => $this->notes,
            'meta' => $this->meta,
            'archived_at' => $this->archived_at?->toIso8601String(),
            'application' => $this->whenLoaded('application', fn () => [
                'uuid' => $this->application?->uuid,
                'application_no' => $this->application?->application_no,
                'project_title' => $this->application?->project_title,
                'project_location' => $this->application?->project_location,
                'status' => $this->application?->status,
                'classification' => $this->application?->classification,
            ]),
            'archived_by' => $this->whenLoaded('archivedByUser', fn () => [
                'uuid' => $this->archivedByUser?->uuid,
                'name' => $this->archivedByUser?->name,
            ]),
        ];
    }
}
