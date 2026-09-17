<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RoutingTemplateStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'routing_template_id',
        'department_id',
        'step_order',
        'label',
        'sla_hours',
    ];

    protected function casts(): array
    {
        return [
            'step_order' => 'integer',
            'sla_hours' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RoutingTemplateStep $step): void {
            if (empty($step->uuid)) {
                $step->uuid = (string) Str::uuid();
            }
        });
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(RoutingTemplate::class, 'routing_template_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
