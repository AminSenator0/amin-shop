<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class HeroBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $banner = $this->route('hero_banner');

        return [
            'title'      => ['required', 'string', 'max:255'],
            'image'      => [$banner ? 'nullable' : 'required', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
            'link'       => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active'  => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان بنر الزامی است.',
            'image.required' => 'تصویر بنر الزامی است.',
            'image.image'    => 'فایل انتخابی باید تصویر باشد.',
            'image.mimes'    => 'فرمت مجاز: jpg، png یا webp.',
            'image.max'      => 'حداکثر حجم تصویر ۴ مگابایت است.',
        ];
    }
}