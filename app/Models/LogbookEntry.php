<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LogbookBookType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LogbookEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'entry_no',
        'book_type',
        'permit_application_id',
        'subject',
        'recipient_name',
        'recipient_contact',
        'notes',
        'meta',
        'recorded_by',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'book_type' => LogbookBookType::class,
            'meta' => 'array',
            'recorded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (LogbookEntry $entry): void {
            if (empty($entry->uuid)) {
                $entry->uuid = (string) Str::uuid();
            }
        });
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(PermitApplication::class, 'permit_application_id');
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
