<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoIp
{
    /**
     * دریافت کشور و شهر IP عمومی
     * نتیجه موفق به مدت ۳۰ روز کش می‌شود.
     */
    public static function lookup(string $ip): array
    {
        $empty = [
            'country' => null,
            'city' => null,
        ];

        if (! filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        )) {
            return $empty;
        }

        return Cache::remember(
            "geoip:{$ip}",
            now()->addDays(30),
            function () use ($ip, $empty) {
                $providers = [
                    'fromIpWhoIs',
                    'fromFreeIpApi',
                    'fromReallyFreeGeoIp',
                    'fromDbIp',
                    'fromIpApi',
                ];

                foreach ($providers as $provider) {
                    try {
                        $result = self::$provider($ip);

                        if (
                            $result !== null &&
                            (
                                ! empty($result['country']) ||
                                ! empty($result['city'])
                            )
                        ) {
                            return $result;
                        }
                    } catch (\Throwable $e) {
                        // خطای یک سرویس نباید کل فرایند را متوقف کند.
                        report($e);
                    }
                }

                // توجه: Cache::remember نتیجه خالی را نیز ذخیره می‌کند.
                // برای جلوگیری از این موضوع، بهتر است پایین‌تر
                // نسخه بدون کش نتیجه ناموفق را جایگزین کنیم.
                return $empty;
            }
        );
    }

    /**
     * ipwho.is
     */
    private static function fromIpWhoIs(string $ip): ?array
    {
        $response = Http::timeout(3)
            ->connectTimeout(2)
            ->acceptJson()
            ->get("https://ipwho.is/{$ip}");

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        if (! is_array($data) || ($data['success'] ?? false) !== true) {
            return null;
        }

        return [
            'country' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
        ];
    }

    /**
     * FreeIPAPI
     * Endpoint نسخه جدید API
     */
    private static function fromFreeIpApi(string $ip): ?array
    {
        $response = Http::timeout(3)
            ->connectTimeout(2)
            ->acceptJson()
            ->get("https://free.freeipapi.com/api/v1/json/{$ip}");

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        if (! is_array($data)) {
            return null;
        }

        return [
            'country' => $data['countryName'] ?? null,
            'city' => $data['cityName'] ?? null,
        ];
    }

    /**
     * ReallyFreeGeoIP
     */
    private static function fromReallyFreeGeoIp(string $ip): ?array
    {
        $response = Http::timeout(3)
            ->connectTimeout(2)
            ->acceptJson()
            ->get("https://reallyfreegeoip.org/json/{$ip}");

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        if (! is_array($data)) {
            return null;
        }

        return [
            'country' => $data['country_name'] ?? null,
            'city' => $data['city'] ?? null,
        ];
    }

    /**
     * DB-IP Free API
     */
    private static function fromDbIp(string $ip): ?array
    {
        $response = Http::timeout(3)
            ->connectTimeout(2)
            ->acceptJson()
            ->get("https://api.db-ip.com/v2/free/{$ip}");

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        if (! is_array($data)) {
            return null;
        }

        return [
            'country' => $data['countryName'] ?? null,
            'city' => $data['city'] ?? null,
        ];
    }

    /**
     * IP-API
     * سرویس HTTP است؛ در صورت محدودیت شبکه ممکن است پاسخ ندهد.
     */
    private static function fromIpApi(string $ip): ?array
    {
        $response = Http::timeout(3)
            ->connectTimeout(2)
            ->acceptJson()
            ->get(
                "http://ip-api.com/json/{$ip}",
                [
                    'fields' => 'status,country,city',
                ]
            );

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        if (
            ! is_array($data) ||
            ($data['status'] ?? null) !== 'success'
        ) {
            return null;
        }

        return [
            'country' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
        ];
    }
}