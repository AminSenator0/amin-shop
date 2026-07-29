<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderTrackingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tracking_code' => ['nullable', 'string', 'max:100'],
            'send_sms' => ['sometimes', 'boolean'],
        ];
    }
}
