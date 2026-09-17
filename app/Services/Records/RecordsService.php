<?php

declare(strict_types=1);

namespace App\Services\Records;

use App\Enums\LogbookBookType;
use App\Models\ArchiveRecord;
use App\Models\LogbookEntry;
use App\Models\NumberingSeries;
use App\Models\PermitApplication;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Notification\WorkflowNotifier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RecordsService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly WorkflowNotifier $notifier,
    ) {
    }

    public function listLogbook(string $search = '', ?string $bookType = null, int $perPage = 25): LengthAwarePaginator
    {
        return LogbookEntry::query()
            ->with([
                'application:id,uuid,application_no,project_title,project_location,status',
                'recordedByUser:id,uuid,name',
            ])
            ->when($bookType !== null && $bookType !== '' && $bookType !== 'all', fn ($q) => $q->where('book_type', $bookType))
            ->when($search !== '', function ($q) use ($search): void {
                $like = '%'.$search.'%';
                $q->where(function ($inner) use ($like): void {
                    $inner->where('entry_no', 'like', $like)
                        ->orWhere('subject', 'like', $like)
                        ->orWhere('recipient_name', 'like', $like)
                        ->orWhere('notes', 'like', $like)
                        ->orWhereHas('application', fn ($app) => $app->where('application_no', 'like', $like)
                            ->orWhere('project_title', 'like', $like));
                });
            })
            ->latest('recorded_at')
            ->paginate(min(max($perPage, 1), 100));
    }

    /**
     * @return array{g01_releasing: int, o02_occupancy: int, e_series: int, g05: int, g06: int, total: int}
     */
    public function logbookSummary(): array
    {
        $counts = LogbookEntry::query()
            ->selectRaw('book_type, COUNT(*) as aggregate')
            ->groupBy('book_type')
            ->pluck('aggregate', 'book_type');

        return [
            'g01_releasing' => (int) ($counts[LogbookBookType::G01Releasing->value] ?? 0),
            'o02_occupancy' => (int) ($counts[LogbookBookType::O02Occupancy->value] ?? 0),
            'e_series' => (int) ($counts[LogbookBookType::ESeries->value] ?? 0),
            'g05' => (int) ($counts[LogbookBookType::G05->value] ?? 0),
            'g06' => (int) ($counts[LogbookBookType::G06->value] ?? 0),
            'total' => LogbookEntry::query()->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createLogbookEntry(User $actor, array $data): LogbookEntry
    {
        $entry = DB::transaction(function () use ($actor, $data): LogbookEntry {
            $bookType = LogbookBookType::from((string) $data['book_type']);
            $application = null;
            if (! empty($data['application_uuid'])) {
                $application = PermitApplication::query()->where('uuid', $data['application_uuid'])->first();
                if (! $application) {
                    throw ValidationException::withMessages([
                        'application_uuid' => ['Application not found.'],
                    ]);
                }
            }

            $prefix = match ($bookType) {
                LogbookBookType::G01Releasing => 'G01-'.date('Y').'-',
                LogbookBookType::O02Occupancy => 'O02-'.date('Y').'-',
                LogbookBookType::ESeries => 'E-'.date('Y').'-',
                LogbookBookType::G05 => 'G05-'.date('Y').'-',
                LogbookBookType::G06 => 'G06-'.date('Y').'-',
            };

            $entry = LogbookEntry::query()->create([
                'entry_no' => $this->nextNumber('logbook_'.$bookType->value, $prefix),
                'book_type' => $bookType->value,
                'permit_application_id' => $application?->id,
                'subject' => $data['subject'],
                'recipient_name' => $data['recipient_name'] ?? null,
                'recipient_contact' => $data['recipient_contact'] ?? null,
                'notes' => $data['notes'] ?? null,
                'meta' => $data['meta'] ?? [],
                'recorded_by' => $actor->id,
                'recorded_at' => now(),
            ]);

            if ($application && $bookType === LogbookBookType::G01Releasing) {
                $application->update(['status' => 'released']);
            }

            $this->audit->log('logbook_entry.created', [
                'entry_id' => $entry->uuid,
                'entry_no' => $entry->entry_no,
                'book_type' => $bookType->value,
                'application_id' => $application?->uuid,
            ]);

            return $entry->load(['application.user', 'recordedByUser']);
        });

        if ($entry->book_type === LogbookBookType::G01Releasing) {
            $this->notifier->permitReleased($entry);
        }

        return $entry;
    }

    public function listArchives(string $search = '', ?string $mediaType = null, int $perPage = 25): LengthAwarePaginator
    {
        return ArchiveRecord::query()
            ->with([
                'application:id,uuid,application_no,project_title,project_location,status,classification',
                'archivedByUser:id,uuid,name',
            ])
            ->when($mediaType !== null && $mediaType !== '' && $mediaType !== 'all', fn ($q) => $q->where('media_type', $mediaType))
            ->when($search !== '', function ($q) use ($search): void {
                $like = '%'.$search.'%';
                $q->where(function ($inner) use ($like): void {
                    $inner->where('archive_no', 'like', $like)
                        ->orWhere('title', 'like', $like)
                        ->orWhere('storage_location', 'like', $like)
                        ->orWhere('notes', 'like', $like)
                        ->orWhere('checksum', 'like', $like)
                        ->orWhereHas('application', fn ($app) => $app->where('application_no', 'like', $like)
                            ->orWhere('project_title', 'like', $like));
                });
            })
            ->latest('archived_at')
            ->paginate(min(max($perPage, 1), 100));
    }

    /**
     * @return array{digital: int, physical: int, hybrid: int, total: int}
     */
    public function archiveSummary(): array
    {
        $counts = ArchiveRecord::query()
            ->selectRaw('media_type, COUNT(*) as aggregate')
            ->groupBy('media_type')
            ->pluck('aggregate', 'media_type');

        return [
            'digital' => (int) ($counts['digital'] ?? 0),
            'physical' => (int) ($counts['physical'] ?? 0),
            'hybrid' => (int) ($counts['hybrid'] ?? 0),
            'total' => ArchiveRecord::query()->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createArchive(User $actor, array $data): ArchiveRecord
    {
        return DB::transaction(function () use ($actor, $data): ArchiveRecord {
            $application = null;
            if (! empty($data['application_uuid'])) {
                $application = PermitApplication::query()->where('uuid', $data['application_uuid'])->first();
                if (! $application) {
                    throw ValidationException::withMessages([
                        'application_uuid' => ['Application not found.'],
                    ]);
                }
            }

            $payload = is_array($application?->payload) ? $application->payload : [];
            $checksum = hash('sha256', json_encode([
                'application' => $application?->uuid,
                'title' => $data['title'],
                'at' => now()->toIso8601String(),
                'payload_keys' => array_keys($payload),
            ], JSON_THROW_ON_ERROR));

            $record = ArchiveRecord::query()->create([
                'archive_no' => $this->nextNumber('archive_record', 'ARC-'.date('Y').'-'),
                'permit_application_id' => $application?->id,
                'title' => $data['title'],
                'storage_location' => $data['storage_location'] ?? 'CICTO digital vault (stub)',
                'media_type' => $data['media_type'] ?? 'digital',
                'checksum' => $checksum,
                'notes' => $data['notes'] ?? null,
                'meta' => $data['meta'] ?? ['cicto_backup' => 'stub'],
                'archived_by' => $actor->id,
                'archived_at' => now(),
            ]);

            $this->audit->log('archive_record.created', [
                'archive_id' => $record->uuid,
                'archive_no' => $record->archive_no,
                'application_id' => $application?->uuid,
            ]);

            return $record->load(['application', 'archivedByUser']);
        });
    }

    private function nextNumber(string $key, string $prefix): string
    {
        $series = NumberingSeries::query()->where('key', $key)->lockForUpdate()->first();
        if (! $series) {
            NumberingSeries::query()->create([
                'key' => $key,
                'prefix' => $prefix,
                'next_number' => 1,
                'pad_length' => 6,
            ]);
            $series = NumberingSeries::query()->where('key', $key)->lockForUpdate()->firstOrFail();
        }

        $number = $series->prefix.str_pad((string) $series->next_number, $series->pad_length, '0', STR_PAD_LEFT);
        $series->update(['next_number' => $series->next_number + 1]);

        return $number;
    }
}
