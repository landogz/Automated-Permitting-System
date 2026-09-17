<?php

declare(strict_types=1);

namespace App\Http\Requests\Records;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArchiveRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('records.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'application_uuid' => ['nullable', 'uuid', 'exists:permit_applications,uuid'],
            'title' => ['required', 'string', 'max:255'],
            'storage_location' => ['nullable', 'string', 'max:255'],
            'media_type' => ['nullable', 'string', Rule::in(['digital', 'physical', 'hybrid'])],
            'notes' => ['nullable', 'string', 'max:5000'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
