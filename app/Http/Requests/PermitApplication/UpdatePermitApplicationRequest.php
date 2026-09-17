<?php

declare(strict_types=1);

namespace App\Http\Requests\PermitApplication;

use App\Support\PermitApplication\FieldCatalog;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePermitApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge([
            'form_definition_uuid' => ['nullable', 'uuid'],
            'project_title' => ['sometimes', 'required', 'string', 'max:255'],
            'project_location' => ['sometimes', 'required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ], FieldCatalog::payloadValidationRules());
    }
}
