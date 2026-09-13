<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChapterStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        if ($user->hasRole('admin') || $user->is_admin) return true;
        
        $courseId = $this->input('course_id');
        if ($courseId && $user->instructor) {
            $course = \App\Models\Course::find($courseId);
            if ($course) {
                return $user->instructor->id === $course->instructor_id;
            }
        }
        
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "chapter_id" => "",
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:500',
            'serial_number' => 'required|integer',
            'contents' => 'required|array|min:1',
            'contents.*.media' => 'file|mimes:jpeg,png,jpg,mp4,mpeg,mp3,wav,webm,ogg,raw,pdf|max:1048576|required_without:contents.*.link',
            'contents.*.link' => [
                'nullable',
                'regex:/^<iframe\s+.*?src=["\']https?:\/\/[^\s"\'<>]+["\'].*?><\/iframe>$/',
            ],
            'contents.*.title' => 'required|string|max:500',
            'contents.*.serial_number' => 'required|integer',
            'contents.*.is_forwardable' => '',
            'contents.*.is_free' => '',
            'contents.*.duration' => ''
        ];
    }
    /**
     * Get custom error messages for specific validation rules.
     */
    /**
     * Get custom error messages for specific validation rules.
     */
    public function messages(): array
    {
        return [
            'course_id.required' => 'Please select a course.',
            'course_id.exists' => 'The selected course does not exist.',
            'title.required' => 'Please provide a title for the chapter.',
            'title.max' => 'The title can have a maximum of 500 characters.',
            'serial_number.required' => 'Please provide a serial number.',
            'serial_number.integer' => 'The serial number must be a valid number.',
            'contents.required' => 'Please add at least one content item.',
            'contents.array' => 'The contents must be provided as a list.',
            'contents.min' => 'Please add at least one content item.',
            'contents.*.media.file' => 'Each media file must be a valid file.',
            'contents.*.media.mimes' => 'The media file must be one of the following types: jpeg, png, jpg, gif, svg, mp4, mpeg, mp3, wav, webm, ogg, raw or pdf.',
            'contents.*.media.max' => 'The media file size must not exceed 1 GB.',
            'contents.*.media.required_without' => 'Please provide either a media file or a valid link for each content item.',
            'contents.*.link.regex' => 'The link must be a valid iframe containing a video source',
            'contents.*.title.required' => 'Please provide a title for each content item.',
            'contents.*.title.max' => 'The content title can have a maximum of 500 characters.',
            'contents.*.serial_number.required' => 'Please provide a serial number for each content item.',
            'contents.*.serial_number.integer' => 'The serial number for each content item must be a valid number.',
        ];
    }
}
