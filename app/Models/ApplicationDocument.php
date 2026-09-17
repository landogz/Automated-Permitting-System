<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApplicationDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'permit_application_id',
        'label',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected static function booted(): void
    {
        static::creating(function (ApplicationDocument $document): void {
            if (empty($document->uuid)) {
                $document->uuid = (string) Str::uuid();
            }
        });
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(PermitApplication::class, 'permit_application_id');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
