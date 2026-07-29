<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderStatusMail;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Support\StoreSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderService
{
    public function __construct(private SmsService $sms) {}

    public function pendingActionCount(): int
    {
        return Order::query()
            ->where('payment_status', PaymentStatus::Paid)
            ->whereIn('status', [OrderStatus::Paid, OrderStatus::Processing])
            ->count();
    }

    public function unreadCount(): int
    {
        return Order::query()->unreadByAdmin()->count();
    }

    public function recentUnread(int $limit = 3): Collection
    {
        return Order::query()
            ->unreadByAdmin()
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function markAsReadByAdmin(Order $order): void
    {
        if ($order->isUnreadByAdmin()) {
            $order->update(['admin_read_at' => now()]);
        }
    }

    public function markAllReadByAdmin(): int
    {
        return Order::query()->unreadByAdmin()->update(['admin_read_at' => now()]);
    }

    public function stats(): array
    {
        return [
            'total' => Order::count(),
            'needs_action' => $this->pendingActionCount(),
            'unread' => $this->unreadCount(),
            'today' => Order::whereDate('created_at', today())->count(),
        ];
    }

    public function filteredQuery(Request $request): Builder
    {
        $query = Order::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->boolean('needs_action')) {
            $query->where('payment_status', PaymentStatus::Paid)
                ->whereIn('status', [OrderStatus::Paid, OrderStatus::Processing]);
        }

        if ($request->boolean('unread')) {
            $query->unreadByAdmin();
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('tracking_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        return $query->latest();
    }

    public function resolvePhone(Order $order): ?string
    {
        return $order->shipping_address['phone']
            ?? $order->user?->phone
            ?? null;
    }

    public function resolveEmail(Order $order): ?string
    {
        return $order->user?->email;
    }

    public function logActivity(Order $order, string $action, string $description, ?array $meta = null, ?User $actor = null): void
    {
        $order->activities()->create([
            'user_id' => $actor?->id ?? auth()->id(),
            'action' => $action,
            'description' => $description,
            'meta' => $meta,
        ]);
    }

    public function notifyStatusChange(Order $order, OrderStatus $status, bool $sendSms, bool $sendEmail = false): bool
    {
        $smsSent = false;

        if ($sendSms) {
            $phone = $this->resolvePhone($order);

            if ($phone) {
                $storeName = StoreSettings::get('store_name', config('app.name'));

                $text = match ($status) {
                    OrderStatus::Processing => "{$storeName}: سفارش {$order->order_number} در حال آماده‌سازی است.",
                    OrderStatus::Shipped => $this->shippedMessage($order, $storeName),
                    OrderStatus::Delivered => "{$storeName}: سفارش {$order->order_number} تحویل داده شد. از خرید شما سپاسگزاریم.",
                    default => null,
                };

                if ($text) {
                    $smsSent = match ($status) {
                        OrderStatus::Processing => $this->sms->notifyOrderProcessing($phone, $order, $storeName),
                        OrderStatus::Shipped => $this->sms->notifyOrderShipped($phone, $order, $storeName),
                        OrderStatus::Delivered => $this->sms->notifyOrderDelivered($phone, $order, $storeName),
                        default => false,
                    };
                }
            }
        }

        if ($sendEmail) {
            $this->sendStatusEmail($order, $status);
        }

        return $smsSent;
    }

    public function notifyTrackingUpdate(Order $order, bool $sendSms, bool $sendEmail = false): bool
    {
        $smsSent = false;

        if ($sendSms && $order->tracking_code) {
            $phone = $this->resolvePhone($order);

            if ($phone) {
                $storeName = StoreSettings::get('store_name', config('app.name'));
                $smsSent = $this->sms->notifyOrderShipped($phone, $order, $storeName);
            }
        }

        if ($sendEmail && $order->tracking_code) {
            $storeName = StoreSettings::get('store_name', config('app.name'));
            $this->sendEmail($order, $this->shippedMessage($order, $storeName));
        }

        return $smsSent;
    }

    public function sendConfirmationEmail(Order $order): void
    {
        $email = $this->resolveEmail($order);

        if (! $email) {
            return;
        }

        $order->loadMissing(['items', 'shippingMethod']);

        Mail::to($email)->send(new OrderConfirmationMail($order));
    }

    public function sendStatusEmail(Order $order, OrderStatus $status): void
    {
        $storeName = StoreSettings::get('store_name', config('app.name'));

        $message = match ($status) {
            OrderStatus::Processing => "سفارش {$order->order_number} در حال آماده‌سازی است.",
            OrderStatus::Shipped => $this->shippedMessage($order, $storeName),
            OrderStatus::Delivered => "سفارش {$order->order_number} تحویل داده شد. از خرید شما سپاسگزاریم.",
            OrderStatus::Cancelled => "سفارش {$order->order_number} لغو شد.",
            default => null,
        };

        if ($message) {
            $this->sendEmail($order, $message);
        }
    }

    private function sendEmail(Order $order, string $message): void
    {
        $email = $this->resolveEmail($order);

        if (! $email) {
            return;
        }

        Mail::to($email)->send(new OrderStatusMail($order, $message));
    }

    private function shippedMessage(Order $order, string $storeName): string
    {
        $message = "{$storeName}: سفارش {$order->order_number} ارسال شد.";

        if ($order->tracking_code) {
            $message .= " کد رهگیری: {$order->tracking_code}";
        }

        return $message;
    }

    public function cancelByUser(Order $order): void
    {
        if (! $order->canBeCancelledByUser()) {
            throw new \RuntimeException('امکان لغو این سفارش وجود ندارد.');
        }

        DB::transaction(function () use ($order) {
            $this->cancelOrder($order, restoreStock: true);
            $this->logActivity($order, 'cancelled', 'سفارش توسط مشتری لغو شد.');
        });

        $this->sendStatusEmail($order->fresh(), OrderStatus::Cancelled);
    }

    public function cancelByAdmin(Order $order, ?User $admin = null): void
    {
        if (! $order->canBeCancelledByAdmin()) {
            throw new \RuntimeException('امکان لغو این سفارش وجود ندارد.');
        }

        $restoreStock = in_array($order->status, [OrderStatus::Pending, OrderStatus::Failed, OrderStatus::Paid, OrderStatus::Processing], true);

        DB::transaction(function () use ($order, $restoreStock, $admin) {
            $this->cancelOrder($order, restoreStock: $restoreStock);
            $this->logActivity($order, 'cancelled', 'سفارش توسط مدیر لغو شد.', actor: $admin);
        });

        $this->sendStatusEmail($order->fresh(), OrderStatus::Cancelled);
    }

    private function cancelOrder(Order $order, bool $restoreStock): void
    {
        $callback = function () use ($order, $restoreStock) {
            if ($restoreStock) {
                $order->load('items');

                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                    }
                }
            }

            $order->update(['status' => OrderStatus::Cancelled]);
        };

        // اگر از داخل transaction بیرونی صدا زده شود، تو در تو امن است
        if (DB::transactionLevel() > 0) {
            $callback();

            return;
        }

        DB::transaction($callback);
    }

    public function updatePaymentStatus(Order $order, PaymentStatus $paymentStatus, ?string $note = null, bool $sendEmail = false, ?User $admin = null): void
    {
        $previous = $order->payment_status;

        DB::transaction(function () use ($order, $paymentStatus, $note, $admin, $previous) {
            $order->update(['payment_status' => $paymentStatus]);

            $this->logActivity($order, 'payment_updated', "وضعیت پرداخت از {$previous->label()} به {$paymentStatus->label()} تغییر کرد.", [
                'from' => $previous->value,
                'to' => $paymentStatus->value,
                'note' => $note,
            ], $admin);
        });

        if ($sendEmail) {
            $this->sendEmail($order, "وضعیت پرداخت سفارش {$order->order_number} به «{$paymentStatus->label()}» تغییر کرد.");
        }
    }

    /**
     * علامت‌گذاری پرداخت ناموفق/لغو‌شده و آزادسازی موجودی رزرو‌شده.
     */
    public function markPaymentFailed(Order $order, string $reason = 'پرداخت ناموفق یا لغو شد.'): bool
    {
        $changed = false;

        DB::transaction(function () use ($order, $reason, &$changed) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->first();

            if (! $locked) {
                return;
            }

            if ($locked->payment_status === PaymentStatus::Paid || $locked->payment_status === PaymentStatus::Refunded) {
                return;
            }

            if ($locked->status === OrderStatus::Cancelled) {
                if ($locked->payment_status !== PaymentStatus::Failed) {
                    $locked->update(['payment_status' => PaymentStatus::Failed]);
                    $changed = true;
                }

                return;
            }

            $shouldRestoreStock = $locked->payment_status === PaymentStatus::Pending
                && in_array($locked->status, [OrderStatus::Pending, OrderStatus::Failed], true);

            if ($shouldRestoreStock) {
                $locked->load('items');

                foreach ($locked->items as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                    }
                }

                if ($locked->coupon_id) {
                    Coupon::query()->whereKey($locked->coupon_id)->where('used_count', '>', 0)->decrement('used_count');
                }
            }

            $updates = [];

            if ($locked->payment_status !== PaymentStatus::Failed) {
                $updates['payment_status'] = PaymentStatus::Failed;
            }

            if ($locked->status !== OrderStatus::Failed) {
                $updates['status'] = OrderStatus::Failed;
            }

            if ($updates !== []) {
                $locked->update($updates);
                $changed = true;
                $this->logActivity($locked, 'payment_failed', $reason);
            }
        });

        $order->refresh();

        return $changed;
    }

    /**
     * رزرو مجدد موجودی برای تلاش دوباره پرداخت سفارش ناموفق.
     */
    public function reserveStockForPaymentRetry(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->payment_status !== PaymentStatus::Failed || $locked->status === OrderStatus::Cancelled) {
                return;
            }

            $locked->load('items');

            foreach ($locked->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->first();

                if (! $product || ! $product->is_active || $product->stock < $item->quantity) {
                    throw new \RuntimeException("موجودی {$item->product_name} برای پرداخت مجدد کافی نیست.");
                }

                $product->decrement('stock', $item->quantity);
            }

            if ($locked->coupon_id) {
                Coupon::query()->whereKey($locked->coupon_id)->increment('used_count');
            }

            $locked->update([
                'payment_status' => PaymentStatus::Pending,
                'status' => OrderStatus::Pending,
            ]);
            $this->logActivity($locked, 'payment_retry', 'تلاش مجدد برای پرداخت — موجودی دوباره رزرو شد.');
        });

        $order->refresh();
    }

    public function reorder(Order $order, CartService $cart): array
    {
        $order->load('items.product');
        $added = 0;
        $skipped = [];

        foreach ($order->items as $item) {
            $product = $item->product;

            if (! $product || ! $product->is_active || $product->stock < 1) {
                $skipped[] = $item->product_name;

                continue;
            }

            try {
                $size = $item->options['size'] ?? null;
                $color = $item->options['color'] ?? null;
                $qty = min($item->quantity, $product->stock);
                $cart->add($product->id, $qty, $size, $color);
                $added += $qty;
            } catch (\RuntimeException) {
                $skipped[] = $item->product_name;
            }
        }

        return ['added' => $added, 'skipped' => $skipped];
    }

    public function createManualOrder(array $data, ?User $admin = null): Order
    {
        $order = DB::transaction(function () use ($data, $admin) {
            $shippingMethod = \App\Models\ShippingMethod::findOrFail($data['shipping_method_id']);
            $subtotal = 0;
            $lineItems = [];

            foreach ($data['items'] as $row) {
                $product = Product::lockForUpdate()->findOrFail($row['product_id']);

                if (! $product->is_active) {
                    throw new \RuntimeException("محصول {$product->name} غیرفعال است.");
                }

                $size = $row['size'] ?? null;
                $color = $row['color'] ?? null;
                $qty = (int) $row['quantity'];

                if ($product->stock < $qty) {
                    throw new \RuntimeException("موجودی {$product->name} کافی نیست.");
                }

                if ($product->hasSizes() && blank($size)) {
                    throw new \RuntimeException("سایز محصول {$product->name} الزامی است.");
                }

                if ($product->hasColors() && blank($color)) {
                    throw new \RuntimeException("رنگ محصول {$product->name} الزامی است.");
                }

                $lineTotal = $product->price * $qty;
                $subtotal += $lineTotal;

                $lineItems[] = compact('product', 'qty', 'size', 'color', 'lineTotal');
            }

            $shippingCost = $shippingMethod->calculateCost($subtotal);
            $total = $subtotal + $shippingCost;
            $markAsPaid = ! empty($data['mark_as_paid']);

            $order = Order::create([
                'user_id' => $data['user_id'],
                'order_number' => Order::generateOrderNumber(),
                'status' => $markAsPaid ? OrderStatus::Paid : OrderStatus::Pending,
                'payment_status' => $markAsPaid ? PaymentStatus::Paid : PaymentStatus::Pending,
                'payment_ref' => $markAsPaid ? 'MANUAL-'.strtoupper(uniqid()) : null,
                'paid_at' => $markAsPaid ? now() : null,
                'shipping_method_id' => $shippingMethod->id,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount_amount' => 0,
                'total' => $total,
                'shipping_address' => [
                    'full_name' => $data['full_name'],
                    'phone' => $data['phone'],
                    'province' => $data['province'],
                    'city' => $data['city'],
                    'address' => $data['address'],
                    'postal_code' => $data['postal_code'],
                ],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($lineItems as $line) {
                $line['product']->decrement('stock', $line['qty']);

                $options = array_filter([
                    'size' => $line['size'] ?? null,
                    'color' => $line['color'] ?? null,
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'product_sku' => $line['product']->sku,
                    'options' => $options !== [] ? $options : null,
                    'price' => $line['product']->price,
                    'quantity' => $line['qty'],
                    'total' => $line['lineTotal'],
                ]);
            }

            $this->logActivity($order, 'created', 'سفارش دستی توسط مدیر ثبت شد.', actor: $admin);

            return $order;
        });

        $this->sendConfirmationEmail($order);

        return $order;
    }

    public function cancelExpiredPendingOrders(): int
    {
        $hours = config('orders.pending_expiry_hours', 24);
        $count = 0;

        Order::query()
            ->where('payment_status', PaymentStatus::Pending)
            ->where('status', OrderStatus::Pending)
            ->where('created_at', '<', now()->subHours($hours))
            ->with('items')
            ->chunkById(50, function ($orders) use (&$count) {
                foreach ($orders as $order) {
                    try {
                        DB::transaction(function () use ($order) {
                            $this->cancelOrder($order, restoreStock: true);
                            $this->logActivity($order, 'auto_cancelled', 'سفارش به‌دلیل عدم پرداخت در مهلت مقرر لغو شد.');
                        });
                        $count++;
                    } catch (\Throwable) {
                        // skip problematic orders
                    }
                }
            });

        return $count;
    }

    public function calculatePartialRefundAmount(Order $order, array $itemQuantities): int
    {
        $amount = 0;

        foreach ($order->items as $item) {
            $qty = (int) ($itemQuantities[$item->id] ?? 0);

            if ($qty > 0) {
                $amount += $item->price * min($qty, $item->quantity);
            }
        }

        return $amount > 0 ? $amount : $order->total;
    }
}
