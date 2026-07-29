<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IranMobile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $mobile = normalize_mobile(is_string($value) ? $value : (string) $value);

        if ($mobile === null || ! preg_match('/^09[0-9]{9}$/', $mobile)) {
            $fail('شماره موبایل معتبر نیست. فرمت صحیح: ۰۹۱۲۱۲۳۴۵۶۷');
        }
    }
}
