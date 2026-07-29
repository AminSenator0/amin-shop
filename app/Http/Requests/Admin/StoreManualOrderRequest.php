<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreManualOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'shipping_method_id' => ['required', 'exists:shipping_methods,id'],
            'full_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'province' => ['required', 'string', 'max:50'],
            'city' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:500'],
            'postal_code' => ['required', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:500'],
            'mark_as_paid' => ['sometimes', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.size' => ['nullable', 'string', 'max:50'],
            'items.*.color' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'حداقل یک محصول انتخاب کنید.',
            'items.min' => 'حداقل یک محصول انتخاب کنید.',
        ];
    }
}
