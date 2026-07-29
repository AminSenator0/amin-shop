<?php

namespace App\Http\Requests\Shop;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $product = Product::find($this->input('product_id'));

            if (! $product) {
                return;
            }

            if ($product->hasSizes() && blank($this->input('size'))) {
                $validator->errors()->add('size', 'لطفاً سایز را انتخاب کنید.');
            }

            if ($product->hasColors() && blank($this->input('color'))) {
                $validator->errors()->add('color', 'لطفاً رنگ را انتخاب کنید.');
            }

            if ($this->filled('size') && ! $product->isValidSize($this->input('size'))) {
                $validator->errors()->add('size', 'سایز انتخاب‌شده معتبر نیست.');
            }

            if ($this->filled('color') && ! $product->isValidColor($this->input('color'))) {
                $validator->errors()->add('color', 'رنگ انتخاب‌شده معتبر نیست.');
            }
        });
    }
}
