@extends('emails.layout')

@section('title', 'پاسخ به پیام شما')

@section('content')

    <!-- Badge -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:20px;">
        <tr>
            <td style="background:#f0f9ff; border:1px solid #bae6fd; border-radius:999px; padding:7px 14px; color:#0369a1; font-size:11px; font-weight:700;">
                💬 پاسخ تیم پشتیبانی
            </td>
        </tr>
    </table>

    <h1 style="margin:0 0 10px; color:#18181b; font-size:24px; font-weight:800; letter-spacing:-0.5px;">پیام شما پاسخ داده شد</h1>

    <p style="margin:0 0 20px; color:#27272a; font-size:15px; line-height:2;">
        سلام،
        <br>
        <span style="color:#52525b; font-size:14px;">به پیام شما با موضوع «<strong style="color:#111318;">{{ $contactMessage->subject }}</strong>» پاسخ داده‌ایم:</span>
    </p>

    <!-- Reply body -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="background:#fafafa; border:1px solid #e4e4e7; border-radius:14px; margin-bottom:20px;">
        <tr>
            <td style="padding:18px; color:#27272a; font-size:14px; line-height:2.1; white-space:pre-line;">{{ $body }}</td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center">
                <a href="{{ route('pages.contact') }}"
                   style="display:block; width:100%; box-sizing:border-box; background:#111318; color:#ffffff; text-decoration:none; padding:16px 22px; border-radius:13px; font-size:14px; font-weight:700; line-height:1.5;">
                    ارسال پیام جدید
                </a>
            </td>
        </tr>
    </table>

@endsection