@extends('emails.layout')

@section('title', 'تأیید سفارش ' . $order->order_number)

@section('content')

    <!-- Badge -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:20px;">
        <tr>
            <td style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:999px; padding:7px 14px; color:#15803d; font-size:11px; font-weight:700;">
                ✓ سفارش شما با موفقیت ثبت شد
            </td>
        </tr>
    </table>

    <h1 style="margin:0 0 10px; color:#18181b; font-size:24px; font-weight:800; letter-spacing:-0.5px;">تأیید سفارش</h1>

    <p style="margin:0 0 20px; color:#27272a; font-size:15px; line-height:2;">
        سلام <strong style="color:#111318;">{{ $order->user->name }}</strong> 👋
        <br>
        <span style="color:#52525b; font-size:14px;">از خرید شما سپاسگزاریم! جزئیات سفارش:</span>
    </p>

    <!-- Order meta -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="background:#fafafa; border:1px solid #e4e4e7; border-radius:14px; margin-bottom:18px;">
        <tr>
            <td style="padding:14px 16px;">
                <span style="color:#71717a; font-size:11px;">شماره سفارش</span><br>
                <strong dir="ltr" style="font-size:15px; letter-spacing:.5px;">{{ $order->order_number }}</strong>
            </td>
            <td style="padding:14px 16px; text-align:left;">
                <span style="color:#71717a; font-size:11px;">تاریخ ثبت</span><br>
                <strong style="font-size:13px;">{{ $order->created_at->format('Y/m/d — H:i') }}</strong>
            </td>
        </tr>
    </table>

    <!-- Items -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="border:1px solid #e4e4e7; border-radius:14px; margin-bottom:18px;">
        <tr>
            <td style="padding:11px 16px; background:#f4f4f5; color:#71717a; font-size:11px; font-weight:700; border-radius:14px 14px 0 0;">محصول</td>
            <td class="hide-mobile" style="padding:11px 16px; background:#f4f4f5; color:#71717a; font-size:11px; font-weight:700;">تعداد</td>
            <td style="padding:11px 16px; background:#f4f4f5; color:#71717a; font-size:11px; font-weight:700; text-align:left; border-radius:14px 14px 0 0;">مبلغ</td>
        </tr>
        @foreach($order->items as $item)
            <tr>
                <td style="padding:12px 16px; border-top:1px solid #eeeeef; font-size:13px; color:#27272a;">{{ $item->product_name }}</td>
                <td class="hide-mobile" style="padding:12px 16px; border-top:1px solid #eeeeef; font-size:13px; color:#52525b; text-align:center;">{{ $item->quantity }}</td>
                <td style="padding:12px 16px; border-top:1px solid #eeeeef; font-size:13px; color:#27272a; text-align:left; white-space:nowrap;">{{ format_price($item->total) }}</td>
            </tr>
        @endforeach
    </table>

    <!-- Totals -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:22px;">
        <tr>
            <td style="padding:4px 2px; color:#71717a; font-size:13px;">جمع اقلام</td>
            <td style="padding:4px 2px; text-align:left; font-size:13px; color:#27272a;">{{ format_price($order->subtotal) }}</td>
        </tr>
        @if($order->discount_amount > 0)
            <tr>
                <td style="padding:4px 2px; color:#15803d; font-size:13px;">تخفیف</td>
                <td style="padding:4px 2px; text-align:left; font-size:13px; color:#15803d;">− {{ format_price($order->discount_amount) }}</td>
            </tr>
        @endif
        @if($order->shipping_cost > 0)
            <tr>
                <td style="padding:4px 2px; color:#71717a; font-size:13px;">هزینه ارسال</td>
                <td style="padding:4px 2px; text-align:left; font-size:13px; color:#27272a;">{{ format_price($order->shipping_cost) }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding:12px 2px 0; border-top:1px dashed #e4e4e7; font-weight:800; font-size:14px; color:#18181b;">مبلغ نهایی</td>
            <td style="padding:12px 2px 0; border-top:1px dashed #e4e4e7; text-align:left; font-weight:800; font-size:17px; color:#111318;">{{ format_price($order->total) }}</td>
        </tr>
    </table>

    <!-- CTA -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:16px;">
        <tr>
            <td align="center">
                <a href="{{ route('user.orders.show', $order) }}"
                   style="display:block; width:100%; box-sizing:border-box; background:#111318; color:#ffffff; text-decoration:none; padding:16px 22px; border-radius:13px; font-size:14px; font-weight:700; line-height:1.5;">
                    پیگیری سفارش
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0; color:#71717a; font-size:12px; line-height:2; text-align:center;">
        @if($order->payment_status === \App\Enums\PaymentStatus::Paid)
            ✅ پرداخت شما با موفقیت انجام شده است.
        @else
            ⏳ لطفاً از پنل کاربری نسبت به تکمیل پرداخت اقدام کنید.
        @endif
    </p>

@endsection