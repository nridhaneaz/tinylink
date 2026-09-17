<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUrlRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'url'         => ['required', 'url', 'max:2048'],
            'custom_code' => [
                'nullable',
                'string',
                'alpha_dash',
                'min:3',
                'max:20',
                'unique:urls,short_code',
            ],
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'url.required' => 'A URL is required.',
            'url.url'      => 'The provided value must be a valid URL.',
            'custom_code.alpha_dash' => 'The custom code may only contain letters, numbers, dashes, and underscores.',
            'custom_code.unique'     => 'This custom short code is already taken. Please choose another.',
        ];
    }
}
