<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->withCount('orders')->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => User::count(),
            'admins' => User::where('role', \App\Enums\UserRole::Admin)->count(),
            'customers' => User::where('role', \App\Enums\UserRole::Customer)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'password' => $data['password'],
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'کاربر جدید ایجاد شد.');
    }

        /** جستجوی زنده مشتری (برای سفارش دستی) */
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $users = \App\Models\User::query()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone']);

        return response()->json($users);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = User::query()->withCount('orders');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->get();
        $filename = 'users-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['نام', 'ایمیل', 'تلفن', 'نقش', 'تعداد سفارش', 'تاریخ عضویت']);

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->name,
                    $user->email,
                    $user->phone ?? '—',
                    $user->role->label(),
                    $user->orders_count,
                    format_jalali($user->created_at, 'Y/m/d H:i', false),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function show(User $user)
    {
        $user->loadCount(['orders', 'addresses', 'reviews']);
        $user->load(['orders' => fn ($q) => $q->latest()->take(10), 'addresses']);

        $totalSpent = $user->orders()->where('payment_status', PaymentStatus::Paid)->sum('total');

        return view('admin.users.show', compact('user', 'totalSpent'));
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $data = $request->validated();
        $emailChanged = $data['email'] !== $user->email;

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'اطلاعات کاربر به‌روزرسانی شد.');
    }

    public function destroy(User $user)
    {
        // ادمین اصلی (خودش) رو نمی‌تونه حذف کنه
        if ($user->id === auth()->id()) {
            return back()->with('error', 'شما نمی‌توانید حساب کاربری خود را حذف کنید.');
        }

        $name = $user->name;
        $email = $user->email;

        // لاگ حذف کاربر
        \App\Services\AuditLogService::log(
            \App\Enums\LogAction::USER_DELETED,
            auth()->id(),
            ['deleted_user_id' => $user->id, 'name' => $name, 'email' => $email],
            severity: \App\Enums\LogSeverity::HIGH
        );

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "کاربر {$name} با موفقیت حذف شد.");
    }
}
