<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RoutingStepStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RoutingSlipStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'routing_slip_id',
        'department_id',
        'step_order',
        'label',
        'status',
        'assigned_user_id',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => RoutingStepStatus::class,
            'step_order' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RoutingSlipStep $step): void {
            if (empty($step->uuid)) {
                $step->uuid = (string) Str::uuid();
            }
        });
    }

    public function slip(): BelongsTo
    {
        return $this->belongsTo(RoutingSlip::class, 'routing_slip_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
