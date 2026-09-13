<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LanguageRequest extends FormRequest
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
            'title' => 'required|string|max:50',
            'name' => 'nullable|string|max:6|alpha',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('The title field is required'),
            'name.required' => __('The name field is required'),
            'name.max' => __('The name field should not be longer than 6 characters'),
            'title.max' => __('The title field should not be longer than 50 characters'),
            'name.alpha' => __('The name field should only contain alphabets'),
        ];
    }
}
