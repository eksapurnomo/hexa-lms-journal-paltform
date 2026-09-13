<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuizSubmitRequest extends FormRequest
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
            'answer' => 'required|array',
            'answer.question_id' => 'required|integer',
            'answer.choice' => 'required_without_all:answer.choices,answer.skip|nullable|string|max:255',
            'answer.choices' => 'required_without_all:answer.choice,answer.skip|array',
            'answer.choices.*' => 'nullable|string|max:255',
            'answer.skip' => 'required|boolean'
        ];
    }
}
