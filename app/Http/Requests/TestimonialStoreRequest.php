<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestimonialStoreRequest extends FormRequest
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
            "name" => "required|string",
            "designation" => "required|string",
            "description" => "required|string|min:20",
            "picture" => "required|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
            'rating' => 'required|numeric|min:1|max:5',
        ];
    }
}
