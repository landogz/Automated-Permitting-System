<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PermitClassification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class RoutingSlip extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'permit_application_id',
        'routing_template_id',
        'slip_no',
        'classification',
        'status',
        'generated_by',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'classification' => PermitClassification::class,
            'generated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RoutingSlip $slip): void {
            if (empty($slip->uuid)) {
                $slip->uuid = (string) Str::uuid();
            }
        });
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(PermitApplication::class, 'permit_application_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(RoutingTemplate::class, 'routing_template_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(RoutingSlipStep::class)->orderBy('step_order');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
