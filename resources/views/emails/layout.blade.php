<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title>@yield('title', \App\Support\StoreSettings::get('store_name', config('app.name')))</title>

    <style>
        @media only screen and (max-width: 600px) {
            .email-wrapper { padding: 20px 12px !important; }
            .email-container { width: 100% !important; border-radius: 20px !important; }
            .header { padding: 30px 20px 26px !important; }
            .content { padding: 26px 20px !important; }
            .footer { padding: 20px 16px !important; }
            .button { display: block !important; width: auto !important; }
            .hide-mobile { display: none !important; }
        }
    </style>
</head>

<body style="margin:0; padding:0; background:#f4f5f7; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; direction:rtl; text-align:right;">

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f5f7;">
    <tr>
        <td class="email-wrapper" align="center" style="padding:48px 16px;">

            <table role="presentation" class="email-container" width="520" cellspacing="0" cellpadding="0" border="0"
                   style="width:520px; max-width:520px; background:#ffffff; border-radius:24px; overflow:hidden; border:1px solid #e8e9ec; box-shadow:0 18px 45px rgba(15,23,42,0.08);">

                <!-- ===== HEADER ===== -->
                <tr>
                    <td class="header" align="center" style="padding:36px 30px 32px; background:#111318;">

                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                            <tr>
                                <td align="center"
                                    style="width:58px; height:58px; background:#ffffff; border-radius:17px; font-size:26px; font-weight:700; color:#111318;">
                                    {{ mb_substr(\App\Support\StoreSettings::get('store_name', config('app.name')), 0, 1) }}
                                </td>
                            </tr>
                        </table>

                        <div style="height:14px; line-height:14px; font-size:1px;">&nbsp;</div>

                        <div style="color:#ffffff; font-size:21px; font-weight:800; letter-spacing:-0.4px;">
                            {{ \App\Support\StoreSettings::get('store_name', config('app.name')) }}
                        </div>

                        <div style="margin-top:6px; color:#a5a8b0; font-size:13px;">فروشگاه آنلاین</div>

                    </td>
                </tr>

                <!-- ===== BODY ===== -->
                <tr>
                    <td class="content" style="padding:36px 38px 32px;">
                        @yield('content')
                    </td>
                </tr>

                <!-- ===== FOOTER ===== -->
                <tr>
                    <td class="footer" align="center" style="padding:24px 30px; background:#fafafa; border-top:1px solid #eeeeef;">
                        <div style="color:#52525b; font-size:12px; font-weight:600; margin-bottom:6px;">
                            با احترام، تیم {{ \App\Support\StoreSettings::get('store_name', config('app.name')) }}
                        </div>
                        <div style="color:#a1a1aa; font-size:11px; line-height:1.8;">
                            این ایمیل به صورت خودکار ارسال شده است.
                            <br>
                            © {{ date('Y') }} {{ \App\Support\StoreSettings::get('store_name', config('app.name')) }} — تمامی حقوق محفوظ است.
                        </div>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>