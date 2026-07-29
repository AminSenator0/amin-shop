<?php

namespace App\Support;

class ShoppingFlow
{
    public const SESSION_KEY = 'shopping_flow';

    public static function activate(): void
    {
        session([self::SESSION_KEY => true]);
    }

    public static function deactivate(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public static function isActive(): bool
    {
        return (bool) session(self::SESSION_KEY);
    }

    public static function usesMinimalHeader(): bool
    {
        if (request()->routeIs('cart.*', 'checkout.*')) {
            return true;
        }

        return self::isActive() && request()->routeIs('products.index');
    }

    public static function showsCartBar(): bool
    {
        return self::isActive()
            && request()->routeIs('products.index')
            && self::currentStep() === 'browse';
    }

    public static function currentStep(): ?string
    {
        if (request()->routeIs('checkout.payment*')) {
            return 'payment';
        }

        if (request()->routeIs('checkout.*')) {
            return 'checkout';
        }

        if (request()->routeIs('cart.*')) {
            return 'cart';
        }

        if (request()->routeIs('products.*') && self::isActive()) {
            return 'browse';
        }

        return null;
    }
}
