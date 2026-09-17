<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NumberingSeries extends Model
{
    protected $fillable = [
        'uuid',
        'key',
        'prefix',
        'next_number',
        'pad_length',
    ];

    protected static function booted(): void
    {
        static::creating(function (NumberingSeries $series): void {
            if (empty($series->uuid)) {
                $series->uuid = (string) Str::uuid();
            }
        });
    }
}
