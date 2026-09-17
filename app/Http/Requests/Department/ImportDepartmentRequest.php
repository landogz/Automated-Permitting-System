<?php

declare(strict_types=1);

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

class ImportDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('departments.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.code' => ['required', 'string', 'max:50'],
            'rows.*.name' => ['required', 'string', 'max:255'],
            'rows.*.description' => ['nullable', 'string', 'max:2000'],
            'rows.*.is_active' => ['nullable'],
            'dry_run' => ['sometimes', 'boolean'],
        ];
    }
}
