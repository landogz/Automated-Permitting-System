<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

/** @mixin \App\Models\LogbookEntry */
class LogbookEntryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $printUrl = null;
        if ($request->user()?->can('records.manage')) {
            $printUrl = URL::temporarySignedRoute(
                'admin.logbooks.print',
                now()->addMinutes(15),
                ['logbook' => $this->uuid],
            );
        }

        return [
            'uuid' => $this->uuid,
            'entry_no' => $this->entry_no,
            'book_type' => $this->book_type?->value ?? $this->book_type,
            'subject' => $this->subject,
            'recipient_name' => $this->recipient_name,
            'recipient_contact' => $this->recipient_contact,
            'notes' => $this->notes,
            'meta' => $this->meta,
            'recorded_at' => $this->recorded_at?->toIso8601String(),
            'application' => $this->whenLoaded('application', fn () => [
                'uuid' => $this->application?->uuid,
                'application_no' => $this->application?->application_no,
                'project_title' => $this->application?->project_title,
                'project_location' => $this->application?->project_location,
                'status' => $this->application?->status,
            ]),
            'recorded_by' => $this->whenLoaded('recordedByUser', fn () => [
                'uuid' => $this->recordedByUser?->uuid,
                'name' => $this->recordedByUser?->name,
            ]),
            'print_url' => $printUrl,
        ];
    }
}
