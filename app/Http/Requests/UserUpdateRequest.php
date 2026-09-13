<?php

namespace App\Http\Requests;

use App\Rules\MailRules;
use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
        if ($this->routeIs('user.update')) {
            $userId = $this->user->id;
        } elseif ($this->routeIs('instructor.update')) {
            $userId = $this->instructor->user->id;
        } else {
            $userId = auth()->id();
        }

        return [
            'phone' => ['required', 'unique:users,phone,' . $userId,  new PhoneNumber()],
            'email' => ['required','email',new MailRules(),'unique:users,email,'. $userId],
            'password' => 'min:8|nullable|confirmed',
            'name' => 'required|string|max:255',
            'profile_picture' => 'image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
