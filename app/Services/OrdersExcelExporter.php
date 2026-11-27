<?php

namespace App\Services;

use App\Models\Order;
use App\Support\StoreSettings;
use App\Support\Xlsx\XlsxWriter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * خروجی اکسل حرفه‌ای سفارشات.
 *
 * یک فایل XLSX راست‌به‌چپ با تیتر، هدر رنگی، ردیف‌های یکی‌درمیان،
 * سطر جمع کل و فیلتر خودکار تولید می‌کند. بدون هیچ وابستگی خارجی.
 */
class OrdersExcelExporter
{
    /** سقف تعداد سفارش در هر خروجی — جلوگیری از فشار حافظتی روی سرور. */
    private const MAX_ROWS = 10000;

    /** عرض ستون‌ها (ترتیب با HEADINGS یکی است). */
    private const COLUMN_WIDTHS = [
        7,   // ردیف
        20,  // شماره سفارش
        17,  // تاریخ ثبت
        20,  // نام مشتری
        15,  // موبایل
        12,  // استان
        12,  // شهر
        38,  // آدرس
        13,  // کد پستی
        46,  // محصولات
        9,   // تعداد اقلام
        14,  // مبلغ کالاها
        12,  // هزینه ارسال
        12,  // تخفیف
        12,  // کیف پول
        15,  // مبلغ نهایی
        17,  // روش پرداخت
        13,  // وضعیت پرداخت
        13,  // وضعیت سفارش
        11,  // نوع فروش
        15,  // روش ارسال
        17,  // کد رهگیری
        12,  // کد تخفیف
        20,  // شناسه پرداخت
        26,  // یادداشت مشتری
    ];

    private const HEADINGS = [
        'ردیف',
        'شماره سفارش',
        'تاریخ ثبت',
        'نام مشتری',
        'موبایل',
        'استان',
        'شهر',
        'آدرس',
        'کد پستی',
        'محصولات',
        'تعداد اقلام',
        'مبلغ کالاها (تومان)',
        'هزینه ارسال (تومان)',
        'تخفیف (تومان)',
        'پرداخت از کیف پول (تومان)',
        'مبلغ نهایی (تومان)',
        'روش پرداخت',
        'وضعیت پرداخت',
        'وضعیت سفارش',
        'نوع فروش',
        'روش ارسال',
        'کد رهگیری',
        'کد تخفیف',
        'شناسه پرداخت',
        'یادداشت مشتری',
    ];

    private const PAYMENT_METHOD_LABELS = [
        'online' => 'آنلاین (زرین‌پال)',
        'zarinpal' => 'آنلاین (زرین‌پال)',
        'c2c' => 'کارت به کارت',
        'wallet' => 'کیف پول',
        'cod' => 'پرداخت در محل',
    ];

    /**
     * ساخت فایل اکسل از کوئری سفارشات و برگرداندن مسیر فایل.
     *
     * @return array{path: string, count: int, truncated: bool}
     */
    public function generate(Builder $query, Request $request): array
    {
        $query->with(['items', 'shippingMethod']);

        $writer = new XlsxWriter();
        $storeName = (string) StoreSettings::get('store_name', config('app.name'));

        $writer->setRightToLeft(true)
            ->setCreator($storeName)
            ->setTitle('گزارش سفارشات')
            ->setColumnWidths(self::COLUMN_WIDTHS)
            ->freezeRows(3)
            ->setAutoFilterHeaderRow(3);

        $columnCount = count(self::HEADINGS);

        // --- سطر تیتر و زیرتیتر ---
        $titleRow = $writer->addRow(
            array_merge(["گزارش سفارشات — {$storeName}"], array_fill(0, $columnCount - 1, null)),
            'title',
            28
        );
        $writer->mergeRow($titleRow, 1, $columnCount);

        $subtitleRow = $writer->addRow(
            array_merge([$this->buildSubtitle($request)], array_fill(0, $columnCount - 1, null)),
            'subtitle',
            20
        );
        $writer->mergeRow($subtitleRow, 1, $columnCount);

        // --- سطر هدر ---
        $writer->addRow(self::HEADINGS, 'header', 32);

        // --- سطرهای داده ---
        $totals = [
            'items' => 0,
            'subtotal' => 0,
            'shipping' => 0,
            'discount' => 0,
            'wallet' => 0,
            'total' => 0,
        ];

        $count = 0;
        $truncated = false;

        $query->chunk(200, function ($orders) use ($writer, &$totals, &$count, &$truncated) {
            foreach ($orders as $order) {
                if ($count >= self::MAX_ROWS) {
                    $truncated = true;

                    return false;
                }

                $count++;
                $zebra = $count % 2 === 0;

                $row = $this->mapOrderToRow($order, $count);
                $writer->addRow($row, 'text', null, $zebra);

                $totals['items'] += $order->items->sum('quantity');
                $totals['subtotal'] += (int) $order->subtotal;
                $totals['shipping'] += (int) $order->shipping_cost;
                $totals['discount'] += (int) $order->discount_amount + (int) ($order->c2c_discount ?? 0);
                $totals['wallet'] += (int) ($order->wallet_amount ?? 0);
                $totals['total'] += (int) $order->total;
            }

            return true;
        });

        // --- سطر جمع کل ---
        if ($count > 0) {
            $writer->addRow([
                '',
                ['v' => "جمع کل ({$this->persianNumber($count)} سفارش)", 's' => 'total'],
                '', '', '', '', '', '', '', '',
                ['v' => $totals['items'], 's' => 'total_number'],
                ['v' => $totals['subtotal'], 's' => 'total_number'],
                ['v' => $totals['shipping'], 's' => 'total_number'],
                ['v' => $totals['discount'], 's' => 'total_number'],
                ['v' => $totals['wallet'], 's' => 'total_number'],
                ['v' => $totals['total'], 's' => 'total_number'],
                ['v' => $truncated ? 'خروجی به سقف '.self::MAX_ROWS.' رکورد محدود شد' : '', 's' => 'total'],
                ...array_fill(0, $columnCount - 17, ''),
            ], 'total', 22);
        }

        $path = Storage::disk('local')->path('exports/orders-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(4)).'.xlsx');
        $writer->save($path);

        return ['path' => $path, 'count' => $count, 'truncated' => $truncated];
    }

    /**
     * @return list<mixed>
     */
    private function mapOrderToRow(Order $order, int $rowNumber): array
    {
        $address = is_array($order->shipping_address) ? $order->shipping_address : [];

        $products = $order->items
            ->map(fn ($item) => $this->persianNumber($item->quantity).' × '.$item->product_name)
            ->implode("\n");

        $discount = (int) $order->discount_amount + (int) ($order->c2c_discount ?? 0);

        return [
            $rowNumber,
            $order->order_number,
            format_jalali($order->created_at, 'Y/m/d H:i'),
            $order->user?->name ?? ($address['full_name'] ?? '—'),
            $this->textValue($order->user?->phone ?? ($address['phone'] ?? null)),
            $address['province'] ?? '',
            $address['city'] ?? '',
            ['v' => $address['address'] ?? '', 's' => 'wrap'],
            $this->textValue($address['postal_code'] ?? null),
            ['v' => $products !== '' ? $products : '—', 's' => 'wrap'],
            (int) $order->items->sum('quantity'),
            (int) $order->subtotal,
            (int) $order->shipping_cost,
            $discount,
            (int) ($order->wallet_amount ?? 0),
            (int) $order->total,
            $this->paymentMethodLabel($order->payment_method),
            $order->payment_status->label(),
            $order->status->label(),
            $order->order_type === 'presale' ? 'پیش‌فروش' : 'عادی',
            $order->shippingMethod?->name ?? '—',
            $this->textValue($order->tracking_code),
            $this->textValue($order->coupon_code),
            $this->textValue($order->payment_ref),
            ['v' => $order->notes ?? '', 's' => 'wrap'],
        ];
    }

    /** زیرتیتر شامل بازه تاریخ و زمان تهیه گزارش. */
    private function buildSubtitle(Request $request): string
    {
        $parts = [];

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $from = $request->filled('date_from') ? format_jalali($request->input('date_from')) : 'ابتدا';
            $to = $request->filled('date_to') ? format_jalali($request->input('date_to')) : 'امروز';
            $parts[] = "بازه: از {$from} تا {$to}";
        } else {
            $parts[] = 'همه سفارشات';
        }

        $parts[] = 'تهیه‌شده در '.format_jalali(now(), 'Y/m/d H:i');

        return implode('   |   ', $parts);
    }

    private function paymentMethodLabel(?string $method): string
    {
        if ($method === null || $method === '') {
            return '—';
        }

        return self::PAYMENT_METHOD_LABELS[$method] ?? $method;
    }

    /** مقادیری که باید حتماً متن بمانند (موبایل، کد پستی، کد رهگیری و…). */
    private function textValue(?string $value): string
    {
        return ($value === null || $value === '') ? '—' : $value;
    }

    private function persianNumber(int|string|null $number): string
    {
        return to_persian_digits((string) ($number ?? 0));
    }
}
