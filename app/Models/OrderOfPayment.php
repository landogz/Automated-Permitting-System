<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderOfPaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class OrderOfPayment extends Model
{
    use SoftDeletes;

    protected $table = 'orders_of_payment';

    protected $fillable = [
        'uuid',
        'permit_application_id',
        'oop_no',
        'status',
        'total_amount',
        'assessed_by',
        'issued_at',
        'paid_at',
        'payment_reference',
        'cto_stub_reference',
        'override_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderOfPaymentStatus::class,
            'total_amount' => 'decimal:2',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (OrderOfPayment $order): void {
            if (empty($order->uuid)) {
                $order->uuid = (string) Str::uuid();
            }
        });
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(PermitApplication::class, 'permit_application_id');
    }

    public function assessedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(OrderOfPaymentLine::class)->orderBy('line_order');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
