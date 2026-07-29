<?php

namespace App\Services;

use App\Models\Coupon;

class CouponService
{
    private const SESSION_KEY = 'coupon_code';

    public function getApplied(): ?Coupon
    {
        $code = session(self::SESSION_KEY);

        if (! $code) {
            return null;
        }

        return Coupon::where('code', $code)->first();
    }

    public function apply(string $code, int $subtotal): Coupon
    {
        $coupon = $this->findByCode($code);

        if (! $coupon) {
            throw new \RuntimeException('کد تخفیف معتبر نیست.');
        }

        if (! $coupon->isValid($subtotal)) {
            throw new \RuntimeException('این کد تخفیف قابل استفاده نیست.');
        }

        session([self::SESSION_KEY => $coupon->code]);

        return $coupon;
    }

    public function remember(string $code): Coupon
    {
        $coupon = $this->findByCode($code);

        if (! $coupon) {
            throw new \RuntimeException('کد تخفیف معتبر نیست.');
        }

        if (! $coupon->isAvailable()) {
            throw new \RuntimeException('این کد تخفیف دیگر قابل استفاده نیست.');
        }

        session([self::SESSION_KEY => $coupon->code]);

        return $coupon;
    }

    private function findByCode(string $code): ?Coupon
    {
        return Coupon::where('code', strtoupper(trim($code)))->first();
    }

    public function remove(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function discount(int $subtotal): int
    {
        $coupon = $this->getApplied();

        if (! $coupon || ! $coupon->isValid($subtotal)) {
            $this->remove();

            return 0;
        }

        return $coupon->calculateDiscount($subtotal);
    }
}
