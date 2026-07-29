<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ContactMessageReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
        ];
    }
}
