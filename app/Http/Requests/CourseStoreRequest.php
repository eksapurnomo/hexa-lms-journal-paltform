<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseStoreRequest extends FormRequest
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
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|min:5|max:500',
            'media' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'video' => 'file|mimes:mp4,mpeg|max:1048576',
            'description' => 'required|array|min:1',
            'description.*.heading' => 'required|string',
            'description.*.body' => 'required|string',
            'regular_price' => 'required|numeric|min:' . ((float) config('app.minimum_amount')),
            'price' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) {
                    if ($value > 0 && $value < (float) config('app.minimum_amount')) {
                        $fail('The price must be at least ' . config('app.minimum_amount') . ' if greater than 0.');
                    }
                },
            ],
            'instructor_id' => 'required|exists:instructors,id',
            'is_active' => '',
        ];
    }
    /**
     * Get custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.min' => 'The title must be at least 5 characters.',
            'title.max' => 'The title may not be greater than 500 characters.',
            'instructor_id.required' => 'The instructor field is required.',
            'instructor_id.exists' => 'The selected instructor is invalid.',
            'category_id.required' => 'The category field is required.',
            'category_id.exists' => 'The selected category is invalid.',
            'media.required' => 'The thumbnail field is required.',
            'media.image' => 'The thumbnail must be an image.',
            'media.mimes' => 'The thumbnail must be a file of type: jpeg, png, jpg',
            'media.max' => 'The thumbnail may not be greater than 2 MB.',
            'regular_price.min' => 'The regular price must be at least ' . config('app.minimum_amount'),
            'price.min' => 'The price must be at least ' . config('app.minimum_amount'),
        ];
    }
}
