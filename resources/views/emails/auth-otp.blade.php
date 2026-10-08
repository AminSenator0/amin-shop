@extends('emails.layout')

@section('title', ($purpose === 'register' ? 'کد ثبت‌نام' : 'کد ورود'))

@section('content')

    <!-- Badge -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:20px;">
        <tr>
            <td style="background:#f4f4f5; border:1px solid #e4e4e7; border-radius:999px; padding:7px 14px; color:#52525b; font-size:11px; font-weight:700;">
                🔐 کد تأیید {{ $purpose === 'register' ? 'ثبت‌نام' : 'ورود' }}
            </td>
        </tr>
    </table>

    <h1 style="margin:0 0 10px; color:#18181b; font-size:24px; font-weight:800; letter-spacing:-0.5px;">
        {{ $purpose === 'register' ? 'تکمیل ثبت‌نام' : 'ورود به حساب' }}
    </h1>

    <p style="margin:0 0 24px; color:#52525b; font-size:14px; line-height:2;">
        کد تأیید شما در <strong style="color:#111318;">{{ $storeName }}</strong>:
    </p>

    <!-- Code box -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:20px;">
        <tr>
            <td align="center"
                style="background:#111318; border-radius:16px; padding:26px 20px;">
                <div style="color:#a5a8b0; font-size:11px; margin-bottom:10px;">کد تأیید شما</div>
                <div dir="ltr"
                     style="color:#ffffff; font-size:34px; font-weight:800; letter-spacing:10px; font-family:'Courier New',monospace;">
                    {{ $code }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Expiry -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="background:#fafafa; border:1px solid #e4e4e7; border-radius:14px; margin-bottom:18px;">
        <tr>
            <td style="padding:14px 16px; color:#52525b; font-size:13px; line-height:1.9;">
                <strong style="color:#18181b;">⏱ اعتبار کد</strong>
                <br>
                این کد تا <strong style="color:#18181b;">۵ دقیقه</strong> معتبر است. آن را با کسی به اشتراک نگذارید.
            </td>
        </tr>
    </table>

    <!-- Security note -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="background:#fafafa; border-right:3px solid #111318;">
        <tr>
            <td style="padding:13px 15px; color:#71717a; font-size:12px; line-height:2;">
                اگر این درخواست از سمت شما نبوده، این ایمیل را نادیده بگیرید — حساب شما در امان است.
            </td>
        </tr>
    </table>

@endsection