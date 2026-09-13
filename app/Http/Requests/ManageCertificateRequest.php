<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManageCertificateRequest extends FormRequest
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
            'site_logo_id' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'subsite_logo_id' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'author_signature_id' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'certificate_title' => 'required',
            'certificate_short_text' => 'required',
            'certificate_text' => 'required',
            'author_name' => 'required',
            'author_designation' => 'nullable',
        ];
    }
}
