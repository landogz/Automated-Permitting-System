<?php

declare(strict_types=1);

namespace App\Http\Requests\FormDefinition;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateFormDefinitionRequest extends FormRequest
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
        $form = $this->route('form_definition');

        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('form_definitions', 'code')->ignore($form?->id),
            ],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'revision' => ['nullable', 'string', 'max:20'],
            'effective_date' => ['nullable', 'date'],
            'schema' => ['sometimes', 'required', 'array'],
            'schema.sections' => ['required_with:schema', 'array', 'min:1'],
            'schema.sections.*.title' => ['required_with:schema', 'string', 'max:120'],
            'schema.sections.*.fields' => ['nullable', 'array'],
            'schema.sections.*.fields.*.name' => ['required_with:schema.sections.*.fields', 'string', 'max:60', 'regex:/^[a-z][a-z0-9_]*$/'],
            'schema.sections.*.fields.*.label' => ['required_with:schema.sections.*.fields', 'string', 'max:255'],
            'schema.sections.*.fields.*.type' => ['required_with:schema.sections.*.fields', 'string', Rule::in([
                'text', 'number', 'email', 'tel', 'date', 'textarea', 'select',
            ])],
            'schema.sections.*.fields.*.required' => ['sometimes', 'boolean'],
            'schema.sections.*.fields.*.options' => ['nullable', 'array'],
            'schema.sections.*.fields.*.options.*' => ['string', 'max:120'],
            'schema.sections.*.fields.*.step' => ['nullable', 'string', 'max:20'],
            'required_attachments' => ['nullable', 'array'],
            'required_attachments.*' => ['string', 'max:80'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->has('schema')) {
                return;
            }

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
