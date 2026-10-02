@extends('layouts.admin')

@section('header', 'شارژهای کیف پول')

@section('content')
<div class="flex flex-wrap items-center gap-3 mb-6">
    <a href="{{ route('admin.dashboard') }}" class="admin-btn-secondary text-xs">
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
        <span>داشبورد</span>
    </a>
    @if($pendingCount > 0)
        <span class="admin-badge-warning">{{ $pendingCount }} در انتظار بررسی</span>
    @endif
</div>

{{-- فیلترها --}}
<div class="flex flex-wrap gap-2 mb-4">
    <a href="{{ route('admin.wallet.deposits.index') }}"
        class="{{ !request('status') && !request('gateway') ? 'admin-btn-primary' : 'admin-btn-secondary' }} text-xs">همه</a>
    @foreach(['pending' => 'در انتظار', 'paid' => 'پرداخت‌شده', 'rejected' => 'رد شده', 'expired' => 'منقضی'] as $value => $label)
        <a href="{{ route('admin.wallet.deposits.index', ['status' => $value]) }}"
            class="{{ request('status') === $value ? 'admin-btn-primary' : 'admin-btn-secondary' }} text-xs">{{ $label }}</a>
    @endforeach
    <span class="mx-1 hidden sm:inline text-zinc-300">|</span>
    @foreach(['zarinpal' => 'زرین‌پال', 'c2c' => 'کارت به کارت'] as $value => $label)
        <a href="{{ route('admin.wallet.deposits.index', array_filter(['status' => request('status'), 'gateway' => $value])) }}"
            class="{{ request('gateway') === $value ? 'admin-btn-primary' : 'admin-btn-secondary' }} text-xs">{{ $label }}</a>
    @endforeach
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title flex items-center gap-2">
            <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9" /></svg>
            <span>لیست شارژها</span>
        </h2>
        <span class="text-sm text-zinc-500">{{ $deposits->total() }} مورد</span>
    </div>

    @if($deposits->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>کاربر</th>
                        <th>مبلغ</th>
                        <th>درگاه</th>
                        <th>وضعیت</th>
                        <th>جزئیات</th>
                        <th>تاریخ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deposits as $deposit)
                        <tr x-data="{ showReject: false }">
                            <td>
                                <a href="{{ route('admin.users.show', $deposit->user) }}" class="font-bold text-zinc-900 hover:text-teal-600">{{ $deposit->user->name }}</a>
                                <p class="text-xs text-zinc-400" dir="ltr">{{ $deposit->user->email }}</p>
                            </td>
                            <td class="font-bold">{{ format_price($deposit->amount) }}</td>
                            <td>
                                <span class="admin-badge-{{ $deposit->gateway->value === 'zarinpal' ? 'indigo' : 'neutral' }}">{{ $deposit->gateway->label() }}</span>
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="admin-badge-{{ $deposit->status->color() }}">{{ $deposit->status->label() }}</span>
                            </td>
                            <td class="text-xs text-zinc-500">
                                @if($deposit->gateway->value === 'c2c' && $deposit->receipt_path)
                                    <a href="{{ Storage::url($deposit->receipt_path) }}" target="_blank" class="text-teal-600 hover:underline">مشاهده رسید</a>
                                    @if($deposit->tracking_code)
                                        <p class="mt-0.5" dir="ltr">{{ $deposit->tracking_code }}</p>
                                    @endif
                                @elseif($deposit->gateway->value === 'zarinpal' && $deposit->authority)
                                    <span dir="ltr" class="block max-w-[140px] truncate">{{ $deposit->authority }}</span>
                                @else
                                    —
                                @endif
                                @if($deposit->rejection_reason)
                                    <p class="mt-0.5 text-rose-500">{{ $deposit->rejection_reason }}</p>
                                @endif
                            </td>
                            <td class="text-zinc-500 whitespace-nowrap">{{ format_jalali($deposit->created_at) }}</td>
                            <td>
                                @if($deposit->status->value === 'pending')
                                    <div class="flex flex-col gap-2">
                                        <form method="POST" action="{{ route('admin.wallet.deposits.verify', $deposit) }}"
                                            onsubmit="return confirm('تأیید شارژ {{ format_price($deposit->amount) }} برای {{ $deposit->user->name }}؟')">
                                            @csrf
                                            <button type="submit" class="admin-btn-primary text-xs w-full">تأیید و واریز</button>
                                        </form>
                                        <button type="button" @click="showReject = !showReject" class="admin-btn-secondary text-xs w-full">رد</button>
                                        <form x-show="showReject" x-cloak method="POST" action="{{ route('admin.wallet.deposits.reject', $deposit) }}" class="space-y-2">
                                            @csrf
                                            <input type="text" name="rejection_reason" required placeholder="دلیل رد" class="admin-input w-full text-xs">
                                            <button type="submit" class="admin-btn-secondary text-xs w-full !text-rose-600">ثبت رد</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-400">بررسی شده</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-100 px-5 py-4">
            {{ $deposits->links() }}
        </div>
    @else
        <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9" /></svg>
            </div>
            <p class="font-bold text-zinc-700">شارژی یافت نشد</p>
            <p class="mt-1 text-sm text-zinc-500">هنوز درخواست شارژی برای کیف پول ثبت نشده است.</p>
        </div>
    @endif
</div>
@endsection