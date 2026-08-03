<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\StoreSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class InvoiceController extends Controller
{
    public function show(Request $request, Order $order)
    {
        $this->authorize('invoice', $order);

        $order->load(['items', 'user', 'shippingMethod']);

        $storeName = StoreSettings::get('store_name', config('app.name'));
        $theme = StoreSettings::themeCssVariables();
        $logoUrl = StoreSettings::logoUrl();
        $logo = StoreSettings::get('logo');

        if ($logo && ! $logoUrl && Storage::disk('public')->exists($logo)) {
            $logoUrl = asset('storage/'.$logo);
        }

        if ($request->query('format') === 'pdf') {
            // لوگو: مسیر فیزیکی برای جلوگیری از HTTP درخواست
            $pdfLogoUrl = null;
            if ($logo && Storage::disk('public')->exists($logo)) {
                $pdfLogoUrl = 'file:///' . str_replace('\\', '/', storage_path('app/public/' . $logo));
            }

            $html = view('invoices.order', [
                'order' => $order,
                'storeName' => $storeName,
                'logoUrl' => $pdfLogoUrl,
                'theme' => $theme,
            ])->render();

            // ============================================================
            // فقط توی local (php artisan serve) لینک‌های HTTP رو پاک کن
            // چون تک‌نخیه و ددلاک می‌شه
            // روی VPS/Production Apache/Nginx چندنخیه و مشکلی نداره
            // ============================================================
            $isLocalDev = app()->environment('local') && 
                          (str_contains(config('app.url'), '127.0.0.1') || 
                           str_contains(config('app.url'), 'localhost'));

            if ($isLocalDev) {
                $html = preg_replace('/<link[^>]+>/i', '', $html);
                $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
            }

            // فیکس مقادیر خالی theme (روی هر دو محیط لازمه)
            $html = str_replace('color: ;', 'color: #334155;', $html);
            $html = str_replace('background: ;', 'background: #ffffff;', $html);
            $html = str_replace('border: 1px solid ;', 'border: 1px solid #e2e8f0;', $html);
            $html = str_replace('border-bottom: 2px solid ;', 'border-bottom: 2px solid #2563eb;', $html);
            $html = str_replace('border-top: 2px solid ;', 'border-top: 2px solid #2563eb;', $html);
            $html = str_replace('background: linear-gradient(135deg,  0%,  100%);', 'background: #2563eb;', $html);

            try {
                $defaultConfig = (new ConfigVariables())->getDefaults();
                $fontDirs = $defaultConfig['fontDir'];

                $defaultFontConfig = (new FontVariables())->getDefaults();
                $fontData = $defaultFontConfig['fontdata'];

                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'margin_left' => 10,
                    'margin_right' => 10,
                    'margin_top' => 10,
                    'margin_bottom' => 10,
                    'default_font' => 'vazirmatn',
                    'directionality' => 'rtl',
                    'autoScriptToLang' => true,
                    'autoLangToFont' => true,
                    'fontDir' => array_merge($fontDirs, [
                        storage_path('fonts'),
                    ]),
                    'fontdata' => $fontData + [
                        'vazirmatn' => [
                            'R' => 'Vazirmatn-Regular.ttf',
                            'B' => 'Vazirmatn-Bold.ttf',
                        ],
                    ],
                ]);

                $mpdf->WriteHTML($html);

                $tmpPath = storage_path('app/invoice-'.$order->order_number.'.pdf');
                $mpdf->Output($tmpPath, 'F');

                return response()->download($tmpPath, "invoice-{$order->order_number}.pdf", [
                    'Content-Type' => 'application/pdf',
                ])->deleteFileAfterSend();

            } catch (\Throwable $e) {
                return back()->with('error', 'خطا در تولید PDF: ' . $e->getMessage());
            }
        }

        return view('invoices.order', compact('order', 'storeName', 'logoUrl', 'theme'));
    }
}