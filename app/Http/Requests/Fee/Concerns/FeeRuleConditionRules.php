<?php

declare(strict_types=1);

namespace App\Http\Requests\Fee\Concerns;

use Illuminate\Validation\Rule;

trait FeeRuleConditionRules
{
    /**
     * Shared validation for fee-rule match conditions.
     *
     * @return array<string, mixed>
     */
    protected function conditionRules(): array
    {
        return [
            'conditions' => ['nullable', 'array', 'max:20'],
            'conditions.*.field' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/'],
            'conditions.*.operator' => [
                'required',
                'string',
                Rule::in(['>=', '>', '<=', '<', '=', 'contains']),
            ],
            'conditions.*.value' => ['required'],
        ];
    }

    /**
     * Coerce numeric values for comparison operators; leave contains as string.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeConditionPayload(array $data, bool $requireKey = false): array
    {
        if (! array_key_exists('conditions', $data)) {
            if ($requireKey) {
                $data['conditions'] = [];
            }

            return $data;
        }

        if (! is_array($data['conditions'])) {
            $data['conditions'] = [];

            return $data;
        }

        $normalized = [];
        foreach ($data['conditions'] as $condition) {
            if (! is_array($condition)) {
                continue;
            }
            $operator = (string) ($condition['operator'] ?? '=');
            $value = $condition['value'] ?? null;
            if (is_string($value)) {
                $value = trim($value);
                if (mb_strlen($value) > 255) {
                    $value = mb_substr($value, 0, 255);
                }
            }
            if ($operator !== 'contains' && is_numeric($value)) {
                $value = str_contains((string) $value, '.') ? (float) $value : (int) $value;
            }
            $normalized[] = [
                'field' => (string) ($condition['field'] ?? ''),
                'operator' => $operator,
                'value' => $value,
            ];
        }
        $data['conditions'] = $normalized;

        return $data;
    }
}
