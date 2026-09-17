<?php

declare(strict_types=1);

namespace App\Http\Requests\PermitApplication;

use Illuminate\Foundation\Http\FormRequest;

class UploadApplicationDocumentRequest extends FormRequest
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
        return [
            'label' => ['required', 'string', 'max:80', 'regex:/^[a-z][a-z0-9_]*$/'],
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimetypes:application/pdf,image/jpeg,image/png,image/webp',
                'extensions:pdf,jpg,jpeg,png,webp',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.mimetypes' => 'Only PDF, JPG, PNG, or WEBP files are allowed.',
            'file.max' => 'Each file must be 10 MB or smaller.',
            'label.regex' => 'Attachment key must be lowercase snake_case.',
        ];
    }
}
