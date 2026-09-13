<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouponUpdateRequest extends FormRequest
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
            'code' => 'string|max:50|unique:coupons,code,' . $this->coupon->id,
            'discount' => 'numeric',
            'applicable_from' => 'date',
            'valid_until' => 'date|after:applicable_from',
            'is_active' => '',
        ];
    }
}
