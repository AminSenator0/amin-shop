<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'], // ← جدید
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'موضوع پیام را وارد کنید.',
            'message.required' => 'متن پیام را وارد کنید.',
        ];
    }
}
