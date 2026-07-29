<?php

namespace App\Http\Requests\Admin;

use App\Support\UploadRules;
use Illuminate\Foundation\Http\FormRequest;

class SliderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'image' => UploadRules::file('slider'),
            'button_text' => ['nullable', 'string', 'max:100'],
            'button_url' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'sort_order' => (int) $this->input('sort_order', 0),
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
