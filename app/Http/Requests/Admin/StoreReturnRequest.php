<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class StoreReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Order $order */
        $order = $this->route('order');

        return [
            'reason' => ['required', 'string', 'max:500'],
            'refund_amount' => ['nullable', 'integer', 'min:0', 'max:'.$order->total],
        ];
    }
}
