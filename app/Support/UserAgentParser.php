<?php

namespace App\Support;

class UserAgentParser
{
    public static function os(?string $ua): ?string
    {
        $ua = (string) $ua;
        return match (true) {
            str_contains($ua, 'Windows NT 10') => 'Windows 10/11',
            str_contains($ua, 'Windows')        => 'Windows',
            str_contains($ua, 'Android')        => 'Android',
            str_contains($ua, 'iPhone'),
            str_contains($ua, 'iPad')           => 'iOS',
            str_contains($ua, 'Mac OS X')       => 'macOS',
            str_contains($ua, 'Linux')          => 'Linux',
            default                             => 'سایر',
        };
    }

    public static function browser(?string $ua): ?string
    {
        $ua = (string) $ua;
        return match (true) {
            str_contains($ua, 'Edg/')           => 'Edge',
            str_contains($ua, 'OPR/'),
            str_contains($ua, 'Opera')          => 'Opera',
            str_contains($ua, 'SamsungBrowser') => 'Samsung Internet',
            str_contains($ua, 'Firefox/')       => 'Firefox',
            str_contains($ua, 'Chrome/')        => 'Chrome',
            str_contains($ua, 'Safari/')        => 'Safari',
            default                             => 'سایر',
        };
    }
}