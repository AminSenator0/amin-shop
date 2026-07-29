<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Tahoma, sans-serif; line-height: 1.8; color: {{ $theme['text'] }}; background: {{ $theme['background'] }};">
    <p>سلام {{ $order->user->name }}،</p>
    <p>سفارش شما با شماره <strong dir="ltr">{{ $order->order_number }}</strong> ثبت شد.</p>
    <div style="background: {{ $theme['surface'] }}; border: 1px solid {{ $theme['border'] }}; padding: 16px; border-radius: 8px; margin: 16px 0;">
        <p><strong>مبلغ قابل پرداخت:</strong> {{ format_price($order->total) }}</p>
        <p><strong>تعداد اقلام:</strong> {{ format_number($order->items->sum('quantity')) }}</p>
        @if($order->shippingMethod)
            <p><strong>روش ارسال:</strong> {{ $order->shippingMethod->name }}</p>
        @endif
    </div>
    <p style="color: {{ $theme['muted'] }}; font-size: 13px;">می‌توانید وضعیت سفارش را از پنل کاربری پیگیری کنید.</p>
</body>
</html>
