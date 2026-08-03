<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">

    <title>بازیابی رمز عبور</title>

    <style>
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 20px 12px !important;
            }

            .email-container {
                width: 100% !important;
                border-radius: 20px !important;
            }

            .header {
                padding: 34px 22px 30px !important;
            }

            .content {
                padding: 30px 22px !important;
            }

            .footer {
                padding: 22px 18px !important;
            }

            .button-wrapper {
                width: 100% !important;
            }

            .button {
                display: block !important;
                width: auto !important;
                padding: 15px 20px !important;
                text-align: center !important;
            }

            .reset-url {
                font-size: 10px !important;
            }
        }
    </style>
</head>

<body style="
    margin:0;
    padding:0;
    background:#f4f5f7;
    font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
    direction:rtl;
    text-align:right;
">

<table role="presentation"
       width="100%"
       cellspacing="0"
       cellpadding="0"
       border="0"
       style="background:#f4f5f7;">

    <tr>
        <td class="email-wrapper"
            align="center"
            style="padding:48px 16px;">

            <!-- Main Card -->
            <table role="presentation"
                   class="email-container"
                   width="520"
                   cellspacing="0"
                   cellpadding="0"
                   border="0"
                   style="
                        width:520px;
                        max-width:520px;
                        background:#ffffff;
                        border-radius:24px;
                        overflow:hidden;
                        border:1px solid #e8e9ec;
                        box-shadow:0 18px 45px rgba(15,23,42,0.08);
                   ">

                <!-- ================= HEADER ================= -->

                <tr>
                    <td class="header"
                        align="center"
                        style="
                            padding:40px 30px 36px;
                            background:#111318;
                        ">

                        <!-- Logo -->
                        <table role="presentation"
                               cellspacing="0"
                               cellpadding="0"
                               border="0"
                               align="center">

                            <tr>
                                <td align="center"
                                    style="
                                        width:58px;
                                        height:58px;
                                        background:#ffffff;
                                        border-radius:17px;
                                        font-size:26px;
                                        font-weight:700;
                                        color:#111318;
                                    ">

                                    {{ mb_substr(config('app.name'), 0, 1) }}

                                </td>
                            </tr>

                        </table>

                        <div style="
                            height:16px;
                            line-height:16px;
                            font-size:1px;
                        ">&nbsp;</div>

                        <!-- Brand -->
                        <div style="
                            color:#ffffff;
                            font-size:21px;
                            font-weight:800;
                            letter-spacing:-0.4px;
                        ">
                            {{ config('app.name') }}
                        </div>

                        <div style="
                            margin-top:7px;
                            color:#a5a8b0;
                            font-size:13px;
                        ">
                            فروشگاه آنلاین
                        </div>

                    </td>
                </tr>


                <!-- ================= BODY ================= -->

                <tr>
                    <td class="content"
                        style="padding:38px 38px 34px;">

                        <!-- Security Badge -->

                        <table role="presentation"
                               cellspacing="0"
                               cellpadding="0"
                               border="0"
                               style="margin-bottom:22px;">

                            <tr>

                                <td style="
                                    background:#f4f4f5;
                                    border:1px solid #e4e4e7;
                                    border-radius:999px;
                                    padding:7px 12px;
                                    color:#52525b;
                                    font-size:11px;
                                    font-weight:600;
                                ">

                                    🔐 درخواست بازیابی رمز

                                </td>

                            </tr>

                        </table>


                        <!-- Title -->

                        <h1 style="
                            margin:0 0 12px;
                            color:#18181b;
                            font-size:25px;
                            line-height:1.5;
                            font-weight:800;
                            letter-spacing:-0.5px;
                        ">

                            بازیابی رمز عبور

                        </h1>


                        <!-- Greeting -->

                        <p style="
                            margin:0 0 20px;
                            color:#27272a;
                            font-size:15px;
                            line-height:2;
                        ">

                            سلام
                            <strong style="color:#111318;">
                                {{ $user->name ?? 'کاربر عزیز' }}
                            </strong>
                            👋

                        </p>


                        <!-- Description -->

                        <p style="
                            margin:0 0 25px;
                            color:#52525b;
                            font-size:14px;
                            line-height:2.05;
                        ">

                            درخواست بازیابی رمز عبور برای حساب کاربری شما دریافت شده است.
                            برای انتخاب یک رمز عبور جدید، روی دکمه زیر کلیک کنید.

                        </p>


                        <!-- ================= CTA ================= -->

                        <table role="presentation"
                               width="100%"
                               cellspacing="0"
                               cellpadding="0"
                               border="0"
                               style="margin:0 0 25px;">

                            <tr>

                                <td align="center">

                                    <a href="{{ $resetUrl }}"
                                       class="button"
                                       style="
                                            display:block;
                                            width:100%;
                                            box-sizing:border-box;
                                            background:#111318;
                                            color:#ffffff;
                                            text-decoration:none;
                                            padding:16px 22px;
                                            border-radius:13px;
                                            font-size:14px;
                                            font-weight:700;
                                            line-height:1.5;
                                       ">

                                        🔑 &nbsp; ایجاد رمز عبور جدید

                                    </a>

                                </td>

                            </tr>

                        </table>


                        <!-- Expiration -->

                        <table role="presentation"
                               width="100%"
                               cellspacing="0"
                               cellpadding="0"
                               border="0"
                               style="
                                    background:#fafafa;
                                    border:1px solid #e4e4e7;
                                    border-radius:14px;
                                    margin-bottom:18px;
                               ">

                            <tr>

                                <td style="
                                    padding:15px 16px;
                                    color:#52525b;
                                    font-size:13px;
                                    line-height:1.8;
                                ">

                                    <strong style="color:#18181b;">
                                        ⏱ اعتبار لینک
                                    </strong>

                                    <br>

                                    این لینک تا
                                    <strong style="color:#18181b;">
                                        {{ $expireMinutes }} دقیقه
                                    </strong>
                                    معتبر است.

                                </td>

                            </tr>

                        </table>


                        <!-- Security Notice -->

                        <table role="presentation"
                               width="100%"
                               cellspacing="0"
                               cellpadding="0"
                               border="0"
                               style="
                                    background:#fafafa;
                                    border-right:3px solid #18181b;
                                    margin-bottom:28px;
                               ">

                            <tr>

                                <td style="
                                    padding:13px 15px;
                                    color:#71717a;
                                    font-size:12px;
                                    line-height:2;
                                ">

                                    اگر شما درخواست بازیابی رمز عبور را ثبت نکرده‌اید،
                                    می‌توانید این ایمیل را نادیده بگیرید.
                                    رمز عبور فعلی شما بدون اقدام شما تغییر نخواهد کرد.

                                </td>

                            </tr>

                        </table>


                        <!-- Divider -->

                        <table role="presentation"
                               width="100%"
                               cellspacing="0"
                               cellpadding="0"
                               border="0"
                               style="margin:0 0 22px;">

                            <tr>

                                <td style="
                                    height:1px;
                                    background:#eeeeef;
                                    font-size:1px;
                                    line-height:1px;
                                ">
                                    &nbsp;
                                </td>

                            </tr>

                        </table>


                        <!-- Fallback URL -->

                        <p style="
                            margin:0 0 9px;
                            color:#71717a;
                            font-size:11px;
                            line-height:1.8;
                            font-weight:600;
                        ">

                            اگر دکمه بالا برای شما کار نمی‌کند:

                        </p>

                        <p style="
                            margin:0;
                            padding:12px;
                            background:#f7f7f8;
                            border:1px solid #e4e4e7;
                            border-radius:10px;
                            direction:ltr;
                            text-align:left;
                            word-break:break-all;
                        ">

                            <a href="{{ $resetUrl }}"
                               class="reset-url"
                               style="
                                    color:#52525b;
                                    font-size:10px;
                                    line-height:1.7;
                                    text-decoration:none;
                               ">

                                {{ $resetUrl }}

                            </a>

                        </p>

                    </td>
                </tr>


                <!-- ================= FOOTER ================= -->

                <tr>

                    <td class="footer"
                        align="center"
                        style="
                            padding:25px 30px;
                            background:#fafafa;
                            border-top:1px solid #eeeeef;
                        ">

                        <div style="
                            color:#52525b;
                            font-size:12px;
                            font-weight:600;
                            margin-bottom:7px;
                        ">

                            با احترام،
                            تیم {{ config('app.name') }}

                        </div>

                        <div style="
                            color:#a1a1aa;
                            font-size:11px;
                            line-height:1.8;
                        ">

                            این ایمیل به صورت خودکار ارسال شده است.
                            <br>

                            © {{ date('Y') }}
                            {{ config('app.name') }}
                            — تمامی حقوق محفوظ است.

                        </div>

                    </td>

                </tr>

            </table>

        </td>
    </tr>

</table>

</body>
</html>