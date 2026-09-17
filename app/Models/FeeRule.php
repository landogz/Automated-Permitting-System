<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FeeAgency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FeeRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'agency',
        'basis',
        'amount',
        'rate',
        'conditions',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'agency' => FeeAgency::class,
            'amount' => 'decimal:2',
            'rate' => 'decimal:4',
            'conditions' => 'array',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FeeRule $rule): void {
            if (empty($rule->uuid)) {
                $rule->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
