<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IranPostalCode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $code = normalize_numeric_string(is_string($value) ? $value : (string) $value);

        if ($code === null || ! preg_match('/^[0-9]{10}$/', $code)) {
            $fail('کد پستی باید ۱۰ رقم باشد.');
        }
    }
}
