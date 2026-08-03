<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Rules\IranMobile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', new IranMobile, 'unique:'.User::class.',phone'],
            // ✅ اضافه شد: regex انگلیسی-only
            'password' => ['required', 'confirmed', 'regex:/^[\x20-\x7E]+$/', Password::defaults()],
        ];
    }
    
    /**
     * پیام‌های خطای فارسی
     */
    public function messages(): array
    {
        return [
            'password.regex' => 'رمز عبور فقط باید شامل حروف انگلیسی، اعداد و نمادها باشد. استفاده از حروف فارسی مجاز نیست.',
        ];
    }
    
    public function attributes(): array
    {
        return [
            'name' => 'نام و نام خانوادگی',
            'email' => 'ایمیل',
            'phone' => 'شماره موبایل',
            'password' => 'رمز عبور',
            'password_confirmation' => 'تکرار رمز عبور',
        ];
    }
}
