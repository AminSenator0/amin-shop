<?php

use App\Support\StoreSettings;
use App\Services\FileUploadService;
use Hekmatinasser\Verta\Verta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

if (! function_exists('to_english_digits')) {
    function to_english_digits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return strtr($value, [
            "\u{06F0}" => '0', "\u{06F1}" => '1', "\u{06F2}" => '2', "\u{06F3}" => '3', "\u{06F4}" => '4',
            "\u{06F5}" => '5', "\u{06F6}" => '6', "\u{06F7}" => '7', "\u{06F8}" => '8', "\u{06F9}" => '9',
            "\u{0660}" => '0', "\u{0661}" => '1', "\u{0662}" => '2', "\u{0663}" => '3', "\u{0664}" => '4',
            "\u{0665}" => '5', "\u{0666}" => '6', "\u{0667}" => '7', "\u{0668}" => '8', "\u{0669}" => '9',
        ]);
    }
}

if (! function_exists('to_persian_digits')) {
    function to_persian_digits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return strtr($value, [
            '0' => "\u{06F0}", '1' => "\u{06F1}", '2' => "\u{06F2}", '3' => "\u{06F3}", '4' => "\u{06F4}",
            '5' => "\u{06F5}", '6' => "\u{06F6}", '7' => "\u{06F7}", '8' => "\u{06F8}", '9' => "\u{06F9}",
        ]);
    }
}

if (!function_exists('upload_message_attachment')) {
    function upload_message_attachment($file): string
    {
        $filename = uniqid('msg_') . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('message-attachments', $filename, 'public');
        return 'message-attachments/' . $filename;
    }
}

if (! function_exists('normalize_mobile')) {
    function normalize_mobile(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $phone = to_english_digits($phone);
        $phone = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($phone, '0098')) {
            $phone = substr($phone, 4);
        } elseif (str_starts_with($phone, '98') && strlen($phone) >= 12) {
            $phone = substr($phone, 2);
        } elseif (str_starts_with($phone, '+98')) {
            $phone = substr($phone, 3);
        }

        if (str_starts_with($phone, '9') && strlen($phone) === 10) {
            $phone = '0'.$phone;
        }

        return $phone;
    }
}

if (! function_exists('normalize_phone')) {
    function normalize_phone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', to_english_digits($phone)) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '98') && strlen($digits) >= 12) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '9') && strlen($digits) === 10) {
            return normalize_mobile($digits);
        }

        if (str_starts_with($digits, '09')) {
            return normalize_mobile($digits);
        }

        return $digits;
    }
}

if (! function_exists('normalize_numeric_string')) {
    function normalize_numeric_string(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return $value;
        }

        $value = to_english_digits($value);

        return str_replace([',', "\u{060C}", "\u{066C}", ' '], '', $value);
    }
}

if (! function_exists('format_jalali')) {
    function format_jalali(mixed $date, string $format = 'Y/m/d', bool $persianDigits = true): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        $formatted = Verta::instance($date)->format($format);

        return $persianDigits ? to_persian_digits($formatted) : $formatted;
    }
}

function parse_jalali(?string $input): ?Carbon
{
    if ($input === null || trim($input) === '') {
        return null;
    }

    $input = to_english_digits(trim($input));

    // اگه فرمت YYYY-MM-DD باشه و سال > 2000، میلادیه
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $input)) {
        $year = (int) substr($input, 0, 4);
        if ($year > 2000) {
            return Carbon::parse($input);
        }
    }

    try {
        return Carbon::instance(Verta::parse($input)->datetime());
    } catch (\Throwable) {
        return null;
    }
}

if (! function_exists('format_price')) {
    function format_price(int $amount, bool $persianDigits = true): string
    {
        $formatted = number_format($amount);

        if ($persianDigits) {
            $formatted = to_persian_digits(str_replace(',', "\u{066C}", $formatted));
        }

        $currency = StoreSettings::get('currency');

        return "{$formatted} {$currency}";
    }
}

if (! function_exists('format_number')) {
    function format_number(int|float $number, bool $persianDigits = true): string
    {
        $formatted = number_format($number);

        if ($persianDigits) {
            $formatted = to_persian_digits(str_replace(',', "\u{066C}", $formatted));
        }

        return $formatted;
    }
}

if (! function_exists('rtl_bidi_tokens')) {
    /**
     * Escape mixed Persian/LTR SMS samples and isolate placeholders for correct RTL display.
     */
    function rtl_bidi_tokens(string $text): string
    {
        $escaped = e($text);

        return (string) preg_replace_callback(
            '/%token\d*|\\{\\d+\\}|\\btoken\\d*\\b/',
            static fn (array $matches): string => '<bdi dir="ltr">'.$matches[0].'</bdi>',
            $escaped
        );
    }
}

if (! function_exists('upload_url')) {
    function upload_url(?string $path): ?string
    {
        return app(FileUploadService::class)->url($path);
    }
}

if (! function_exists('men_perfume_image_url')) {
    function men_perfume_image_url(?string $categorySlug = null): string
    {
        return category_image_url(null, 1, 'عطر و ادکلن', $categorySlug);
    }
}

if (! function_exists('product_image_url')) {
    function product_image_url(?string $path, int $fallbackId = 1): ?string
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }

        return null;
    }
}

if (! function_exists('prepare_pdf_rtl_html')) {
    function prepare_pdf_rtl_html(string $html): string
    {
        $arabic = new \ArPHP\I18N\Arabic();
        $positions = $arabic->arIdentify($html);

        for ($i = count($positions) - 1; $i >= 0; $i -= 2) {
            $start = $positions[$i - 1];
            $length = $positions[$i] - $start;
            $segment = substr($html, $start, $length);
            $shaped = $arabic->utf8Glyphs($segment, 500);
            $html = substr_replace($html, $shaped, $start, $length);
        }

        return $html;
    }
}

if (! function_exists('category_image_url')) {
    function category_image_url(?string $path, int $fallbackIndex = 1, ?string $categoryName = null, ?string $categorySlug = null): string
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }

        $basename = \App\Support\CategoryImages::storageBasename($categoryName, $categorySlug);
        $storedFallback = 'categories/'.$basename.'.webp';

        if (Storage::disk('public')->exists($storedFallback)) {
            return asset('storage/'.$storedFallback);
        }

        return asset('images/placeholder.svg');
    }
}

if (! function_exists('slider_image_url')) {
    function slider_image_url(?string $path, int $fallbackIndex = 1, ?string $sliderTitle = null): string
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }

        $fallback = 'sliders/'.\App\Support\SliderImages::storageBasename($sliderTitle).'.webp';

        if (Storage::disk('public')->exists($fallback)) {
            return asset('storage/'.$fallback);
        }

        return asset('images/placeholder.svg');
    }
}

if (! function_exists('banner_image_url')) {
    function banner_image_url(?string $path, int $fallbackIndex = 1): string
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }

        return asset('images/placeholder.svg');
    }
}

if (! function_exists('hero_showcase_image_url')) {
    function hero_showcase_image_url(): string
    {
        if (file_exists(public_path('images/hero-showcase.webp'))) {
            return asset('images/hero-showcase.webp');
        }

        if (file_exists(public_path('images/hero-showcase.jpg'))) {
            return asset('images/hero-showcase.jpg');
        }

        return asset('images/placeholder.svg');
    }
}

if (! function_exists('hero_showcase_image_sources')) {
    /** @return array{webp: ?string, jpg: ?string} */
    function hero_showcase_image_sources(): array
    {
        $webp = file_exists(public_path('images/hero-showcase.webp'))
            ? asset('images/hero-showcase.webp')
            : null;

        $jpg = file_exists(public_path('images/hero-showcase.jpg'))
            ? asset('images/hero-showcase.jpg')
            : null;

        return compact('webp', 'jpg');
    }
}

if (! function_exists('brand_logo_url')) {
    function brand_logo_url(?string $path, ?string $slug = null, int $fallbackIndex = 1): string
    {
        if ($path && preg_match('/brands\/[a-f0-9-]{36}\./i', $path) === 1 && Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }

        if ($slug) {
            foreach (['svg', 'png', 'webp'] as $extension) {
                $staticPath = public_path('images/brands/'.$slug.'.'.$extension);

                if (is_file($staticPath)) {
                    return asset('images/brands/'.$slug.'.'.$extension);
                }
            }
        }

        if ($path && Storage::disk('public')->exists($path)) {
            $basename = strtolower(basename($path));

            if (! str_contains($basename, 'test') && ! str_contains($basename, '+تست+')) {
                return asset('storage/'.$path);
            }
        }

        return asset('images/placeholder.svg');
    }
}
