<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PermitClassification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ClassificationRule extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'classification',
        'priority',
        'conditions',
        'sla_hours',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'classification' => PermitClassification::class,
            'conditions' => 'array',
            'is_active' => 'boolean',
            'priority' => 'integer',
            'sla_hours' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ClassificationRule $rule): void {
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
