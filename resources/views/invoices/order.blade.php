<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاکتور {{ $order->order_number }}</title>
    <x-favicon />
    @vite(['resources/css/app.css'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            font-family: 'Vazirmatn', sans-serif;
            font-size: 14px;
            line-height: 1.7;
            color: {{ $theme['text'] }};
            background: {{ $theme['background'] }};
            direction: rtl;
        }
        .invoice {
            max-width: 800px;
            margin: 0 auto;
            background: {{ $theme['surface'] }};
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            padding: 32px;
        }
        .toolbar {
            max-width: 800px;
            margin: 0 auto 16px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, {{ $theme['primary'] }} 0%, {{ $theme['accent'] }} 100%);
            color: #fff;
        }
        .btn-primary:hover { opacity: 0.9; }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding-bottom: 20px;
            margin-bottom: 24px;
            border-bottom: 2px solid {{ $theme['primary'] }};
        }
        .brand { display: flex; align-items: center; gap: 16px; }
        .logo { max-height: 56px; max-width: 80px; object-fit: contain; }
        .title { font-size: 22px; font-weight: 700; color: {{ $theme['primary'] }}; margin: 0; }
        .subtitle { font-size: 14px; color: {{ $theme['muted'] }}; margin: 4px 0 0; }
        .invoice-no {
            text-align: left;
            font-size: 13px;
            color: {{ $theme['muted'] }};
        }
        .invoice-no strong {
            display: block;
            font-size: 18px;
            color: {{ $theme['text'] }};
            margin-top: 4px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }
        .meta-box {
            background: {{ $theme['background'] }};
            border-radius: 8px;
            padding: 16px;
        }
        .meta-box h3 {
            margin: 0 0 10px;
            font-size: 13px;
            font-weight: 700;
            color: {{ $theme['primary'] }};
        }
        .meta-box p { margin: 4px 0; font-size: 13px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            border: 1px solid {{ $theme['border'] }};
            padding: 10px 12px;
            text-align: right;
        }
        th {
            background: {{ $theme['background'] }};
            font-weight: 700;
            font-size: 13px;
        }
        td { font-size: 13px; }
        .totals-wrap { display: flex; justify-content: flex-start; margin-top: 20px; }
        .totals { width: 50%; min-width: 260px; }
        .totals td { border: none; padding: 6px 12px; }
        .totals .grand td {
            font-weight: 700;
            font-size: 15px;
            border-top: 2px solid {{ $theme['primary-dark'] }};
            padding-top: 10px;
        }
        .footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid {{ $theme['border'] }};
            text-align: center;
            font-size: 12px;
            color: {{ $theme['muted'] }};
        }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .invoice { box-shadow: none; border-radius: 0; padding: 0; max-width: none; }
        }
        @media (max-width: 640px) {
            body { padding: 12px; }
            .invoice { padding: 20px; }
            .meta-grid { grid-template-columns: 1fr; }
            .header { flex-direction: column; align-items: flex-start; }
            .invoice-no { text-align: right; }
            .totals { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button type="button" class="btn btn-primary" onclick="window.print()">چاپ</button>
        <a href="?format=pdf" class="btn btn-primary">دانلود PDF</a>
    </div>

    <div class="invoice">
        <div class="header">
            <div class="brand">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" class="logo" alt="{{ $storeName }}">
                @endif
                <div>
                    <h1 class="title">{{ $storeName }}</h1>
                    <p class="subtitle">فاکتور فروش</p>
                </div>
            </div>
            <div class="invoice-no">
                شماره سفارش
                <strong>{{ $order->order_number }}</strong>
            </div>
        </div>

        <div class="meta-grid">
            <div class="meta-box">
                <h3>اطلاعات سفارش</h3>
                <p><strong>تاریخ:</strong> {{ format_jalali($order->created_at, 'Y/m/d H:i') }}</p>
                <p><strong>وضعیت:</strong> {{ $order->status->label() }}</p>
                @if($order->payment_ref)
                    <p><strong>کد پیگیری پرداخت:</strong> {{ $order->payment_ref }}</p>
                @endif
            </div>
            <div class="meta-box">
                <h3>مشتری</h3>
                <p>{{ $order->user->name }}</p>
                <p dir="ltr" style="text-align: right;">{{ $order->user->email }}</p>
            </div>
        </div>

        <div class="meta-box" style="margin-bottom: 24px;">
            <h3>آدرس ارسال</h3>
            <p>{{ $order->shipping_address['full_name'] }} — {{ $order->shipping_address['phone'] }}</p>
            <p>{{ $order->shipping_address['province'] }}، {{ $order->shipping_address['city'] }}، {{ $order->shipping_address['address'] }}</p>
            <p>کد پستی: {{ $order->shipping_address['postal_code'] }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ردیف</th>
                    <th>محصول</th>
                    <th>کد</th>
                    <th>قیمت واحد</th>
                    <th>تعداد</th>
                    <th>جمع</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $i => $item)
                    <tr>
                        <td>{{ format_number($i + 1) }}</td>
                        <td>
                            {{ $item->product_name }}
                            @if($item->optionsLabel())
                                <br><small style="color: {{ $theme['muted'] }}">{{ $item->optionsLabel() }}</small>
                            @endif
                        </td>
                        <td dir="ltr">{{ $item->product_sku }}</td>
                        <td>{{ format_price($item->price) }}</td>
                        <td>{{ format_number($item->quantity) }}</td>
                        <td>{{ format_price($item->total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-wrap">
            <table class="totals">
                <tr><td>جمع محصولات</td><td>{{ format_price($order->subtotal) }}</td></tr>
                @if($order->discount_amount > 0)
                    <tr><td>تخفیف ({{ $order->coupon_code }})</td><td>-{{ format_price($order->discount_amount) }}</td></tr>
                @endif
                <tr><td>هزینه ارسال</td><td>{{ format_price($order->shipping_cost) }}</td></tr>
                <tr class="grand"><td>جمع کل</td><td>{{ format_price($order->total) }}</td></tr>
            </table>
        </div>

        <div class="footer">
            {{ $storeName }} — صادر شده در {{ format_jalali(now(), 'Y/m/d H:i') }}
        </div>
    </div>
</body>
</html>
