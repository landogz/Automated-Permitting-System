<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ArchiveRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'archive_no',
        'permit_application_id',
        'title',
        'storage_location',
        'media_type',
        'checksum',
        'notes',
        'meta',
        'archived_by',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'archived_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ArchiveRecord $record): void {
            if (empty($record->uuid)) {
                $record->uuid = (string) Str::uuid();
            }
        });
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(PermitApplication::class, 'permit_application_id');
    }

    public function archivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
