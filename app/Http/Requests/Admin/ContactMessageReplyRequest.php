<?php

namespace App\Http\Requests\Admin;

use App\Enums\ReplyChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'channel' => ['required', Rule::enum(ReplyChannel::class)],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'], // ← جدید
        ];
    }

    public function messages(): array
    {
        return [
            'channel.required' => 'روش ارسال پاسخ را انتخاب کنید.',
        ];
    }
}
