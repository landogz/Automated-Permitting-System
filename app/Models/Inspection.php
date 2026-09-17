<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InspectionResult;
use App\Enums\InspectionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Inspection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'permit_application_id',
        'inspection_no',
        'type',
        'status',
        'result',
        'scheduled_at',
        'completed_at',
        'inspector_id',
        'scheduled_by',
        'location',
        'latitude',
        'longitude',
        'notes',
        'compliance_sheet',
        'electrical_form',
    ];

    protected function casts(): array
    {
        return [
            'status' => InspectionStatus::class,
            'result' => InspectionResult::class,
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'compliance_sheet' => 'array',
            'electrical_form' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Inspection $inspection): void {
            if (empty($inspection->uuid)) {
                $inspection->uuid = (string) Str::uuid();
            }
        });
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(PermitApplication::class, 'permit_application_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function scheduledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
