<?php

namespace App\Http\Requests\User;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class StoreReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Order $order */
        $order = $this->route('order');

        return $order->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        /** @var Order $order */
        $order = $this->route('order');
        $order->loadMissing('items');
        $maxQuantities = $order->items->pluck('quantity', 'id')->map(fn ($q) => (int) $q)->all();

        $rules = [
            'reason' => ['required', 'string', 'max:500'],
            'items' => ['sometimes', 'array'],
        ];

        foreach ($maxQuantities as $itemId => $maxQty) {
            $rules["items.{$itemId}"] = ['nullable', 'integer', 'min:0', 'max:'.$maxQty];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'دلیل مرجوعی را وارد کنید.',
            'reason.max' => 'دلیل مرجوعی نباید بیش از ۵۰۰ کاراکتر باشد.',
        ];
    }
}
