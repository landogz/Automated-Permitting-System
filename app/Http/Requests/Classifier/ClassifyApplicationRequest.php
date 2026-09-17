<?php

declare(strict_types=1);

namespace App\Http\Requests\Classifier;

use App\Enums\PermitClassification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassifyApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('evaluations.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'classification' => ['nullable', Rule::enum(PermitClassification::class)],
            'auto' => ['nullable', 'boolean'],
        ];
    }
}
