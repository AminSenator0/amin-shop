<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedIp;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class BlockedIpController extends Controller
{
    public function index()
    {
        $ips = BlockedIp::with('blocker')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('admin.audit-logs.blocked-ips', compact('ips'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'required|string|max:255',
            'blocked_until' => 'nullable|date',
        ]);

        AuditLogService::blockIp(
            $request->ip_address,
            $request->reason,
            auth()->id(),
            $request->blocked_until ? \Carbon\Carbon::parse($request->blocked_until) : null
        );

        return back()->with('success', 'IP مسدود شد.');
    }

    public function destroy(BlockedIp $blockedIp)
    {
        $blockedIp->delete();
        \Cache::forget('blocked_ip:' . $blockedIp->ip_address);
        return back()->with('success', 'IP آزاد شد.');
    }
}