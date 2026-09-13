<?php

namespace App\Http\Requests;

use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class InstructorStoreRequest extends FormRequest
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
            'title' => 'required|string|max:60',
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => ['required', 'unique:users,phone',  new PhoneNumber()],
            'password' => 'required|min:8|confirmed',
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'is_featured' => ''
        ];
    }
}
