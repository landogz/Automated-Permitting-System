<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RegistrationApprovalStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'approval_status',
        'approval_notes',
        'registered_at',
        'approved_at',
        'declined_at',
        'reviewed_by',
        'department_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'approval_status' => RegistrationApprovalStatus::class,
            'registered_at' => 'datetime',
            'approved_at' => 'datetime',
            'declined_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function permitApplications(): HasMany
    {
        return $this->hasMany(PermitApplication::class);
    }

    public function isRegistrationApproved(): bool
    {
        $status = $this->approval_status instanceof RegistrationApprovalStatus
            ? $this->approval_status
            : RegistrationApprovalStatus::tryFrom((string) $this->approval_status);

        return ($status?->canLogin() ?? false) && $this->is_active;
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
