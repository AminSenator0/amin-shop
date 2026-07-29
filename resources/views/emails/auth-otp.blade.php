<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Tahoma, sans-serif; line-height: 1.8; color: {{ $theme['text'] }}; background: {{ $theme['background'] }};">
    <p>سلام،</p>
    <p>
        کد
        {{ $purpose === 'register' ? 'ثبت‌نام' : 'ورود' }}
        شما در <strong>{{ $storeName }}</strong>:
    </p>
    <p style="font-size: 28px; font-weight: bold; letter-spacing: 6px; text-align: center; direction: ltr; margin: 24px 0;">
        {{ $code }}
    </p>
    <p style="color: {{ $theme['muted'] }}; font-size: 13px;">این کد تا ۵ دقیقه معتبر است. اگر این درخواست از سمت شما نبوده، این پیام را نادیده بگیرید.</p>
</body>
</html>
