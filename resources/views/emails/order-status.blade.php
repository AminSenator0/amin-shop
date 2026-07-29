<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Tahoma, sans-serif; line-height: 1.8; color: {{ $theme['text'] }}; background: {{ $theme['background'] }};">
    <p>سلام {{ $order->user->name }}،</p>
    <p>وضعیت سفارش <strong dir="ltr">{{ $order->order_number }}</strong> به‌روزرسانی شد:</p>
    <div style="background: {{ $theme['background'] }}; border: 1px solid {{ $theme['border'] }}; padding: 16px; border-radius: 8px; margin: 16px 0; white-space: pre-line;">{{ $statusMessage }}</div>
    @if($order->tracking_code)
        <p><strong>کد رهگیری:</strong> <span dir="ltr">{{ $order->tracking_code }}</span></p>
    @endif
    <p style="color: {{ $theme['muted'] }}; font-size: 13px;">جزئیات بیشتر در پنل کاربری شما قابل مشاهده است.</p>
</body>
</html>
