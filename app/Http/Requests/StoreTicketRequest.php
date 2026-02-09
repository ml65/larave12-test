<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Публичный endpoint для виджета
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\+[1-9]\d{1,14}$/'], // E.164 формат
            'email' => ['nullable', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'text' => ['required', 'string'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'max:10240'], // 10MB максимум на файл
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Phone number must be in E.164 format (e.g., +1234567890)',
            'files.*.max' => 'Each file must not exceed 10MB',
        ];
    }
}
