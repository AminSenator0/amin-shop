<?php

namespace App\Http\Requests\User;

use App\Rules\IranMobile;
use App\Rules\IranPostalCode;
use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:50'],
            'full_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', new IranMobile],
            'province' => ['required', 'string', 'max:50'],
            'city' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:500'],
            'postal_code' => ['required', new IranPostalCode],
            'is_default' => ['boolean'],
        ];
    }
}
