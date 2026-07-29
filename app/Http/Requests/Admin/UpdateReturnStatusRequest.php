<?php

namespace App\Http\Requests\Admin;

use App\Enums\ReturnStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReturnStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_column(ReturnStatus::cases(), 'value'))],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
