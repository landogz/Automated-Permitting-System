<?php

declare(strict_types=1);

namespace App\Http\Requests\Records;

use App\Enums\LogbookBookType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLogbookEntryRequest extends FormRequest
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
            'book_type' => ['required', Rule::enum(LogbookBookType::class)],
            'application_uuid' => ['nullable', 'uuid', 'exists:permit_applications,uuid'],
            'subject' => ['required', 'string', 'max:255'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_contact' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
