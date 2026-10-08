@extends('emails.layout')

@section('title', 'به‌روزرسانی سفارش ' . $order->order_number)

@section('content')

    <!-- Badge -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:20px;">
        <tr>
            <td style="background:#f4f4f5; border:1px solid #e4e4e7; border-radius:999px; padding:7px 14px; color:#52525b; font-size:11px; font-weight:700;">
                📦 به‌روزرسانی وضعیت سفارش
            </td>
        </tr>
    </table>

    <h1 style="margin:0 0 10px; color:#18181b; font-size:24px; font-weight:800; letter-spacing:-0.5px;">وضعیت سفارش شما</h1>

    <p style="margin:0 0 20px; color:#27272a; font-size:15px; line-height:2;">
        سلام <strong style="color:#111318;">{{ $order->user->name }}</strong>،
        <br>
        <span style="color:#52525b; font-size:14px;">وضعیت سفارش شما به‌روزرسانی شد:</span>
    </p>

    <!-- Order + Status -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="background:#fafafa; border:1px solid #e4e4e7; border-radius:14px; margin-bottom:18px;">
        <tr>
            <td style="padding:14px 16px;">
                <span style="color:#71717a; font-size:11px;">شماره سفارش</span><br>
                <strong dir="ltr" style="font-size:15px;">{{ $order->order_number }}</strong>
            </td>
            <td style="padding:14px 16px; text-align:left; vertical-align:middle;">
                <span style="display:inline-block; background:#111318; color:#ffffff; padding:7px 16px; border-radius:999px; font-size:12px; font-weight:700;">
                    {{ $statusMessage }}
                </span>
            </td>
        </tr>
    </table>

    @if($order->tracking_code)
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
               style="background:#fafafa; border-right:3px solid #111318; margin-bottom:18px;">
            <tr>
                <td style="padding:13px 16px; color:#52525b; font-size:13px; line-height:1.9;">
                    <strong style="color:#18181b;">🚚 کد رهگیری مرسوله:</strong>
                    <span dir="ltr" style="letter-spacing:1px;">{{ $order->tracking_code }}</span>
                </td>
            </tr>
        </table>
    @endif

    <!-- CTA -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:16px;">
        <tr>
            <td align="center">
                <a href="{{ route('user.orders.show', $order) }}"
                   style="display:block; width:100%; box-sizing:border-box; background:#111318; color:#ffffff; text-decoration:none; padding:16px 22px; border-radius:13px; font-size:14px; font-weight:700; line-height:1.5;">
                    مشاهده جزئیات سفارش
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0; color:#71717a; font-size:12px; line-height:2; text-align:center;">
        اگر سؤالی دارید، از بخش «تماس با ما» در فروشگاه با ما در ارتباط باشید.
    </p>

@endsection