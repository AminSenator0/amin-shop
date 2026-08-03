@extends('layouts.admin')

@section('header', 'سفارش '.$order->order_number)

@section('content')
<div class="flex flex-wrap items-center gap-3 mb-6">
    <x-admin.status-badge :status="$order->status" class="text-sm px-3 py-1" />
    <x-admin.status-badge :status="$order->payment_status" class="text-sm px-3 py-1" />
    <span class="text-xs text-zinc-500">{{ format_jalali($order->created_at, 'Y/m/d H:i') }}</span>
    @if($order->payment_status->value === 'paid')
        <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" rel="noopener" class="admin-btn-secondary text-xs">
            <span>مشاهده فاکتور</span>
        </a>
        <a href="{{ route('admin.orders.invoice', ['order' => $order, 'format' => 'pdf']) }}" class="admin-btn-secondary text-xs">دانلود PDF</a>
    @endif
    @if($order->canBeCancelledByAdmin())
        <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" data-confirm-message="آیا از لغو این سفارش مطمئن هستید؟">
            @csrf
            <button type="submit" class="admin-btn-secondary text-xs !text-rose-600">لغو سفارش</button>
        </form>
    @endif
</div>

<div class="grid gap-6 lg:grid-cols-3 mb-6">
    <div class="admin-card p-5">
        <h3 class="flex items-center gap-2 text-sm font-bold text-zinc-500 mb-3">
            <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            <span>مشتری</span>
        </h3>
        <p class="font-bold text-zinc-900">
            <a href="{{ route('admin.users.show', $order->user) }}" class="admin-link">{{ $order->user->name }}</a>
        </p>
        <p class="text-sm text-zinc-500 mt-1" dir="ltr">{{ $order->user->email }}</p>
        @if($order->user->phone)
            <p class="text-sm text-zinc-500" dir="ltr">{{ $order->user->phone }}</p>
        @endif
    </div>

    <div class="admin-card p-5">
        <h3 class="flex items-center gap-2 text-sm font-bold text-zinc-500 mb-3">
            <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            <span>آدرس ارسال</span>
        </h3>
        <p class="font-bold text-zinc-900">{{ $order->shipping_address['full_name'] ?? '' }}</p>
        @if($order->shipping_address['phone'] ?? null)
            <p class="text-sm text-zinc-500 mt-1" dir="ltr">{{ $order->shipping_address['phone'] }}</p>
        @endif
        <p class="text-sm text-zinc-500 mt-1">{{ $order->shipping_address['province'] ?? '' }}، {{ $order->shipping_address['city'] ?? '' }}</p>
        <p class="text-sm text-zinc-500">{{ $order->shipping_address['address'] ?? '' }}</p>
        @if($order->shipping_address['postal_code'] ?? null)
            <p class="text-sm text-zinc-500">کد پستی: <span dir="ltr">{{ $order->shipping_address['postal_code'] }}</span></p>
        @endif
        @if($order->shippingMethod)
            <p class="text-sm text-zinc-500 mt-2">روش ارسال: <span class="font-medium text-zinc-700">{{ $order->shippingMethod->name }}</span></p>
        @endif
        @if($order->notes)
            <div class="mt-3 rounded-xl bg-amber-50 border border-amber-200 px-3 py-2">
                <p class="flex items-center gap-2 text-xs font-bold text-amber-700">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
                    <span>یادداشت مشتری</span>
                </p>
                <p class="text-sm text-amber-800 mt-1.5">{{ $order->notes }}</p>
            </div>
        @endif
    </div>

    <div class="admin-card p-5 space-y-4">
        <div>
            <h3 class="flex items-center gap-2 text-sm font-bold text-zinc-500 mb-3">
                <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>زمان‌بندی</span>
            </h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-zinc-500">ثبت سفارش</dt>
                    <dd class="text-zinc-900">{{ format_jalali($order->created_at, 'Y/m/d H:i') }}</dd>
                </div>
                @if($order->paid_at)
                    <div class="flex justify-between gap-3">
                        <dt class="text-zinc-500">پرداخت</dt>
                        <dd class="text-zinc-900">{{ format_jalali($order->paid_at, 'Y/m/d H:i') }}</dd>
                    </div>
                @endif
                @if($order->shipped_at)
                    <div class="flex justify-between gap-3">
                        <dt class="text-zinc-500">ارسال</dt>
                        <dd class="text-zinc-900">{{ format_jalali($order->shipped_at, 'Y/m/d H:i') }}</dd>
                    </div>
                @endif
                @if($order->delivered_at)
                    <div class="flex justify-between gap-3">
                        <dt class="text-zinc-500">تحویل</dt>
                        <dd class="text-zinc-900">{{ format_jalali($order->delivered_at, 'Y/m/d H:i') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        @if($order->payment_ref || $order->coupon_code)
            <div class="border-t border-zinc-100 pt-4">
                <h3 class="flex items-center gap-2 text-sm font-bold text-zinc-500 mb-3">
                    <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                    <span>پرداخت و تخفیف</span>
                </h3>
                @if($order->payment_ref)
                    <p class="text-sm text-zinc-500">کد پیگیری: <span class="font-medium text-zinc-800" dir="ltr">{{ $order->payment_ref }}</span></p>
                @endif
                @if($order->coupon_code)
                    <p class="text-sm text-zinc-500 mt-1">کد تخفیف: <span class="font-medium text-emerald-700">{{ $order->coupon_code }}</span></p>
                @endif
            </div>
        @endif
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3 mb-6">
    <div class="admin-card p-5 lg:col-span-1 space-y-5">
        <div>
            <h3 class="flex items-center gap-2 text-sm font-bold text-zinc-500 mb-3">
                <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                <span>تغییر وضعیت</span>
            </h3>
            <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="space-y-2">
                @csrf @method('PATCH')
                <select name="status" class="admin-select w-full">
                    @foreach(\App\Enums\OrderStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected($order->status === $status)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 text-xs text-zinc-600 cursor-pointer">
                    <input type="checkbox" name="send_sms" value="1" class="rounded border-zinc-300 text-indigo-600" @checked(old('send_sms', true))>
                    <span>ارسال پیامک به مشتری</span>
                </label>
                <label class="flex items-center gap-2 text-xs text-zinc-600 cursor-pointer">
                    <input type="checkbox" name="send_email" value="1" class="rounded border-zinc-300 text-indigo-600" @checked(old('send_email', false))>
                    <span>ارسال ایمیل به مشتری</span>
                </label>
                <button type="submit" class="admin-btn-primary w-full">به‌روزرسانی وضعیت</button>
            </form>
        </div>
        <div>
            <h3 class="flex items-center gap-2 text-sm font-bold text-zinc-500 mb-3">
                <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                <span>کد رهگیری</span>
            </h3>
            <form method="POST" action="{{ route('admin.orders.tracking', $order) }}" class="space-y-2">
                @csrf @method('PATCH')
                <input type="text" name="tracking_code" value="{{ $order->tracking_code }}" placeholder="کد رهگیری پست..." class="admin-input w-full" dir="ltr">
                <label class="flex items-center gap-2 text-xs text-zinc-600 cursor-pointer">
                    <input type="checkbox" name="send_sms" value="1" class="rounded border-zinc-300 text-indigo-600" @checked(old('send_sms', true))>
                    <span>ارسال پیامک با کد رهگیری</span>
                </label>
                <label class="flex items-center gap-2 text-xs text-zinc-600 cursor-pointer">
                    <input type="checkbox" name="send_email" value="1" class="rounded border-zinc-300 text-indigo-600" @checked(old('send_email', false))>
                    <span>ارسال ایمیل با کد رهگیری</span>
                </label>
                <button type="submit" class="admin-btn-secondary w-full">ذخیره کد رهگیری</button>
            </form>
        </div>
        <div>
            <h3 class="flex items-center gap-2 text-sm font-bold text-zinc-500 mb-3">
                <span>وضعیت پرداخت</span>
            </h3>
            <form method="POST" action="{{ route('admin.orders.payment-status', $order) }}" class="space-y-2">
                @csrf @method('PATCH')
                <select name="payment_status" class="admin-select w-full">
                    @foreach(\App\Enums\PaymentStatus::cases() as $paymentStatus)
                        <option value="{{ $paymentStatus->value }}" @selected($order->payment_status === $paymentStatus)>{{ $paymentStatus->label() }}</option>
                    @endforeach
                </select>
                <input type="text" name="payment_note" placeholder="یادداشت (اختیاری)..." class="admin-input w-full text-sm">
                <label class="flex items-center gap-2 text-xs text-zinc-600 cursor-pointer">
                    <input type="checkbox" name="send_email" value="1" class="rounded border-zinc-300 text-indigo-600">
                    <span>اطلاع‌رسانی ایمیل</span>
                </label>
                <button type="submit" class="admin-btn-secondary w-full">به‌روزرسانی پرداخت</button>
            </form>
        </div>
    </div>

    <div class="admin-card lg:col-span-2">
        <div class="admin-card-header">
            <h2 class="admin-card-title flex items-center gap-2">
                <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                <span>اقلام سفارش</span>
            </h2>
            <span class="text-sm font-bold text-zinc-900">{{ format_price($order->total) }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>محصول</th>
                        <th>SKU</th>
                        <th>قیمت</th>
                        <th>تعداد</th>
                        <th>جمع</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>
                                <x-order-item-name
                                    :item="$item"
                                    :link="$item->product ? route('admin.products.edit', $item->product) : null"
                                    link-class="admin-link font-bold text-zinc-900"
                                />
                                {{-- ═══ فیلدهای سفارشی ═══ --}}
                                @if(!empty($item->custom_fields))
                                    <div class="mt-1.5 space-y-0.5 text-xs text-zinc-500">
                                        @foreach($item->custom_fields as $cf)
                                            <div>
                                                <span class="font-medium text-zinc-600">{{ $cf['label'] ?? 'فیلد سفارشی' }}:</span>
                                                <span>{{ $cf['value'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                {{-- ═══ پایان فیلدهای سفارشی ═══ --}}
                            </td>
                            <td class="text-zinc-500 text-xs" dir="ltr">{{ $item->product_sku ?: '—' }}</td>
                            <td>{{ format_price($item->price) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td class="font-bold">{{ format_price($item->total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-zinc-100 bg-zinc-50/50">
                    <tr><td colspan="4" class="text-zinc-500">جمع محصولات</td><td class="font-bold">{{ format_price($order->subtotal) }}</td></tr>
                    <tr><td colspan="4" class="text-zinc-500">هزینه ارسال</td><td>{{ format_price($order->shipping_cost) }}</td></tr>
                    @if($order->discount_amount > 0)
                        <tr><td colspan="4" class="text-zinc-500">تخفیف @if($order->coupon_code)({{ $order->coupon_code }})@endif</td><td class="text-emerald-600">-{{ format_price($order->discount_amount) }}</td></tr>
                    @endif
                    <tr><td colspan="4" class="font-black text-zinc-900">مبلغ نهایی</td><td class="font-black text-zinc-900">{{ format_price($order->total) }}</td></tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-1">
        <div class="admin-card p-5">
            <h3 class="flex items-center gap-2 text-sm font-bold text-zinc-500 mb-3">
                <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                <span>یادداشت داخلی</span>
            </h3>
            <form method="POST" action="{{ route('admin.orders.internal-notes', $order) }}" class="space-y-2">
                @csrf @method('PATCH')
                <textarea name="internal_notes" rows="4" placeholder="یادداشت فقط برای تیم..." class="admin-input w-full resize-none">{{ old('internal_notes', $order->internal_notes) }}</textarea>
                <button type="submit" class="admin-btn-secondary w-full">ذخیره یادداشت</button>
            </form>
        </div>
    </div>

    <div class="admin-card p-5 lg:col-span-2">
        <h3 class="flex items-center gap-2 text-sm font-bold text-zinc-500 mb-3">
            <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
            <span>ثبت مرجوعی</span>
        </h3>
        @if($order->returns->isNotEmpty())
            <div class="space-y-3 mb-4">
                @foreach($order->returns as $return)
                    <div class="rounded-xl border border-zinc-200 p-3">
                        <div class="flex items-center justify-between mb-2">
                            <x-admin.status-badge :status="$return->status" />
                            <span class="text-xs text-zinc-500">{{ format_jalali($return->created_at) }}</span>
                        </div>
                        <p class="text-sm text-zinc-700">{{ $return->reason }}</p>
                        @if($return->status === \App\Enums\ReturnStatus::Pending)
                            <form method="POST" action="{{ route('admin.returns.status', $return) }}" class="mt-3 space-y-2">
                                @csrf @method('PATCH')
                                <select name="status" class="admin-select w-full text-xs">
                                    <option value="approved">تأیید</option>
                                    <option value="rejected">رد</option>
                                    <option value="refunded">بازگشت وجه</option>
                                </select>
                                <input type="text" name="admin_note" placeholder="یادداشت مدیر..." class="admin-input w-full text-xs">
                                <button type="submit" class="admin-btn-primary w-full text-xs py-2">ثبت تصمیم</button>
                            </form>
                        @elseif($return->admin_note)
                            <p class="text-xs text-zinc-500 mt-2">{{ $return->admin_note }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if(!$order->returns->whereIn('status', [\App\Enums\ReturnStatus::Pending, \App\Enums\ReturnStatus::Approved])->count())
            <form method="POST" action="{{ route('admin.returns.store', $order) }}" class="space-y-2">
                @csrf
                <textarea name="reason" rows="3" placeholder="دلیل مرجوعی..." class="admin-input w-full resize-none text-sm" required></textarea>
                <input type="text" name="refund_amount" value="{{ old('refund_amount', format_number($order->total, false)) }}" data-price-input placeholder="مبلغ استرداد" class="admin-input w-full text-sm" dir="ltr">
                <button type="submit" class="admin-btn-secondary w-full">ثبت درخواست مرجوعی</button>
            </form>
        @endif
    </div>
</div>

@if($order->activities->isNotEmpty())
    <div class="admin-card p-5 mt-6">
        <h3 class="text-sm font-bold text-zinc-500 mb-4">تاریخچه تغییرات</h3>
        <div class="space-y-3">
            @foreach($order->activities as $activity)
                <div class="flex flex-wrap items-start justify-between gap-2 border-b border-zinc-100 pb-3 last:border-0 last:pb-0">
                    <div>
                        <p class="text-sm text-zinc-800">{{ $activity->description }}</p>
                        @if($activity->user)
                            <p class="text-xs text-zinc-400 mt-1">توسط {{ $activity->user->name }}</p>
                        @endif
                    </div>
                    <span class="text-xs text-zinc-400">{{ format_jalali($activity->created_at, 'Y/m/d H:i') }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endif
@endsection