<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PermitApplication extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'application_no',
        'user_id',
        'form_definition_id',
        'status',
        'classification',
        'classified_by_rule',
        'classified_by',
        'classified_at',
        'project_title',
        'project_location',
        'latitude',
        'longitude',
        'payload',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'submitted_at' => 'datetime',
            'classified_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PermitApplication $application): void {
            if (empty($application->uuid)) {
                $application->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formDefinition(): BelongsTo
    {
        return $this->belongsTo(FormDefinition::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function classifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'classified_by');
    }

    public function routingSlips(): HasMany
    {
        return $this->hasMany(RoutingSlip::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(EvaluationTimeLog::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    public function ordersOfPayment(): HasMany
    {
        return $this->hasMany(OrderOfPayment::class);
    }

    public function complianceNotices(): HasMany
    {
        return $this->hasMany(ComplianceNotice::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
