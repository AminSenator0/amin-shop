<?php

namespace App\Services;

use App\Models\Order;
use App\Support\StoreSettings;
use Illuminate\Support\Facades\Http;

class ZarinpalService
{
    public function isConfigured(): bool
    {
        return $this->merchantId() !== '';
    }

    public function isSandbox(): bool
    {
        return StoreSettings::zarinpalSandbox();
    }

    public function requestPayment(Order $order): array
    {
        $merchantId = $this->requireMerchantId();
        $amount = $this->amountInRials((int) $order->total);

        if ($amount < 10000) {
            throw new \RuntimeException('حداقل مبلغ قابل پرداخت از طریق زرین‌پال ۱۰٬۰۰۰ ریال است.');
        }

        $order->loadMissing('user');

        $mobile = data_get($order->shipping_address, 'phone')
            ?: $order->user?->phone;
        $email = $order->user?->email;

        $metadata = array_filter([
            'mobile' => $mobile ? (string) $mobile : null,
            'email' => $email ? (string) $email : null,
            'order_id' => (string) $order->id,
        ], fn ($value) => filled($value));

        $response = Http::acceptJson()
            ->asJson()
            ->timeout(30)
            ->post($this->apiBaseUrl().'/payment/request.json', [
                'merchant_id' => $merchantId,
                'amount' => $amount,
                'callback_url' => StoreSettings::zarinpalCallbackUrl(),
                'description' => "سفارش {$order->order_number}",
                'metadata' => $metadata,
            ]);

        if (! $response->successful() && empty($response->json())) {
            throw new \RuntimeException('خطا در اتصال به درگاه پرداخت زرین‌پال.');
        }

        $data = $response->json('data') ?? [];
        $errors = $response->json('errors');

        if (! empty($errors) || empty($data['authority'])) {
            throw new \RuntimeException($this->errorMessage($errors, 'خطا در ایجاد درخواست پرداخت.'));
        }

        $authority = (string) $data['authority'];

        return [
            'authority' => $authority,
            'redirect_url' => $this->gatewayBaseUrl().'/StartPay/'.$authority,
            'fee' => $data['fee'] ?? null,
            'fee_type' => $data['fee_type'] ?? null,
        ];
    }

    public function verifyPayment(string $authority, int $amountToman): array
    {
        $authority = trim($authority);

        if ($authority === '') {
            throw new \RuntimeException('شناسه تراکنش نامعتبر است.');
        }

        if ($amountToman < 1) {
            throw new \RuntimeException('مبلغ سفارش برای تایید پرداخت نامعتبر است.');
        }

        $merchantId = $this->requireMerchantId();
        $amount = $this->amountInRials($amountToman);

        $response = Http::acceptJson()
            ->asJson()
            ->timeout(30)
            ->post($this->apiBaseUrl().'/payment/verify.json', [
                'merchant_id' => $merchantId,
                'amount' => $amount,
                'authority' => $authority,
            ]);

        if (! $response->successful() && empty($response->json())) {
            throw new \RuntimeException('خطا در اتصال به درگاه پرداخت برای تایید تراکنش.');
        }

        $data = $response->json('data') ?? [];
        $errors = $response->json('errors');
        $code = isset($data['code']) ? (int) $data['code'] : null;

        // طبق مستندات رسمی: 100 = تایید موفق، 101 = قبلاً تایید شده
        if (! empty($errors) || ! in_array($code, [100, 101], true) || empty($data['ref_id'])) {
            throw new \RuntimeException($this->errorMessage($errors, 'تایید پرداخت ناموفق بود.'));
        }

        return [
            'code' => $code,
            'ref_id' => $data['ref_id'],
            'card_pan' => $data['card_pan'] ?? null,
            'card_hash' => $data['card_hash'] ?? null,
            'fee' => $data['fee'] ?? null,
            'fee_type' => $data['fee_type'] ?? null,
            'already_verified' => $code === 101,
        ];
    }

    public function amountInRials(int $amountInStoreCurrency): int
    {
        return StoreSettings::zarinpalAmountInRials($amountInStoreCurrency);
    }

    private function merchantId(): string
    {
        $merchantId = StoreSettings::zarinpalMerchantId();

        if ($merchantId !== '') {
            return $merchantId;
        }

        // سندباکس: طبق مستندات رسمی، مرچنت‌کد ۳۶ کاراکتری دلخواه کافی است
        if ($this->isSandbox()) {
            return trim((string) config('zarinpal.sandbox_merchant_id', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'));
        }

        return '';
    }

    private function requireMerchantId(): string
    {
        $merchantId = $this->merchantId();

        if ($merchantId === '') {
            throw new \RuntimeException('درگاه زرین‌پال پیکربندی نشده است. مرچنت‌کد را از پنل مدیریت وارد کنید یا حالت تست (Sandbox) را فعال کنید.');
        }

        return $merchantId;
    }

    private function apiBaseUrl(): string
    {
        return $this->isSandbox()
            ? 'https://sandbox.zarinpal.com/pg/v4'
            : 'https://api.zarinpal.com/pg/v4';
    }

    private function gatewayBaseUrl(): string
    {
        return $this->isSandbox()
            ? 'https://sandbox.zarinpal.com/pg'
            : 'https://www.zarinpal.com/pg';
    }

    private function errorMessage(mixed $errors, string $fallback): string
    {
        if (is_array($errors)) {
            if (isset($errors['message']) && is_string($errors['message']) && $errors['message'] !== '') {
                return $errors['message'];
            }

            if (isset($errors[0]['message']) && is_string($errors[0]['message']) && $errors[0]['message'] !== '') {
                return $errors[0]['message'];
            }
        }

        return $fallback;
    }
}
