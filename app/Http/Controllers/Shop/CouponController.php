<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ApplyCouponRequest;
use App\Services\CartService;
use App\Services\CouponService;

class CouponController extends Controller
{
    public function __construct(
        private CouponService $coupons,
        private CartService $cart,
    ) {}

    public function apply(ApplyCouponRequest $request)
    {
        try {
            $this->coupons->apply($request->validated('code'), $this->cart->subtotal());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'کد تخفیف اعمال شد.');
    }

    public function destroy()
    {
        $this->coupons->remove();

        return back()->with('success', 'کد تخفیف حذف شد.');
    }
}
