<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EvaluationResult;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Evaluation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'permit_application_id',
        'routing_slip_step_id',
        'evaluator_id',
        'status',
        'result',
        'findings',
        'remarks',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'result' => EvaluationResult::class,
            'findings' => 'array',
            'decided_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Evaluation $evaluation): void {
            if (empty($evaluation->uuid)) {
                $evaluation->uuid = (string) Str::uuid();
            }
        });
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(PermitApplication::class, 'permit_application_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(RoutingSlipStep::class, 'routing_slip_step_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(EvaluationTimeLog::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
