<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidJalaliDate implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || trim((string) $value) === '') {
            return;
        }

        if (parse_jalali((string) $value) === null) {
            $fail('تاریخ وارد شده معتبر نیست.');
        }
    }
}
