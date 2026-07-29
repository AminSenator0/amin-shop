<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizePersianInput
{
    /** @var list<string> */
    private array $phoneFields = ['phone', 'mobile', 'contact_phone', 'social_whatsapp'];

    /** @var list<string> */
    private array $numericFields = [
        'price', 'compare_price', 'value', 'min_order', 'max_uses',
        'cost', 'free_above', 'quantity', 'stock', 'weight',
        'postal_code', 'refund_amount', 'estimated_days', 'sort_order',
        'min_order_amount', 'free_shipping_threshold', 'return_days',
    ];

    /** @var list<string> */
    private array $jalaliDateFields = ['expires_at', 'published_at', 'date_from', 'date_to'];

    public function handle(Request $request, Closure $next): Response
    {
        $request->merge($this->normalizeInput($request->all()));

        return $next($request);
    }

    private function normalizeInput(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->normalizeInput($value);

                continue;
            }

            if (! is_string($value)) {
                continue;
            }

            if (in_array($key, $this->jalaliDateFields, true) && trim($value) !== '') {
                $parsed = parse_jalali($value);
                $data[$key] = $parsed?->format('Y-m-d');

                continue;
            }

            if (in_array($key, $this->phoneFields, true)) {
                $data[$key] = $key === 'social_whatsapp'
                    ? normalize_mobile($value)
                    : normalize_phone($value);

                continue;
            }

            if (in_array($key, $this->numericFields, true)) {
                $data[$key] = normalize_numeric_string($value);

                continue;
            }

            $data[$key] = to_english_digits($value);
        }

        return $data;
    }
}
