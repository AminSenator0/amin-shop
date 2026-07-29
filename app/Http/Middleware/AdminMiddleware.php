<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            return redirect()
                ->route('user.dashboard')
                ->with('error', 'شما دسترسی مدیر ندارید. با حساب مدیر وارد شوید.');
        }

        return $next($request);
    }
}
