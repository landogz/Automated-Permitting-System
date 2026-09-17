<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FeeAgency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OrderOfPaymentLine extends Model
{
    protected $fillable = [
        'uuid',
        'order_of_payment_id',
        'fee_rule_id',
        'agency',
        'description',
        'amount',
        'external_stub_reference',
        'line_order',
    ];

    protected function casts(): array
    {
        return [
            'agency' => FeeAgency::class,
            'amount' => 'decimal:2',
            'line_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (OrderOfPaymentLine $line): void {
            if (empty($line->uuid)) {
                $line->uuid = (string) Str::uuid();
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderOfPayment::class, 'order_of_payment_id');
    }

    public function feeRule(): BelongsTo
    {
        return $this->belongsTo(FeeRule::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
