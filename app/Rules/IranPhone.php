<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IranPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || trim((string) $value) === '') {
            return;
        }

        $phone = normalize_phone(is_string($value) ? $value : (string) $value);

        if ($phone === null || ! preg_match('/^0\d{10,11}$/', $phone)) {
            $fail('شماره تلفن معتبر نیست.');
        }
    }
}
