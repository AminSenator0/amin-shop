<?php

namespace App\Rules;

use App\Exceptions\FileUploadException;
use App\Services\FileUploadService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ValidUploadedFile implements ValidationRule
{
    public function __construct(private readonly string $preset) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail('فایل آپلود شده معتبر نیست.');

            return;
        }

        if (! $value->isValid()) {
            $fail('خطا در آپلود فایل. لطفاً دوباره تلاش کنید.');

            return;
        }

        try {
            app(FileUploadService::class)->validate($value, $this->preset);
        } catch (FileUploadException $e) {
            $fail($e->getMessage());
        }
    }
}
