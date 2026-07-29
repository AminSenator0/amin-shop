<?php

namespace App\Http\Requests\Admin;

use App\Support\UploadRules;
use Illuminate\Foundation\Http\FormRequest;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'logo' => UploadRules::file('brand_logo'),
            'remove_logo' => ['sometimes', 'boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
