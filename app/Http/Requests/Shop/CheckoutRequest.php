<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'address_id' => [
                'required',
                Rule::exists('addresses', 'id')->where('user_id', $this->user()->id),
            ],
            'shipping_method_id' => [
                'required',
                Rule::exists('shipping_methods', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'notes' => ['nullable', 'string', 'max:500'],
            // مبلغ‌ها فقط سمت سرور محاسبه می‌شوند — هر فیلد جعلی رد می‌شود
            'total' => ['prohibited'],
            'payment_method' => ['required', 'in:online,c2c'],
            'subtotal' => ['prohibited'],
            'shipping_cost' => ['prohibited'],
            'discount' => ['prohibited'],
            'discount_amount' => ['prohibited'],
            'price' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'total.prohibited' => 'دستکاری مبلغ سفارش مجاز نیست.',
            'subtotal.prohibited' => 'دستکاری مبلغ سفارش مجاز نیست.',
            'shipping_cost.prohibited' => 'دستکاری هزینه ارسال مجاز نیست.',
            'discount.prohibited' => 'دستکاری تخفیف مجاز نیست.',
            'discount_amount.prohibited' => 'دستکاری تخفیف مجاز نیست.',
            'price.prohibited' => 'دستکاری قیمت مجاز نیست.',
        ];
    }
}
