<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function show(Request $request): View
    {
        $code = trim($request->string('code')->toString());
        $phone = trim($request->string('phone')->toString());
        $order = null;
        $phoneRequired = false;
        $phoneMismatch = false;

        if ($code !== '') {
            $candidate = Order::with(['items.product', 'shippingMethod'])
                ->where(function ($q) use ($code) {
                    $q->where('order_number', $code)->orWhere('tracking_code', $code);
                })
                ->first();

            if ($candidate) {
                $expectedPhone = $this->normalizePhone(
                    $candidate->shipping_address['phone'] ?? $candidate->user?->phone ?? ''
                );

                if ($expectedPhone === '') {
                    $order = $candidate;
                } elseif ($phone === '') {
                    $phoneRequired = true;
                } elseif ($this->normalizePhone($phone) !== $expectedPhone) {
                    $phoneMismatch = true;
                } else {
                    $order = $candidate;
                }
            }
        }

        return view('shop.orders.track', compact('order', 'code', 'phone', 'phoneRequired', 'phoneMismatch'));
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '98') && strlen($digits) === 12) {
            $digits = '0'.substr($digits, 2);
        }

        return $digits;
    }
}
