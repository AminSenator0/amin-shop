<?php

namespace App\Services;

use App\Models\AccountSession;
use App\Models\User;
use App\Support\GeoIp;
use App\Support\UserAgentParser;
use Illuminate\Http\Request;

class AccountSessionService
{
    /**
     * ثبت ورود: اگر IP قبلاً برای این کاربر ثبت شده فقط زمان فعالیت آپدیت می‌شود
     * و فقط IP جدید ردیف جدید می‌سازد (مثل تلگرام).
     */
    public function record(Request $request, User $user): AccountSession
    {
        $ip = (string) $request->ip();

        $session = AccountSession::query()
            ->where('user_id', $user->id)
            ->where('ip_address', $ip)
            ->first();

        if ($session) {
            $session->update([
                'session_id' => $request->session()->getId(),
                'last_active_at' => now(),
            ]);

            return $session;
        }

        $geo = GeoIp::lookup($ip);

        return AccountSession::create([
            'user_id' => $user->id,
            'session_id' => $request->session()->getId(),
            'ip_address' => $ip,
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'browser' => UserAgentParser::browser($request->userAgent()),
            'os' => UserAgentParser::os($request->userAgent()),
            'country' => $geo['country'],
            'city' => $geo['city'],
            'last_active_at' => now(),
        ]);
    }

    /** خاتمه دادن به یک نشست (حذف رکورد + انهدام سشن واقعی) */
    public function terminate(AccountSession $session): void
    {
        if ($session->session_id) {
            try {
                app('session.handler')->destroy($session->session_id);
            } catch (\Throwable) {
                // سشن از قبل منقضی شده
            }
        }

        $session->delete();
    }
}