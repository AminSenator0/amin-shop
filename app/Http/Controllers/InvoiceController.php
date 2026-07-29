<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\StoreSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function __invoke(Request $request, Order $order)
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
            $html = view('invoices.order', compact('order', 'storeName', 'logoUrl', 'theme'))->render();
            $html = prepare_pdf_rtl_html($html);

            return Pdf::loadHTML($html)
                ->setPaper('a4')
                ->download("invoice-{$order->order_number}.pdf");
        }

        return view('invoices.order', compact('order', 'storeName', 'logoUrl', 'theme'));
    }
}
