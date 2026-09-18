<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EvaluationTimeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'permit_application_id',
        'evaluation_id',
        'user_id',
        'department_id',
        'routing_slip_step_id',
        'started_at',
        'ended_at',
        'duration_seconds',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'duration_seconds' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (EvaluationTimeLog $log): void {
            if (empty($log->uuid)) {
                $log->uuid = (string) Str::uuid();
            }
        });
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(PermitApplication::class, 'permit_application_id');
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function routingSlipStep(): BelongsTo
    {
        return $this->belongsTo(RoutingSlipStep::class, 'routing_slip_step_id');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
