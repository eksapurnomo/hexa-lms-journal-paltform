<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContentStoreRequest extends FormRequest
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
            'chapter_id' => 'required|exists:chapters,id',
            'media' => 'image|mimes:jpeg,png,jpg|max:2048',
            'title' => 'required|string|min:5|max:50',
            'type' => 'required|string|min:5|max:50',
            'serial_number' => 'required|integer',
        ];
    }
}
