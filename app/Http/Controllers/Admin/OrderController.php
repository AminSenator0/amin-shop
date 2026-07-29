<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManualOrderRequest;
use App\Http\Requests\Admin\UpdateOrderNotesRequest;
use App\Http\Requests\Admin\UpdateOrderPaymentStatusRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Http\Requests\Admin\UpdateOrderTrackingRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request, OrderService $orderService)
    {
        $orders = $orderService->filteredQuery($request)->paginate(15)->withQueryString();
        $stats = $orderService->stats();

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    public function create()
    {
        $users = User::where('role', UserRole::Customer)->orderBy('name')->get(['id', 'name', 'email', 'phone']);
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'price', 'stock', 'sku']);
        $shippingMethods = ShippingMethod::where('is_active', true)->get();

        return view('admin.orders.create', compact('users', 'products', 'shippingMethods'));
    }

    public function store(StoreManualOrderRequest $request, OrderService $orderService): RedirectResponse
    {
        try {
            $order = $orderService->createManualOrder($request->validated(), $request->user());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'سفارش دستی با موفقیت ثبت شد.');
    }

    public function show(Order $order, OrderService $orderService)
    {
        $this->authorize('manage', $order);

        $orderService->markAsReadByAdmin($order);

        $order->load(['user', 'items.product', 'shippingMethod', 'returns.items.orderItem', 'activities.user']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order, OrderService $orderService)
    {
        $this->authorize('manage', $order);

        $status = OrderStatus::from($request->validated('status'));
        $previous = $order->status;

        DB::transaction(function () use ($order, $status, $previous, $request, $orderService) {
            $updates = ['status' => $status];

            if ($status === OrderStatus::Shipped && ! $order->shipped_at) {
                $updates['shipped_at'] = now();
            }

            if ($status === OrderStatus::Delivered && ! $order->delivered_at) {
                $updates['delivered_at'] = now();
            }

            $order->update($updates);

            $orderService->logActivity($order, 'status_changed', "وضعیت از {$previous->label()} به {$status->label()} تغییر کرد.", [
                'from' => $previous->value,
                'to' => $status->value,
            ], $request->user());
        });

        $sendSms = $request->boolean('send_sms', true);
        $sendEmail = $request->boolean('send_email', false);
        $smsSent = $orderService->notifyStatusChange($order->fresh(), $status, $sendSms, $sendEmail);

        $message = 'وضعیت سفارش به‌روزرسانی شد.';

        if ($sendSms && ! $smsSent && in_array($status, [OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Delivered], true)) {
            if (! $orderService->resolvePhone($order)) {
                $message .= ' شماره تماس برای ارسال پیامک یافت نشد.';
            }
        } elseif ($smsSent) {
            $message .= ' پیامک به مشتری ارسال شد.';
        }

        if ($sendEmail) {
            $message .= ' ایمیل به مشتری ارسال شد.';
        }

        return back()->with('success', $message);
    }

    public function updateTracking(UpdateOrderTrackingRequest $request, Order $order, OrderService $orderService)
    {
        $this->authorize('manage', $order);

        $previous = $order->tracking_code;

        DB::transaction(function () use ($order, $request, $orderService, $previous) {
            $order->update(['tracking_code' => $request->validated('tracking_code')]);

            $orderService->logActivity($order, 'tracking_updated', 'کد رهگیری به‌روزرسانی شد.', [
                'from' => $previous,
                'to' => $order->tracking_code,
            ], $request->user());
        });

        $sendSms = $request->boolean('send_sms', true);
        $sendEmail = $request->boolean('send_email', false);
        $smsSent = $orderService->notifyTrackingUpdate($order->fresh(), $sendSms, $sendEmail);

        $message = 'کد رهگیری به‌روزرسانی شد.';

        if ($sendSms && ! $smsSent) {
            if (! $order->tracking_code) {
                $message .= ' برای ارسال پیامک، کد رهگیری را وارد کنید.';
            } elseif (! $orderService->resolvePhone($order)) {
                $message .= ' شماره تماس برای ارسال پیامک یافت نشد.';
            }
        } elseif ($smsSent) {
            $message .= ' پیامک به مشتری ارسال شد.';
        }

        if ($sendEmail) {
            $message .= ' ایمیل به مشتری ارسال شد.';
        }

        return back()->with('success', $message);
    }

    public function updatePaymentStatus(UpdateOrderPaymentStatusRequest $request, Order $order, OrderService $orderService)
    {
        $this->authorize('manage', $order);

        $paymentStatus = PaymentStatus::from($request->validated('payment_status'));

        $orderService->updatePaymentStatus(
            $order,
            $paymentStatus,
            $request->validated('payment_note'),
            $request->boolean('send_email', false),
            $request->user()
        );

        return back()->with('success', 'وضعیت پرداخت به‌روزرسانی شد.');
    }

    public function cancel(Order $order, OrderService $orderService): RedirectResponse
    {
        $this->authorize('adminCancel', $order);

        try {
            $orderService->cancelByAdmin($order, auth()->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'سفارش لغو شد و در صورت نیاز موجودی بازگردانده شد.');
    }

    public function markAllRead(OrderService $orderService): RedirectResponse
    {
        $count = $orderService->markAllReadByAdmin();

        return redirect()
            ->route('admin.orders.index')
            ->with('success', "{$count} سفارش به‌عنوان خوانده‌شده علامت‌گذاری شد.");
    }

    public function export(Request $request, OrderService $orderService): StreamedResponse
    {
        $orders = $orderService->filteredQuery($request)->get();
        $filename = 'orders-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['شماره سفارش', 'مشتری', 'موبایل', 'مبلغ', 'وضعیت سفارش', 'وضعیت پرداخت', 'کد رهگیری', 'تاریخ']);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->user->name,
                    $order->user->phone ?? ($order->shipping_address['phone'] ?? ''),
                    $order->total,
                    $order->status->label(),
                    $order->payment_status->label(),
                    $order->tracking_code ?? '',
                    format_jalali($order->created_at, 'Y/m/d H:i'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function updateInternalNotes(UpdateOrderNotesRequest $request, Order $order, OrderService $orderService)
    {
        $this->authorize('manage', $order);

        DB::transaction(function () use ($request, $order, $orderService) {
            $order->update(['internal_notes' => $request->validated('internal_notes')]);

            $orderService->logActivity($order, 'notes_updated', 'یادداشت داخلی به‌روزرسانی شد.', actor: $request->user());
        });

        return back()->with('success', 'یادداشت داخلی ذخیره شد.');
    }
}
