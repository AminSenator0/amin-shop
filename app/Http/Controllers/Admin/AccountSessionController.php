<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountSession;
use App\Services\AccountSessionService;
use Illuminate\Http\Request;

class AccountSessionController extends Controller
{
    public function index(Request $request)
    {
        $sessions = AccountSession::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_active_at')
            ->paginate(15);

        $currentSessionId = $request->session()->getId();

        return view('admin.account-sessions.index', compact('sessions', 'currentSessionId'));
    }

    public function destroy(Request $request, AccountSession $accountSession, AccountSessionService $service)
    {
        abort_if($accountSession->user_id !== $request->user()->id, 403);

        $service->terminate($accountSession);

        return back()->with('success', 'نشست با موفقیت خاتمه یافت.');
    }
}