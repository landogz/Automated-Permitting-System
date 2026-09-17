<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AppealStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ComplianceAppeal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'compliance_notice_id',
        'filed_by',
        'status',
        'grounds',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AppealStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ComplianceAppeal $appeal): void {
            if (empty($appeal->uuid)) {
                $appeal->uuid = (string) Str::uuid();
            }
        });
    }

    public function notice(): BelongsTo
    {
        return $this->belongsTo(ComplianceNotice::class, 'compliance_notice_id');
    }

    public function filedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filed_by');
    }

    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
