<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExamStoreRequest extends FormRequest
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
            'title' => 'required|string|max:500',
            'duration' => 'required|integer',
            'mark_per_question' => 'required|integer',
            'pass_marks' => 'required|integer',
            'course_id' => 'required|exists:courses,id',

            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string|max:500',
            'questions.*.question_type' => 'required|string',
            'questions.*.correct_option' => 'string|in:option_1,option_2,option_3,option_4,yes,no',

            'questions.*.option_1' => 'required_without:questions.*.correct_option|array|min:1|max:2',
            'questions.*.option_1.text' => 'required_without:questions.*.correct_option|string|max:500',
            'questions.*.option_1.is_correct' => '',

            'questions.*.option_2' => 'required_without:questions.*.correct_option|array|min:1|max:2',
            'questions.*.option_2.text' => 'required_without:questions.*.correct_option|string|max:500',
            'questions.*.option_2.is_correct' => '',

            'questions.*.option_3' => 'required_without:questions.*.correct_option|array|min:1|max:2',
            'questions.*.option_3.text' => 'required_without:questions.*.correct_option|string|max:500',
            'questions.*.option_3.is_correct' => '',

            'questions.*.option_4' => 'required_without:questions.*.correct_option|array|min:1|max:2',
            'questions.*.option_4.text' => 'required_without:questions.*.correct_option|string|max:500',
            'questions.*.option_4.is_correct' => '',
        ];
    }

    public function messages(): array
    {
        return [
            'questions.*.correct_option.required_without_all' => 'Please select at least one option.',
            'questions.*.option_1.is_correct.required_without_all' => 'Please select at least one option.',
            'questions.*.option_2.is_correct.required_without_all' => 'Please select at least one option.',
            'questions.*.option_3.is_correct.required_without_all' => 'Please select at least one option.',
            'questions.*.option_4.is_correct.required_without_all' => 'Please select at least one option.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->sometimes('questions.*.option_1.is_correct', 'required_without_all:questions.*.option_2.is_correct,questions.*.option_3.is_correct,questions.*.option_4.is_correct,questions.*.correct_option', function ($input) {
            return true;
        });

        $validator->sometimes('questions.*.option_2.is_correct', 'required_without_all:questions.*.option_1.is_correct,questions.*.option_3.is_correct,questions.*.option_4.is_correct,questions.*.correct_option', function ($input) {
            return true;
        });

        $validator->sometimes('questions.*.option_3.is_correct', 'required_without_all:questions.*.option_1.is_correct,questions.*.option_2.is_correct,questions.*.option_4.is_correct,questions.*.correct_option', function ($input) {
            return true;
        });

        $validator->sometimes('questions.*.option_4.is_correct', 'required_without_all:questions.*.option_1.is_correct,questions.*.option_2.is_correct,questions.*.option_3.is_correct,questions.*.correct_option', function ($input) {
            return true;
        });
    }
}
