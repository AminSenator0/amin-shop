<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\C2CCheck;
use App\Models\C2CPayment;
use Illuminate\Http\Request;

class C2CPaymentController extends Controller
{
    public function requestCheck(Request $request, C2CPayment $c2cPayment)
    {
        if ($c2cPayment->order->user_id !== $request->user()->id) {
            abort(403);
        }

        $existing = C2CCheck::where('c2c_payment_id', $c2cPayment->id)
            ->whereIn('status', ['pending', 'checking'])
            ->first();

        if ($existing) {
            return response()->json(['check_id' => $existing->id, 'status' => $existing->status]);
        }

        if (in_array($c2cPayment->status, ['verified', 'rejected'])) {
            return response()->json(['message' => 'Payment already processed'], 422);
        }

        $check = C2CCheck::create([
            'c2c_payment_id' => $c2cPayment->id,
            'amount' => $c2cPayment->exact_rial,
            'tracking_code' => $c2cPayment->tracking_code ?? $c2cPayment->order->order_number,
            'status' => 'pending',
            'check_from' => now()->subMinutes(10),
        ]);

        return response()->json(['check_id' => $check->id, 'status' => 'pending']);
    }

    public function checkStatus(Request $request, C2CCheck $check)
    {
        if ($check->c2cPayment->order->user_id !== $request->user()->id) {
            abort(403);
        }

        return response()->json([
            'status' => $check->status,
            'found' => $check->status === 'found',
            'result' => $check->result ? json_decode($check->result) : null,
        ]);
    }
}