<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ComplianceNoticeStatus;
use App\Enums\ComplianceNoticeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ComplianceNotice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'permit_application_id',
        'inspection_id',
        'notice_no',
        'type',
        'status',
        'title',
        'body',
        'issued_by',
        'issued_at',
        'due_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ComplianceNoticeType::class,
            'status' => ComplianceNoticeStatus::class,
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ComplianceNotice $notice): void {
            if (empty($notice->uuid)) {
                $notice->uuid = (string) Str::uuid();
            }
        });
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(PermitApplication::class, 'permit_application_id');
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function issuedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function appeals(): HasMany
    {
        return $this->hasMany(ComplianceAppeal::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
