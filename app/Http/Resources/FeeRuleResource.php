<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\FeeRule */
class FeeRuleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'agency' => $this->agency?->value ?? $this->agency,
            'basis' => $this->basis,
            'amount' => $this->amount,
            'rate' => $this->rate,
            'conditions' => $this->conditions,
            'condition_count' => is_array($this->conditions) ? count($this->conditions) : 0,
            'priority' => $this->priority,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
