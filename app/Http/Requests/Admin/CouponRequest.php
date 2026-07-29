<?php

namespace App\Http\Requests\Admin;

use App\Models\Coupon;
use App\Rules\ValidJalaliDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Coupon|null $coupon */
        $coupon = $this->route('coupon');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('coupons', 'code')->ignore($coupon),
            ],
            'type' => ['required', 'in:percent,fixed'],
            'value' => [
                'required',
                'integer',
                'min:1',
                Rule::when($this->input('type') === 'percent', ['max:100']),
            ],
            'min_order' => ['nullable', 'integer', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date', new ValidJalaliDate],
        ];
    }
}
