@extends('layouts.user')

@section('title', 'کیف پول')

@section('content')
@php
    $currency = \App\Support\StoreSettings::get('currency', 'تومان');
    $minWithdrawal = (int) \App\Support\StoreSettings::get('wallet_min_withdrawal', 200000);
    $recentWithdrawals = $wallet->withdrawals()->latest()->take(5)->get();
    $quickAmounts = [50000, 100000, 200000, 500000, 1000000, 2000000];
@endphp

<div
    class="mx-auto w-full min-w-0 max-w-5xl overflow-x-clip px-3 py-5 sm:px-6 sm:py-7"
    x-data="{
        tab: 'charge',
        gateway: 'zarinpal',
        amount: '{{ old('amount') }}',
        enDigits(v) {
            return String(v ?? '')
                .replace(/[۰-۹]/g, ch => '۰۱۲۳۴۵۶۷۸۹'.indexOf(ch))
                .replace(/[٠-٩]/g, ch => '٠١٢٣٤٥٦٧٨٩'.indexOf(ch))
                .replace(/[^\d]/g, '');
        },
        fmtAmount(v) {
            const n = this.enDigits(v);
            return n ? Number(n).toLocaleString('en-US') : '';
        }
    }"
>
    <x-user.breadcrumb :items="[
        ['label' => 'داشبورد', 'url' => route('user.dashboard')],
        ['label' => 'کیف پول'],
    ]" />

    {{-- ═══════════ کارت موجودی ═══════════ --}}
    <div class="relative isolate mb-6 w-full min-w-0 overflow-hidden rounded-3xl
        bg-gradient-to-l from-[#0c4f4b] via-[#16827D] to-[#2ec4b6]
        p-5 text-white shadow-xl shadow-teal-900/20 sm:p-8">

        {{-- دکور پس‌زمینه --}}
        <div class="pointer-events-none absolute -left-12 -top-16 h-52 w-52 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-12 h-64 w-64 rounded-full bg-emerald-300/20"></div>
        <div class="pointer-events-none absolute bottom-0 left-1/3 h-32 w-32 rounded-full bg-white/5 blur-2xl"></div>

        <div class="relative flex min-w-0 flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">

            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 text-sm font-medium text-teal-50/90">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/15">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"/>
                        </svg>
                    </span>
                    <span>موجودی کیف پول</span>
                </div>

                <p class="mt-4 break-words text-3xl font-black tracking-tight sm:text-4xl">
                    {{ format_price($wallet->balance) }}
                </p>

                <p class="mt-2 text-xs leading-6 text-teal-50/70">
                    موجودی قابل استفاده در خرید و پرداخت‌های شما
                </p>
            </div>

            <div class="flex w-full flex-col gap-3 sm:w-auto sm:min-w-[170px]">
                <button
                    type="button"
                    @click="tab = 'charge'; $refs.chargeBox.scrollIntoView({behavior:'smooth', block:'start'})"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl
                        bg-white px-5 py-3.5 text-sm font-bold text-[#0c4f4b]
                        shadow-md transition hover:bg-teal-50 active:scale-[.98]"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    شارژ کیف پول
                </button>

                @if($wallet->balance > 0)
                    <button
                        type="button"
                        @click="tab = 'withdraw'; $refs.chargeBox.scrollIntoView({behavior:'smooth', block:'start'})"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl
                            border border-white/40 bg-white/10 px-5 py-3.5
                            text-sm font-bold text-white transition
                            hover:bg-white/20 active:scale-[.98]"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/>
                        </svg>
                        برداشت وجه
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════ تب‌ها و فرم‌ها ═══════════ --}}
    <div
        x-ref="chargeBox"
        class="mb-6 w-full min-w-0 scroll-mt-5 overflow-hidden rounded-3xl
            border border-gray-200/80 bg-white shadow-sm"
    >
        <div class="border-b border-gray-100 px-4 py-5 sm:px-7">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 8.25h19.5M3.75 5.25h16.5A1.5 1.5 0 0121.75 6.75v10.5a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5V6.75a1.5 1.5 0 011.5-1.5z"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <h2 class="text-base font-black text-gray-800">مدیریت کیف پول</h2>
                    <p class="mt-1 text-xs leading-5 text-gray-400">شارژ کیف پول یا درخواست برداشت وجه</p>
                </div>
            </div>
        </div>

        <div class="min-w-0 p-4 sm:p-7">

            {{-- سوییچ تب --}}
            <div class="mb-7 grid grid-cols-2 gap-2 rounded-2xl bg-gray-100/80 p-1.5">
                <button
                    type="button"
                    @click="tab = 'charge'"
                    :class="tab === 'charge'
                        ? 'bg-white text-teal-700 shadow-sm ring-1 ring-gray-200/70'
                        : 'text-gray-500 hover:bg-white/60 hover:text-gray-700'"
                    class="inline-flex min-w-0 items-center justify-center gap-2 rounded-xl px-2 py-3 text-xs font-bold transition sm:text-sm"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    <span>شارژ کیف پول</span>
                </button>

                <button
                    type="button"
                    @click="tab = 'withdraw'"
                    :class="tab === 'withdraw'
                        ? 'bg-white text-amber-700 shadow-sm ring-1 ring-gray-200/70'
                        : 'text-gray-500 hover:bg-white/60 hover:text-gray-700'"
                    class="inline-flex min-w-0 items-center justify-center gap-2 rounded-xl px-2 py-3 text-xs font-bold transition sm:text-sm"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/>
                    </svg>
                    <span>برداشت وجه</span>
                </button>
            </div>

            {{-- ═══════════ تب شارژ ═══════════ --}}
            <div x-show="tab === 'charge'" x-cloak>

                {{-- انتخاب درگاه --}}
                <div class="mb-6">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-bold text-gray-800">روش پرداخت</h3>
                        <span class="text-[11px] text-gray-400">یک روش را انتخاب کنید</span>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                        {{-- زرین‌پال --}}
                        <button
                            type="button"
                            @click="gateway = 'zarinpal'"
                            :class="gateway === 'zarinpal'
                                ? 'border-teal-500 bg-teal-50/70 ring-2 ring-teal-500/10'
                                : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50/70'"
                            class="flex min-w-0 items-center gap-3 rounded-2xl border-2 p-4 text-right transition"
                        >
                            <span
                                :class="gateway === 'zarinpal' ? 'border-teal-600 bg-teal-600' : 'border-gray-300 bg-white'"
                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2"
                            >
                                <span x-show="gateway === 'zarinpal'" class="h-2 w-2 rounded-full bg-white"></span>
                            </span>

                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-600 text-white shadow-sm">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 7.5h18M5.25 4.5h13.5A2.25 2.25 0 0121 6.75v10.5a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 17.25V6.75A2.25 2.25 0 015.25 4.5z"/>
                                </svg>
                            </span>

                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-bold text-gray-800">زرین‌پال</span>
                                <span class="mt-1 block text-xs leading-5 text-gray-400">پرداخت آنلاین و شارژ آنی</span>
                            </span>
                        </button>

                        {{-- کارت به کارت --}}
                        <button
                            type="button"
                            @click="gateway = 'c2c'"
                            :class="gateway === 'c2c'
                                ? 'border-teal-500 bg-teal-50/70 ring-2 ring-teal-500/10'
                                : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50/70'"
                            class="flex min-w-0 items-center gap-3 rounded-2xl border-2 p-4 text-right transition"
                        >
                            <span
                                :class="gateway === 'c2c' ? 'border-teal-600 bg-teal-600' : 'border-gray-300 bg-white'"
                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2"
                            >
                                <span x-show="gateway === 'c2c'" class="h-2 w-2 rounded-full bg-white"></span>
                            </span>

                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 8.25h19.5M3.75 5.25h16.5A1.5 1.5 0 0121.75 6.75v10.5a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5V6.75a1.5 1.5 0 011.5-1.5z"/>
                                </svg>
                            </span>

                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-bold text-gray-800">کارت به کارت</span>
                                <span class="mt-1 block text-xs leading-5 text-gray-400">واریز دستی با تأیید مدیریت</span>
                            </span>
                        </button>
                    </div>
                </div>

                {{-- مبالغ سریع --}}
                <div class="mb-6">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <h3 class="text-sm font-bold text-gray-800">مبلغ شارژ</h3>
                        <span class="text-xs text-gray-400">{{ $currency }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
                        @foreach($quickAmounts as $qa)
                            <button
                                type="button"
                                @click="amount = '{{ $qa }}'"
                                :class="parseInt(amount) === {{ $qa }}
                                    ? 'border-teal-500 bg-teal-50 text-teal-700 ring-2 ring-teal-500/10'
                                    : 'border-gray-200 bg-white text-gray-600 hover:border-teal-300 hover:bg-teal-50/50'"
                                class="flex min-w-0 items-center justify-center rounded-xl border-2 px-2 py-3 text-xs font-bold transition sm:text-sm"
                            >
                                {{ format_number($qa) }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- فرم زرین‌پال --}}
                <form x-show="gateway === 'zarinpal'" x-cloak method="POST" action="{{ route('wallet.charge') }}" class="min-w-0">
                    @csrf

                    <div class="mb-5 min-w-0">
                        <label class="mb-2 block text-sm font-bold text-gray-700">
                            مبلغ پرداخت
                            <span class="text-xs font-medium text-gray-400">({{ $currency }})</span>
                            <span class="text-rose-500">*</span>
                        </label>

                        <input
                            type="text"
                            :value="fmtAmount(amount)"
                            @input="amount = enDigits($event.target.value)"
                            required
                            placeholder="مثلاً 500,000"
                            dir="ltr"
                            inputmode="numeric"
                            class="block w-full min-w-0 max-w-full rounded-2xl border-2 border-gray-200
                                bg-gray-50 px-4 py-4 text-left text-base font-bold text-gray-800
                                outline-none transition placeholder:font-medium placeholder:text-gray-400
                                focus:border-teal-500 focus:bg-white"
                        >
                        <input type="hidden" name="amount" :value="amount">

                        <div class="mt-2 flex flex-wrap items-center justify-between gap-1 text-xs">
                            <span class="text-gray-400">حداقل: {{ format_price($minDeposit) }}</span>
                            <span class="text-gray-400">حداکثر: {{ format_price($maxDeposit) }}</span>
                        </div>

                        <template x-if="parseInt(amount) > 0">
                            <p class="mt-2 text-xs font-bold text-teal-700">
                                مبلغ قابل پرداخت: <span x-text="fmtAmount(amount)"></span> {{ $currency }}
                            </p>
                        </template>

                        @error('amount')
                            <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl
                            bg-teal-600 px-5 py-4 text-sm font-black text-white
                            shadow-lg shadow-teal-600/20 transition hover:bg-teal-700
                            active:scale-[.99]"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        پرداخت و شارژ کیف پول
                    </button>
                </form>

                {{-- فرم کارت به کارت --}}
                <div x-show="gateway === 'c2c'" x-cloak class="min-w-0">

                    @php($c2cCards = \App\Support\StoreSettings::c2cCards())

                    @forelse($c2cCards as $card)
                        <div class="mb-3 rounded-2xl border border-teal-100 bg-gradient-to-l from-teal-50 to-white p-4 sm:p-5">
                            <p class="mb-2 text-xs text-teal-700">مبلغ را به این کارت واریز کنید:</p>

                            <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <p class="break-all text-lg font-black tracking-wider text-teal-800 sm:text-xl" dir="ltr">
                                    {{ implode(' ', str_split($card['number'], 4)) }}
                                </p>

                                <button
                                    type="button"
                                    onclick="navigator.clipboard.writeText('{{ $card['number'] }}').then(()=>{this.textContent='کپی شد!';setTimeout(()=>this.textContent='کپی شماره کارت',2000)})"
                                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl
                                        border border-teal-200 bg-white px-4 py-2.5
                                        text-xs font-bold text-teal-700 transition hover:bg-teal-100"
                                >
                                    کپی شماره کارت
                                </button>
                            </div>

                            <p class="mt-2 text-sm font-bold text-teal-800">
                                {{ $card['owner'] }}
                                @if($card['bank'] !== '') — {{ $card['bank'] }} @endif
                            </p>
                        </div>
                    @empty
                        <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-800">
                            کارت مقصد هنوز توسط مدیر فروشگاه تعریف نشده است. لطفاً بعداً تلاش کنید.
                        </div>
                    @endforelse

                    @if(count($c2cCards) > 0)
                        <div class="mb-5 flex items-start gap-2 rounded-xl bg-amber-50 p-3 text-xs leading-6 text-amber-800">
                            <svg class="mt-1 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            </svg>
                            <span>مبلغ واریزی باید دقیقاً با مبلغ درخواستی یکسان باشد.</span>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('wallet.charge-c2c') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-5 grid min-w-0 grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="min-w-0">
                                <label class="mb-2 block text-sm font-bold text-gray-700">
                                    مبلغ واریزی
                                    <span class="text-xs font-medium text-gray-400">({{ $currency }})</span>
                                    <span class="text-rose-500">*</span>
                                </label>

                                <input
                                    type="text"
                                    :value="fmtAmount(amount)"
                                    @input="amount = enDigits($event.target.value)"
                                    required
                                    placeholder="مثلاً 500,000"
                                    dir="ltr"
                                    inputmode="numeric"
                                    class="block w-full min-w-0 rounded-2xl border-2 border-gray-200
                                        bg-gray-50 px-4 py-3.5 text-left font-bold text-gray-800
                                        outline-none transition placeholder:font-medium placeholder:text-gray-400
                                        focus:border-teal-500 focus:bg-white"
                                >
                                <input type="hidden" name="amount" :value="amount">

                                <template x-if="parseInt(amount) > 0">
                                    <p class="mt-2 text-xs font-bold text-teal-700">
                                        مبلغ واریزی: <span x-text="fmtAmount(amount)"></span> {{ $currency }}
                                    </p>
                                </template>

                                @error('amount')
                                    <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="min-w-0">
                                <label class="mb-2 block text-sm font-bold text-gray-700">
                                    کد رهگیری
                                    <span class="text-xs font-medium text-gray-400">(اختیاری)</span>
                                </label>

                                <input
                                    type="text"
                                    name="tracking_code"
                                    maxlength="50"
                                    value="{{ old('tracking_code') }}"
                                    placeholder="کد رهگیری بانکی"
                                    dir="ltr"
                                    class="block w-full min-w-0 rounded-2xl border-2 border-gray-200
                                        bg-gray-50 px-4 py-3.5 text-left font-bold text-gray-800
                                        outline-none transition placeholder:font-medium placeholder:text-gray-400
                                        focus:border-teal-500 focus:bg-white"
                                >

                                @error('tracking_code')
                                    <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- آپلود رسید --}}
                        <div class="mb-5 min-w-0">
                            <label class="mb-2 block text-sm font-bold text-gray-700">
                                تصویر رسید واریز
                                <span class="text-rose-500">*</span>
                            </label>

                            <label class="group flex min-w-0 cursor-pointer flex-col items-center justify-center
                                gap-2 rounded-2xl border-2 border-dashed border-gray-300
                                bg-gray-50/70 px-4 py-7 text-center transition
                                hover:border-teal-400 hover:bg-teal-50/50">

                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-teal-600 shadow-sm transition group-hover:bg-teal-100">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                    </svg>
                                </span>

                                <span class="text-sm font-bold text-gray-700">انتخاب یا آپلود رسید</span>
                                <span class="text-xs text-gray-400">تصویر رسید بانکی را انتخاب کنید</span>

                                <input type="file" name="receipt" required accept="image/jpeg,image/png" class="sr-only">
                            </label>

                            <p class="mt-2 text-xs leading-5 text-gray-400">فرمت مجاز: JPG یا PNG — حداکثر ۵ مگابایت</p>

                            @error('receipt')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl
                                bg-teal-600 px-5 py-4 text-sm font-black text-white
                                shadow-lg shadow-teal-600/20 transition hover:bg-teal-700
                                active:scale-[.99]"
                        >
                            ثبت رسید و درخواست شارژ
                        </button>
                    </form>
                </div>
            </div>

            {{-- ═══════════ تب برداشت ═══════════ --}}
            <div x-show="tab === 'withdraw'" x-cloak>

                @if($wallet->balance <= 0)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-center">
                        <span class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            </svg>
                        </span>
                        <p class="text-sm font-bold text-amber-800">موجودی کیف پول شما صفر است</p>
                        <p class="mt-1 text-xs leading-6 text-amber-700">برای برداشت وجه، ابتدا کیف پول خود را شارژ کنید.</p>

                        <button
                            type="button"
                            @click="tab = 'charge'"
                            class="mt-4 rounded-xl bg-amber-600 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-amber-700"
                        >
                            شارژ کیف پول
                        </button>
                    </div>
                @else
                    <form method="POST" action="{{ route('wallet.withdraw') }}">
                        @csrf

                        <div class="mb-6 rounded-2xl border border-amber-100 bg-gradient-to-l from-amber-50 to-white p-4 sm:p-5">
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/>
                                    </svg>
                                </span>

                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium text-amber-700">موجودی قابل برداشت</p>
                                    <p class="mt-1 break-words text-lg font-black text-amber-900">
                                        {{ format_price($wallet->balance) }}
                                    </p>
                                </div>
                            </div>

                            <p class="mt-3 text-xs leading-6 text-amber-700">
                                مبلغ درخواستی بلافاصله از کیف پول شما کسر (بلوکه) می‌شود و پس از بررسی، به شبای شما واریز می‌گردد.
                            </p>
                        </div>

                        <div class="mb-5 grid min-w-0 grid-cols-1 gap-4 sm:grid-cols-2">

                            {{-- مبلغ برداشت (با فرمت زنده) --}}
                            <div class="min-w-0" x-data="{ wamt: '{{ old('amount') }}' }">
                                <label class="mb-2 block text-sm font-bold text-gray-700">
                                    مبلغ برداشت
                                    <span class="text-xs font-medium text-gray-400">({{ $currency }})</span>
                                    <span class="text-rose-500">*</span>
                                </label>

                                <input
                                    type="text"
                                    :value="fmtAmount(wamt)"
                                    @input="wamt = enDigits($event.target.value)"
                                    required
                                    placeholder="مثلاً 500,000"
                                    dir="ltr"
                                    inputmode="numeric"
                                    class="block w-full min-w-0 rounded-2xl border-2 border-gray-200
                                        bg-gray-50 px-4 py-3.5 text-left font-bold text-gray-800
                                        outline-none transition placeholder:font-medium placeholder:text-gray-400
                                        focus:border-amber-500 focus:bg-white"
                                >
                                <input type="hidden" name="amount" :value="wamt">

                                <p class="mt-2 text-xs text-gray-400">
                                    حداقل برداشت: {{ format_price($minWithdrawal) }}
                                </p>

                                <template x-if="parseInt(wamt) > 0">
                                    <p class="mt-1.5 text-xs font-bold text-amber-700">
                                        مبلغ قابل برداشت: <span x-text="fmtAmount(wamt)"></span> {{ $currency }}
                                    </p>
                                </template>

                                @error('amount')
                                    <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="min-w-0">
                                <label class="mb-2 block text-sm font-bold text-gray-700">
                                    شماره شبا
                                    <span class="text-rose-500">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="sheba"
                                    value="{{ old('sheba') }}"
                                    required
                                    pattern="^IR\d{24}$"
                                    maxlength="26"
                                    placeholder="IR060120000000002345678901"
                                    dir="ltr"
                                    class="block w-full min-w-0 rounded-2xl border-2 border-gray-200
                                        bg-gray-50 px-4 py-3.5 text-left font-bold text-gray-800
                                        outline-none transition placeholder:font-medium placeholder:text-gray-400
                                        focus:border-amber-500 focus:bg-white"
                                >

                                @error('sheba')
                                    <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl
                                bg-amber-500 px-5 py-4 text-sm font-black text-white
                                shadow-lg shadow-amber-500/20 transition hover:bg-amber-600
                                active:scale-[.99]"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/>
                            </svg>
                            ثبت درخواست برداشت
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════ عملیات اخیر ═══════════ --}}
    @if($recentDeposits->isNotEmpty() || $recentWithdrawals->isNotEmpty())
        <div class="mb-6 w-full min-w-0 overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm">

            <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-5 sm:px-6">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6l4 2m5-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-sm font-black text-gray-800">عملیات اخیر</h2>
                    <p class="mt-1 text-xs text-gray-400">آخرین شارژها و برداشت‌های کیف پول</p>
                </div>
            </div>

            <div class="space-y-3 p-3 sm:p-5">
                @foreach($recentDeposits as $deposit)
                    <div class="flex min-w-0 items-start justify-between gap-3 rounded-2xl border border-gray-100 bg-white p-3 transition hover:border-teal-100 hover:bg-teal-50/20 sm:items-center sm:p-4">

                        <div class="flex min-w-0 items-start gap-3 sm:items-center">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                            </span>

                            <div class="min-w-0">
                                <p class="break-words text-sm font-bold text-gray-800">
                                    شارژ {{ $deposit->gateway->label() }}
                                </p>
                                <p class="mt-1 break-words text-xs leading-5 text-gray-400">
                                    {{ format_price($deposit->amount) }} — {{ format_jalali($deposit->created_at, 'Y/m/d — H:i') }}
                                </p>
                            </div>
                        </div>

                        <span class="shrink-0 rounded-full px-2.5 py-1.5 text-[10px] font-bold sm:px-3 sm:text-xs
                            {{ $deposit->status->value === 'pending' ? 'bg-amber-50 text-amber-700' : '' }}
                            {{ $deposit->status->value === 'paid' ? 'bg-emerald-50 text-emerald-700' : '' }}
                            {{ $deposit->status->value === 'rejected' ? 'bg-rose-50 text-rose-700' : '' }}
                            {{ $deposit->status->value === 'expired' ? 'bg-gray-100 text-gray-500' : '' }}">
                            {{ $deposit->status->label() }}
                        </span>
                    </div>
                @endforeach

                @foreach($recentWithdrawals as $withdrawal)
                    <div class="flex min-w-0 items-start justify-between gap-3 rounded-2xl border border-gray-100 bg-white p-3 transition hover:border-amber-100 hover:bg-amber-50/20 sm:items-center sm:p-4">

                        <div class="flex min-w-0 items-start gap-3 sm:items-center">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/>
                                </svg>
                            </span>

                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-800">برداشت به شبا</p>
                                <p class="mt-1 break-all text-xs leading-5 text-gray-400" dir="ltr">
                                    {{ $withdrawal->sheba }}
                                </p>
                                <p class="mt-1 text-xs text-gray-400">
                                    {{ format_jalali($withdrawal->created_at, 'Y/m/d — H:i') }}
                                </p>
                            </div>
                        </div>

                        <span class="shrink-0 rounded-full px-2.5 py-1.5 text-[10px] font-bold sm:px-3 sm:text-xs
                            {{ $withdrawal->status->value === 'pending' ? 'bg-amber-50 text-amber-700' : '' }}
                            {{ $withdrawal->status->value === 'paid' ? 'bg-emerald-50 text-emerald-700' : '' }}
                            {{ $withdrawal->status->value === 'rejected' ? 'bg-rose-50 text-rose-700' : '' }}">
                            {{ $withdrawal->status->label() }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ═══════════ تاریخچه تراکنش‌ها ═══════════ --}}
    <div class="w-full min-w-0 overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm">

        <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-5 sm:px-6">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8.25 6.75h12m-12 5.25h12m-12 5.25h12M3.75 6.75h.008v.008H3.75V6.75zm0 5.25h.008v.008H3.75V12zm0 5.25h.008v.008H3.75v-.008z"/>
                </svg>
            </span>
            <div>
                <h2 class="text-sm font-black text-gray-800">تاریخچه تراکنش‌ها</h2>
                <p class="mt-1 text-xs text-gray-400">جزئیات و وضعیت تراکنش‌های کیف پول شما</p>
            </div>
        </div>

        @if($transactions->isEmpty())
            <div class="flex flex-col items-center justify-center px-4 py-14 text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 text-gray-400">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h6m-3-3v6M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                    </svg>
                </div>

                <p class="text-sm font-bold text-gray-700">تراکنشی یافت نشد</p>
                <p class="mt-2 text-xs leading-6 text-gray-400">هنوز تراکنشی برای کیف پول شما ثبت نشده است.</p>

                <button
                    type="button"
                    @click="tab = 'charge'; $refs.chargeBox.scrollIntoView({behavior:'smooth', block:'start'})"
                    class="mt-5 rounded-xl bg-teal-600 px-5 py-3 text-xs font-bold text-white transition hover:bg-teal-700"
                >
                    شارژ کیف پول
                </button>
            </div>
        @else
            {{-- دسکتاپ --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[650px] text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 text-right text-xs text-gray-500">
                            <th class="px-5 py-4 font-bold">نوع تراکنش</th>
                            <th class="px-5 py-4 font-bold">مبلغ</th>
                            <th class="px-5 py-4 font-bold">موجودی بعد</th>
                            <th class="px-5 py-4 font-bold">شرح</th>
                            <th class="whitespace-nowrap px-5 py-4 font-bold">تاریخ</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @foreach($transactions as $tx)
                            <tr class="transition hover:bg-gray-50/70">
                                <td class="whitespace-nowrap px-5 py-4">
                                    @if($tx->type->value === 'credit')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            واریز
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                            برداشت
                                        </span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 font-black {{ $tx->type->value === 'credit' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $tx->type->value === 'credit' ? '+' : '−' }}{{ format_price($tx->amount) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-gray-500">
                                    {{ format_price($tx->balance_after) }}
                                </td>

                                <td class="max-w-xs px-5 py-4 text-gray-500">
                                    <p class="truncate">{{ $tx->description ?: $tx->reference_type->label() }}</p>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-xs text-gray-400">
                                    {{ format_jalali($tx->created_at, 'Y/m/d — H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- موبایل: تایم‌لاین --}}
            <div class="relative space-y-3 p-3 md:hidden">
                <div class="absolute bottom-8 right-[27px] top-8 w-px bg-gray-200"></div>

                @foreach($transactions as $tx)
                    <div class="relative flex min-w-0 gap-3">
                        <span class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center
                            rounded-full border-4 border-white shadow-sm
                            {{ $tx->type->value === 'credit' ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }}">

                            @if($tx->type->value === 'credit')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                            @else
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/>
                                </svg>
                            @endif
                        </span>

                        <div class="min-w-0 flex-1 rounded-2xl border border-gray-100 bg-gray-50/70 p-3.5">
                            <div class="flex min-w-0 flex-wrap items-start justify-between gap-2">
                                <p class="min-w-0 break-words text-xs font-bold text-gray-600">
                                    {{ $tx->reference_type->label() }}
                                </p>
                                <span class="shrink-0 text-[10px] text-gray-400">
                                    {{ format_jalali($tx->created_at, 'Y/m/d') }}
                                </span>
                            </div>

                            <p class="mt-2 break-words text-base font-black {{ $tx->type->value === 'credit' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $tx->type->value === 'credit' ? '+' : '−' }}{{ format_price($tx->amount) }}
                            </p>

                            @if($tx->description)
                                <p class="mt-1 break-words text-xs leading-5 text-gray-400">{{ $tx->description }}</p>
                            @endif

                            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-gray-200/70 pt-3">
                                <span class="text-[11px] text-gray-400">موجودی پس از تراکنش</span>
                                <span class="text-xs font-bold text-gray-600">{{ format_price($tx->balance_after) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($transactions->hasPages())
                <div class="border-t border-gray-100 px-4 py-4 sm:px-6">
                    {{ $transactions->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection