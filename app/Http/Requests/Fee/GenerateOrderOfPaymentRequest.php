<?php

declare(strict_types=1);

namespace App\Http\Requests\Fee;

use Illuminate\Foundation\Http\FormRequest;

class GenerateOrderOfPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('fees.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'override_total' => ['nullable', 'numeric', 'min:0'],
            'override_reason' => ['nullable', 'string', 'min:5', 'max:1000'],
        ];
    }
}
