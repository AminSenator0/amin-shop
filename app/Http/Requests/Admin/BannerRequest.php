<?php

namespace App\Http\Requests\Admin;

use App\Services\BannerFormUploadCache;
use App\Support\UploadRules;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class BannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'image' => UploadRules::file('banner'),
            'link' => ['nullable', 'string', 'max:500'],
            'position' => ['required', 'in:home'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        app(BannerFormUploadCache::class)->storeFromRequest($this);

        throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'sort_order' => (int) $this->input('sort_order', 0),
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
