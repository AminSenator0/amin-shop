@extends('layouts.admin')

@section('header', $user->name)

@section('content')
<div class="flex flex-wrap items-center gap-3 mb-6">
    <a href="{{ route('admin.users.index') }}" class="admin-btn-secondary text-xs">
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
        <span>بازگشت به لیست</span>
    </a>
    @if($user->role === \App\Enums\UserRole::Admin)
        <span class="admin-badge-indigo">{{ $user->role->label() }}</span>
    @else
        <span class="admin-badge-neutral">{{ $user->role->label() }}</span>
    @endif
    @if($user->email_verified_at)
        <span class="admin-badge-success">ایمیل تأیید شده</span>
    @else
        <span class="admin-badge-warning">ایمیل تأیید نشده</span>
    @endif
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 mb-6">
    <x-admin.stat-card label="تعداد سفارش" :value="$user->orders_count" color="blue"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>' />
    <x-admin.stat-card label="مجموع خرید" :value="format_price($totalSpent)" color="emerald"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
    <x-admin.stat-card label="آدرس‌ها" :value="$user->addresses_count" color="violet"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>' />
    <x-admin.stat-card label="نظرات" :value="$user->reviews_count" color="amber"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>' />
    {{-- ⬇️ کارت کیف پول ⬇️ --}}
    <x-admin.stat-card label="موجودی کیف پول" :value="format_price($walletBalance)" color="teal"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>' />
</div>

<div class="grid gap-6 lg:grid-cols-3 mb-6">
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="admin-card" x-data="{ name: @js(old('name', $user->name)), email: @js(old('email', $user->email)) }">
        @csrf
        @method('PATCH')

        <div class="admin-card-header !px-5 !pt-5 !pb-0">
            <h2 class="admin-card-title flex items-center gap-2">
                <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                <span>اطلاعات کاربر</span>
            </h2>
            <p class="text-xs text-zinc-500">عضویت از {{ format_jalali($user->created_at) }}</p>
        </div>

        <div class="space-y-5 p-5">
            <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-zinc-50/80 p-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-xl font-black text-indigo-600">{{ mb_substr(old('name', $user->name), 0, 1) }}</div>
                <div class="min-w-0">
                    <p class="font-bold text-zinc-900 truncate" x-text="name || '—'"></p>
                    <p class="text-xs text-zinc-500 mt-0.5 truncate" dir="ltr" x-text="email || '—'"></p>
                </div>
            </div>

            <div>
                <label for="name" class="admin-field-label">نام <span class="text-rose-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                    class="admin-input w-full" x-model="name">
                @error('name')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="admin-field-label">ایمیل <span class="text-rose-500">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="admin-input w-full" dir="ltr" x-model="email">
                @error('email')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="phone" class="admin-field-label">تلفن</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                    placeholder="۰۹۱۲۳۴۵۶۷۸۹" data-phone-input class="admin-input w-full" dir="ltr" inputmode="numeric">
                @error('phone')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="role" class="admin-field-label">نقش <span class="text-rose-500">*</span></label>
                <select id="role" name="role" class="admin-select w-full">
                    @foreach(\App\Enums\UserRole::cases() as $role)
                        <option value="{{ $role->value }}" @selected(old('role', $user->role->value) === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
                @error('role')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>

            <div class="rounded-2xl border border-zinc-100 bg-zinc-50/50 p-4 space-y-4">
                <p class="text-xs font-bold text-zinc-500">تغییر رمز عبور (اختیاری)</p>
                <div>
                    <label for="password" class="admin-field-label">رمز عبور جدید</label>
                    <input type="password" id="password" name="password" autocomplete="new-password"
                        class="admin-input w-full" dir="ltr">
                    @error('password')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="admin-field-label">تکرار رمز عبور</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                        class="admin-input w-full" dir="ltr">
                </div>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-5">
                <button type="submit" class="admin-btn-primary w-full sm:w-auto">ذخیره تغییرات</button>
                <a href="{{ route('admin.users.show', $user) }}" class="admin-btn-secondary w-full sm:w-auto">انصراف</a>
            </div>
        </div>
    </form>

    {{-- ⬇️ کارت کیف پول ⬇️ --}}
    <div class="admin-card" x-data="{ showAdjust: false }">
        <div class="admin-card-header">
            <h2 class="admin-card-title flex items-center gap-2">
                <svg class="h-4 w-4 shrink-0 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                <span>کیف پول</span>
            </h2>
            <span class="text-sm font-bold text-teal-600">{{ format_price($walletBalance) }}</span>
        </div>

        <div class="p-5 space-y-5">
            {{-- موجودی فعلی --}}
            <div class="flex items-center justify-between rounded-2xl border border-teal-100 bg-teal-50/50 p-4">
                <div>
                    <p class="text-xs text-teal-600 font-medium">موجودی فعلی</p>
                    <p class="text-2xl font-black text-teal-700 mt-1">{{ format_price($walletBalance) }}</p>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-teal-100 text-teal-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                </div>
            </div>

            {{-- ۱۰ تراکنش آخر --}}
            @if($wallet && $wallet->transactions->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="admin-table text-sm">
                        <thead>
                            <tr>
                                <th>نوع</th>
                                <th>مبلغ</th>
                                <th>موجودی بعد</th>
                                <th>دلیل</th>
                                <th>تاریخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($wallet->transactions as $tx)
                                <tr>
                                    <td>
                                        @if($tx->type->value === 'credit')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-600">واریز</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-600">برداشت</span>
                                        @endif
                                    </td>
                                    <td class="font-bold">{{ format_price($tx->amount) }}</td>
                                    <td class="text-zinc-500">{{ format_price($tx->balance_after) }}</td>
                                    <td class="text-zinc-500 truncate max-w-[120px]" title="{{ $tx->description }}">{{ $tx->description ?: '—' }}</td>
                                    <td class="text-zinc-400 text-xs whitespace-nowrap">{{ format_jalali($tx->created_at) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-6 text-zinc-400 text-sm">تراکنشی ثبت نشده</div>
            @endif

            {{-- دکمه‌ی تنظیم دستی --}}
            <button type="button" @click="showAdjust = !showAdjust" class="admin-btn-secondary w-full text-xs">
                <span x-text="showAdjust ? 'بستن فرم' : 'تنظیم دستی موجودی'"></span>
            </button>

            {{-- فرم تنظیم دستی --}}
            <form x-show="showAdjust" x-cloak method="POST" action="{{ route('admin.users.wallet.adjust', $user) }}" class="space-y-4 border-t border-zinc-100 pt-4">
                @csrf
                <div>
                    <label class="admin-field-label">مبلغ (تومان) <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount') }}" required
                        class="admin-input w-full" dir="ltr"
                        placeholder="مثبت = واریز | منفی = برداشت">
                    <p class="text-xs text-zinc-400 mt-1">عدد مثبت = واریز به کیف پول | عدد منفی = کسر از کیف پول</p>
                    {{-- ⬇️ خطای amount ⬇️ --}}
                    @error('amount')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="admin-field-label">دلیل <span class="text-rose-500">*</span></label>
                    <input type="text" name="reason" value="{{ old('reason') }}" required
                        class="admin-input w-full"
                        placeholder="مثلاً: اصلاح اشتباه سیستمی">
                    {{-- ⬇️ خطای reason ⬇️ --}}
                    @error('reason')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="admin-btn-primary w-full text-xs">اعمال تراکنش</button>
            </form>
        </div>
    </div>

    <div class="admin-card lg:col-span-2">
        <div class="admin-card-header">
            <h2 class="admin-card-title flex items-center gap-2">
                <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                <span>سفارشات</span>
            </h2>
            @if($user->orders_count > 10)
                <span class="text-xs text-zinc-500">۱۰ سفارش اخیر از {{ $user->orders_count }}</span>
            @endif
        </div>
        @if($user->orders->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>شماره سفارش</th>
                            <th>مبلغ</th>
                            <th>وضعیت</th>
                            <th>تاریخ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->orders as $order)
                            <tr>
                                <td class="font-bold text-zinc-900">{{ $order->order_number }}</td>
                                <td class="font-bold">{{ format_price($order->total) }}</td>
                                <td class="whitespace-nowrap"><x-admin.status-badge :status="$order->status" /></td>
                                <td class="text-zinc-500">{{ format_jalali($order->created_at) }}</td>
                                <td>
                                    <x-admin.table-actions>
                                        <x-admin.table-action type="view" :href="route('admin.orders.show', $order)" title="جزئیات" />
                                    </x-admin.table-actions>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </div>
                <p class="font-bold text-zinc-700">سفارشی ثبت نشده</p>
                <p class="mt-1 text-sm text-zinc-500">این کاربر هنوز خریدی انجام نداده است.</p>
            </div>
        @endif
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title flex items-center gap-2">
            <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            <span>آدرس‌ها</span>
        </h2>
        <span class="text-sm text-zinc-500">{{ $user->addresses_count }} آدرس</span>
    </div>
    @if($user->addresses->isNotEmpty())
        <div class="divide-y divide-zinc-100">
            @foreach($user->addresses as $address)
                <div class="flex flex-wrap items-start gap-4 px-5 py-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <p class="font-bold text-zinc-900">{{ $address->title ?: 'آدرس' }}</p>
                            @if($address->is_default)
                                <span class="admin-badge-success text-[10px] px-2 py-0.5">پیش‌فرض</span>
                            @endif
                        </div>
                        <p class="text-sm text-zinc-700">{{ $address->full_name }} — <span dir="ltr">{{ $address->phone }}</span></p>
                        <p class="text-sm text-zinc-500 mt-1">{{ $address->fullAddress() }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </div>
            <p class="font-bold text-zinc-700">آدرسی ثبت نشده</p>
            <p class="mt-1 text-sm text-zinc-500">این کاربر هنوز آدرسی اضافه نکرده است.</p>
        </div>
    @endif
</div>
@endsection