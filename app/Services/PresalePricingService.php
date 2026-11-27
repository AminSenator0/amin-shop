<?php

namespace App\Services;

use App\Enums\PresaleStatus;
use App\Models\Order;
use App\Models\PresaleCampaign;
use App\Models\PresaleProduct;
use Illuminate\Support\Facades\DB;

class PresalePricingService
{
    public function __construct(
        private OrderService $orders,
    ) {}

    /**
     * تعیین قیمت نهایی و جریمه یک محصول پیش‌فروش
     * و همگام‌سازی خودکار با همه سفارش‌های مرتبط.
     */
    public function setProductPricing(PresaleProduct $presaleProduct, int $finalPrice, int $penalty, ?int $actorId = null): void
    {
        DB::transaction(function () use ($presaleProduct, $finalPrice, $penalty, $actorId) {
            // ۱) آپدیت محصول پیش‌فروش
            $presaleProduct->update([
                'final_price'    => $finalPrice,
                'penalty_amount' => $penalty,
            ]);

            // ۲) یافتن همه سفارش‌های در انتظار قیمت نهایی
            $affectedOrders = Order::where('order_type', 'presale')
                ->where('presale_campaign_id', $presaleProduct->campaign_id)
                                                ->whereIn('presale_status', [
                    PresaleStatus::AwaitingFinalPrice->value,
                    PresaleStatus::AwaitingPayment->value,
                    PresaleStatus::PartiallyPaid->value,
                ])
                ->lockForUpdate()
                ->get();

            foreach ($affectedOrders as $order) {
                // آپدیت اقلام
                foreach ($order->items as $item) {
                    if ($item->presale_product_id === $presaleProduct->id) {
                        $item->update([
                            'presale_final_price' => $finalPrice,
                            'presale_penalty'     => $penalty,
                        ]);
                    }
                }

                // محاسبه مجدد جمع‌ها
                $finalTotal = $order->items->sum(function ($item) {
                    return ($item->presale_final_price ?? $item->price) * $item->quantity;
                });

                $penaltiesTotal = $order->items->sum(function ($item) {
                    return ($item->presale_penalty ?? 0) * $item->quantity;
                });

                $order->update([
                    'presale_final_total'     => $finalTotal,
                    'presale_penalties_total' => $penaltiesTotal,
                    'presale_status'          => PresaleStatus::AwaitingPayment->value,
                ]);

                $this->orders->logActivity($order, 'presale_price_set', "قیمت نهایی و جریمه برای «{$presaleProduct->product->name}» تعیین شد.");
            }
        });
    }

    /**
     * بستن کمپین.
     */
    public function closeCampaign(PresaleCampaign $campaign, ?int $actorId = null): void
    {
        DB::transaction(function () use ($campaign, $actorId) {
            $campaign->update([
                'status'    => 'closed',
                'closed_at' => now(),
            ]);

            $orders = Order::where('order_type', 'presale')
                ->where('presale_campaign_id', $campaign->id)
                                    ->whereIn('presale_status', [
                    PresaleStatus::AwaitingFinalPrice->value,
                    PresaleStatus::AwaitingPayment->value,
                    PresaleStatus::PartiallyPaid->value,
                ])
                ->get();

            foreach ($orders as $order) {
                $this->orders->logActivity($order, 'campaign_closed', 'کمپین پیش‌فروش بسته شد.');
            }
        });
    }

    /**
     * تمدید مهلت تسویه یک سفارش.
     */
    public function extendDeadline(Order $order, int $days, ?int $actorId = null): void
    {
        if (! $order->isPresale()) {
            throw new \RuntimeException('این سفارش پیش‌فروش نیست.');
        }

        $order->update([
            'presale_deadline_at' => now()->addDays($days),
        ]);

        $this->orders->logActivity($order, 'deadline_extended', "مهلت تسویه {$days} روز تمدید شد.");
    }

    /**
     * لغو سفارش پیش‌فروش و استرداد به کیف پول.
     */
    public function cancelOrder(Order $order, string $reason, ?int $actorId = null): void
    {
        if (! $order->isPresale()) {
            throw new \RuntimeException('این سفارش پیش‌فروش نیست.');
        }

        if ($order->presale_refunded_at) {
            throw new \RuntimeException('این سفارش قبلاً لغو و استرداد شده است.');
        }

        DB::transaction(function () use ($order, $reason, $actorId) {
            // استرداد به کیف پول
            app(PresalePaymentService::class)->refundToWallet($order);

            $order->update([
                'status'              => \App\Enums\OrderStatus::Cancelled,
                'payment_status'      => \App\Enums\PaymentStatus::Refunded,
                'presale_status'      => PresaleStatus::Cancelled->value,
                'presale_refunded_at' => now(),
            ]);

            $this->orders->logActivity($order, 'cancelled', "لغو پیش‌فروش: {$reason}");
        });
    }
}