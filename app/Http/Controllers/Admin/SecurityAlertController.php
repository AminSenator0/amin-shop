<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityAlert;
use Illuminate\Http\Request;

class SecurityAlertController extends Controller
{
    public function index(Request $request)
    {
        $alerts = SecurityAlert::with('user')
            ->orderBy('created_at', 'desc')
            ->when($request->severity, fn($q) => $q->where('severity', $request->severity))
            ->when($request->status === 'resolved', fn($q) => $q->where('is_resolved', true))
            ->when($request->status === 'unresolved', fn($q) => $q->where('is_resolved', false))
            ->paginate(30);

        return view('admin.audit-logs.alerts', compact('alerts'));
    }

    public function resolve(SecurityAlert $alert)
    {
        $alert->markResolved(auth()->id());
        return back()->with('success', 'هشدار برطرف شد.');
    }
}