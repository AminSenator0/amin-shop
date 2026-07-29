<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreCouponRequest;
use App\Models\Coupon;
use App\Services\CouponService;

class CouponController extends Controller
{
    public function __construct(
        private CouponService $coupons,
    ) {}

    public function index()
    {
        $coupons = auth()->user()
            ->savedCoupons()
            ->latest('user_coupons.created_at')
            ->get();

        return view('user.coupons.index', compact('coupons'));
    }

    public function store(StoreCouponRequest $request)
    {
        $code = strtoupper(trim($request->validated('code')));
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return back()
                ->withInput()
                ->with('error', 'کد تخفیف معتبر نیست.');
        }

        if (! $coupon->isAvailable()) {
            return back()
                ->withInput()
                ->with('error', 'این کد تخفیف دیگر قابل استفاده نیست.');
        }

        $user = auth()->user();

        if ($user->savedCoupons()->where('coupon_id', $coupon->id)->exists()) {
            return back()
                ->withInput()
                ->with('error', 'این کد تخفیف قبلاً در حساب شما ثبت شده است.');
        }

        $user->savedCoupons()->attach($coupon->id);

        return redirect()
            ->route('user.coupons.index')
            ->with('success', 'کد تخفیف با موفقیت به حساب شما اضافه شد.');
    }

    public function destroy(Coupon $coupon)
    {
        if (! auth()->user()->savedCoupons()->where('coupon_id', $coupon->id)->exists()) {
            abort(403);
        }

        auth()->user()->savedCoupons()->detach($coupon->id);

        return redirect()
            ->route('user.coupons.index')
            ->with('success', 'کد تخفیف از حساب شما حذف شد.');
    }

    public function apply(Coupon $coupon)
    {
        if (! auth()->user()->savedCoupons()->where('coupon_id', $coupon->id)->exists()) {
            abort(403);
        }

        try {
            $this->coupons->remember($coupon->code);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('user.coupons.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('checkout.index')
            ->with('success', 'کد تخفیف برای خرید بعدی انتخاب شد.');
    }
}
