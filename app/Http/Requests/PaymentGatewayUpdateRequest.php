<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentGatewayUpdateRequest extends FormRequest
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
            'mode' => 'string',
            'app_id' => 'string|min:3|max:1000',
            'client_id' => 'string|min:3|max:1000',
            'client_secret' => 'string|min:3|max:1000',
            'publishable_key' => 'string|min:3|max:1000',
            'secret_key' => 'string|min:3|max:1000',
            'merchant' => 'string|min:3|max:1000',
            'store_id' => 'string|min:3|max:1000',
            'signature_key' => 'string|min:3|max:1000',
        ];
    }
}
