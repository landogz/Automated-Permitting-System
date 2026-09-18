<?php

declare(strict_types=1);

namespace App\Http\Requests\FormDefinition;

use App\Support\PermitApplication\FieldCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreFormDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('forms.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('form_definitions', 'code')],
            'title' => ['required', 'string', 'max:255'],
            'revision' => ['nullable', 'string', 'max:20'],
            'effective_date' => ['nullable', 'date'],
            'schema' => ['required', 'array'],
            'schema.sections' => ['required', 'array', 'min:1'],
            'schema.sections.*.title' => ['required', 'string', 'max:120'],
            'schema.sections.*.fields' => ['nullable', 'array'],
            'schema.sections.*.fields.*.name' => ['required', 'string', 'max:60', 'regex:/^[a-z][a-z0-9_]*$/'],
            'schema.sections.*.fields.*.label' => ['required', 'string', 'max:255'],
            'schema.sections.*.fields.*.type' => ['required', 'string', Rule::in(FieldCatalog::allowedFieldTypes())],
            'schema.sections.*.fields.*.required' => ['sometimes', 'boolean'],
            'schema.sections.*.fields.*.options' => ['nullable', 'array'],
            'schema.sections.*.fields.*.options.*' => ['string', 'max:120'],
            'schema.sections.*.fields.*.step' => ['nullable', 'string', 'max:20'],
            'required_attachments' => ['nullable', 'array'],
            'required_attachments.*' => ['string', 'max:80'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'schema.sections.*.fields.*.type.in' => 'Each field type must be one of: text, number, email, phone, date, long text, dropdown, or map location.',
            'schema.sections.*.fields.*.name.regex' => 'Field keys must be lowercase snake_case (e.g. lot_area).',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $sections = $this->input('schema.sections');
            if (! is_array($sections)) {
                return;
            }

            $fieldCount = 0;
            $names = [];
            foreach ($sections as $section) {
                if (! is_array($section) || ! isset($section['fields']) || ! is_array($section['fields'])) {
                    continue;
                }
                foreach ($section['fields'] as $field) {
                    if (! is_array($field)) {
                        continue;
                    }
                    $fieldCount++;
                    $name = (string) ($field['name'] ?? '');
                    if ($name !== '') {
                        $names[] = $name;
                    }
                    if (($field['type'] ?? '') === 'select') {
                        $options = $field['options'] ?? [];
                        if (! is_array($options) || count($options) === 0) {
                            $validator->errors()->add('schema', 'Dropdown fields require at least one option.');
                        }
                    }
                }
            }

            if ($fieldCount === 0) {
                $validator->errors()->add('schema', 'Add at least one field to the form.');
            }

            if (count($names) !== count(array_unique($names))) {
                $validator->errors()->add('schema', 'Field keys must be unique across the form.');
            }
        });
    }
}
