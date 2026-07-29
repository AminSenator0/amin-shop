<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Tahoma, sans-serif; line-height: 1.8; color: {{ $theme['text'] }}; background: {{ $theme['background'] }};">
    <p>سلام {{ $contactMessage->name }}،</p>
    <p>پاسخ پشتیبانی به پیام شما با موضوع «{{ $contactMessage->subject }}»:</p>
    <div style="background: {{ $theme['background'] }}; border: 1px solid {{ $theme['border'] }}; padding: 16px; border-radius: 8px; margin: 16px 0; white-space: pre-line;">{{ $replyBody }}</div>
    <p style="color: {{ $theme['muted'] }}; font-size: 13px;">می‌توانید در پنل کاربری خود نیز این گفتگو را مشاهده کنید.</p>
</body>
</html>
