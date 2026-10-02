<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FooterLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $license = $this->route('footer_license');

        return [
            'title'      => ['nullable', 'string', 'max:100'],
            'image'      => [$license ? 'nullable' : 'required', 'image', 'mimes:jpeg,jpg,png,webp,gif,svg', 'max:2048'],
            'link'       => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active'  => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'تصویر مجوز الزامی است.',
            'image.image'    => 'فایل انتخابی باید تصویر باشد.',
            'image.mimes'    => 'فرمت مجاز: jpg، png، webp، gif یا svg.',
            'image.max'      => 'حداکثر حجم ۲ مگابایت است.',
        ];
    }
}