<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PermitClassification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class RoutingTemplate extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'classification',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'classification' => PermitClassification::class,
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RoutingTemplate $template): void {
            if (empty($template->uuid)) {
                $template->uuid = (string) Str::uuid();
            }
        });
    }

    public function steps(): HasMany
    {
        return $this->hasMany(RoutingTemplateStep::class)->orderBy('step_order');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
