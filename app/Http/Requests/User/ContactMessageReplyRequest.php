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
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],
            'reply_to' => ['nullable', 'array'],
            'reply_to.sender' => ['nullable', 'string', 'max:100'],
            'reply_to.body' => ['nullable', 'string', 'max:500'],
        ];
    }
}