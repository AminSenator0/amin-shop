<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ShippingMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'cost' => ['required', 'integer', 'min:0'],
            'free_above' => ['nullable', 'integer', 'min:1'],
            'estimated_days' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'نام روش',
            'description' => 'توضیحات',
            'cost' => 'هزینه پایه',
            'free_above' => 'آستانه ارسال رایگان',
            'estimated_days' => 'زمان تحویل',
        ];
    }

    protected function prepareForValidation(): void
    {
        $cost = $this->input('cost');
        $freeAbove = $this->input('free_above');
        $estimatedDays = $this->input('estimated_days');

        $this->merge([
            'name' => trim((string) $this->input('name', '')),
            'cost' => $cost !== null && trim((string) $cost) !== ''
                ? (int) normalize_numeric_string((string) $cost)
                : null,
            'free_above' => $freeAbove !== null && trim((string) $freeAbove) !== ''
                ? (int) normalize_numeric_string((string) $freeAbove)
                : null,
            'estimated_days' => $estimatedDays !== null && trim((string) $estimatedDays) !== ''
                ? (int) normalize_numeric_string((string) $estimatedDays)
                : null,
        ]);
    }
}
