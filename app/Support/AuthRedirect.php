<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\RedirectResponse;

class AuthRedirect
{
    public static function dashboardRoute(User $user): string
    {
        return $user->isAdmin() ? 'admin.dashboard' : 'user.dashboard';
    }

    public static function afterLogin(User $user, ?string $defaultRoute = null, string $query = ''): RedirectResponse
    {
        $defaultRoute ??= static::dashboardRoute($user);
        $default = route($defaultRoute, absolute: false).$query;

        $intended = session()->pull('url.intended');

        if ($intended && static::isIntendedAllowedForUser($intended, $user)) {
            return redirect($intended);
        }

        return redirect($default);
    }

    public static function isIntendedAllowedForUser(string $url, User $user): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '/';

        if ($user->isAdmin()) {
            return str_starts_with($path, '/admin');
        }

        return ! str_starts_with($path, '/admin');
    }
}
