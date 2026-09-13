<?php

namespace App\Http\Requests;

use App\Models\Journal;
use Illuminate\Foundation\Http\FormRequest;

class SubmissionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Actual authorization in controller/policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'journal_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $journal = Journal::find($value);
                    if (!$journal || $journal->status !== 'active') {
                        $fail('The selected journal is invalid or not active.');
                    }
                },
            ],
            'title' => 'required|string|max:255',
            'abstract' => 'nullable|string',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:50',
            'authors' => 'nullable|array',
            'authors.*.first_name' => 'required|string|max:100',
            'authors.*.last_name' => 'nullable|string|max:100',
            'authors.*.email' => 'nullable|email|max:255',
            'authors.*.affiliation' => 'nullable|string|max:255',
            'authors.*.is_corresponding' => 'boolean',
        ];
    }
}
