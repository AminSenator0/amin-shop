<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\PresaleCampaign;
use App\Models\PresaleProduct;
use App\Enums\PresaleStatus;
use App\Services\PresalePricingService;
use Illuminate\Http\Request;

class PresaleCampaignController extends Controller
{
    public function __construct(
        private PresalePricingService $pricing,
    ) {}

    public function index()
    {
               $campaigns = PresaleCampaign::withCount('presaleProducts')
            ->withCount('presaleOrders')
            ->latest()
            ->paginate(20);
        return view('admin.presale.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('admin.presale.campaigns.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'starts_at'  => ['required', 'date'],
            'ends_at'    => ['required', 'date', 'after:starts_at'],
        ]);

        PresaleCampaign::create([
            'title'     => $validated['title'],
            'starts_at' => $validated['starts_at'],
            'ends_at'   => $validated['ends_at'],
            'status'    => 'active',
        ]);

        return redirect()->route('admin.presale.campaigns.index')
            ->with('success', 'کمپین پیش‌فروش ایجاد شد.');
    }

    public function show(PresaleCampaign $presaleCampaign)
    {
        $presaleCampaign->load([
            'presaleProducts.product',
            'presaleProducts.orderItems.order',
        ]);

         $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        $orders = Order::where('order_type', 'presale')
            ->where('presale_campaign_id', $presaleCampaign->id)
            ->with(['items.product', 'user'])
            ->latest()
            ->paginate(10);

        return view('admin.presale.campaigns.show', compact('presaleCampaign', 'products', 'orders'));
    }

    public function edit(PresaleCampaign $presaleCampaign)
    {
        return view('admin.presale.campaigns.edit', compact('presaleCampaign'));
    }

    public function update(Request $request, PresaleCampaign $presaleCampaign)
    {
        $validated = $request->validate([
            'title'     => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at'   => ['required', 'date', 'after:starts_at'],
        ]);

        $presaleCampaign->update([
            'title'     => $validated['title'],
            'starts_at' => $validated['starts_at'],
            'ends_at'   => $validated['ends_at'],
        ]);

       return redirect()->route('admin.presale.campaigns.index')
            ->with('success', 'کمپین به‌روزرسانی شد.');
    }

    public function destroy(PresaleCampaign $presaleCampaign)
    {
        $presaleCampaign->delete();

        return redirect()->route('admin.presale.campaigns.index')
            ->with('success', 'کمپین حذف شد.');
    }

    /** تمدید کمپین */
    public function extend(Request $request, PresaleCampaign $presaleCampaign)
    {
        $validated = $request->validate([
            'new_ends_at' => ['required', 'date', 'after:ends_at'],
        ]);

        $presaleCampaign->update([
            'ends_at'     => $validated['new_ends_at'],
            'is_extended' => true,
            'extended_at' => now(),
        ]);

        return back()->with('success', 'مهلت کمپین تمدید شد.');
    }

    /** بستن کمپین */
    public function close(PresaleCampaign $presaleCampaign)
    {
        $this->pricing->closeCampaign($presaleCampaign, auth()->id());

        return back()->with('success', 'کمپین بسته شد. حالا می‌توانید قیمت نهایی محصولات را تعیین کنید.');
    }

    /** افزودن محصول به کمپین */
    public function addProduct(Request $request, PresaleCampaign $presaleCampaign)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'presale_price' => ['required', 'integer', 'min:0'],
            'custom_fields' => ['nullable', 'array'],
            'custom_fields.*.option_id' => ['nullable', 'integer'],
        ]);

        $product = \App\Models\Product::with('customFields.optionDetails')->findOrFail($validated['product_id']);
        // بررسی فیلدهای سفارشی
        $customFields = [];
        if ($product->customFields->isNotEmpty()) {
            $submitted = $validated['custom_fields'] ?? [];
            foreach ($product->customFields as $field) {
                $optionId = $submitted[$field->id]['option_id'] ?? null;
                if (! $optionId) {
                    return back()->with('error', "برای فیلد «{$field->label}» باید یک گزینه انتخاب شود.");
                }
                                $option = $field->optionDetails->firstWhere('id', $optionId);
                if (! $option) {
                    return back()->with('error', "گزینه انتخاب‌شده برای فیلد «{$field->label}» نامعتبر است.");
                }
                $customFields[$field->id] = [
                    'field_label' => $field->label,
                    'option_id' => $option->id,
                    'option_value' => $option->value,
                ];
            }
        }

        // بررسی تکراری (محصول + گزینه‌های سفارشی)
        $existing = PresaleProduct::where('campaign_id', $presaleCampaign->id)
            ->where('product_id', $validated['product_id'])
            ->get()
            ->first(function ($pp) use ($customFields) {
                return $pp->custom_fields == $customFields;
            });

        if ($existing) {
            return back()->with('error', 'این ترکیب محصول و گزینه‌ها قبلاً به کمپین اضافه شده است.');
        }

        PresaleProduct::create([
            'campaign_id' => $presaleCampaign->id,
            'product_id' => $validated['product_id'],
            'custom_fields' => $customFields !== [] ? $customFields : null,
            'presale_price' => $validated['presale_price'],
            'is_active' => true,
        ]);

        $label = $product->name;
        if ($customFields !== []) {
            $options = collect($customFields)->pluck('option_value')->implode(' / ');
            $label .= " ({$options})";
        }

        return back()->with('success', 'محصول «' . $label . '» به کمپین اضافه شد.');
    }

    /** حذف محصول از کمپین */
    public function removeProduct(PresaleCampaign $presaleCampaign, PresaleProduct $presaleProduct)
    {
        if ($presaleProduct->campaign_id !== $presaleCampaign->id) {
            abort(404);
        }

        $hasOrders = \App\Models\OrderItem::where('presale_product_id', $presaleProduct->id)->exists();

        if ($hasOrders) {
            $presaleProduct->update(['is_active' => false]);
            return back()->with('success', 'محصول غیرفعال شد (دارای سفارش).');
        }

        $presaleProduct->delete();

        return back()->with('success', 'محصول از کمپین حذف شد.');
    }

    /** تعیین قیمت نهایی و جریمه */
    public function setPricing(Request $request, PresaleCampaign $presaleCampaign, PresaleProduct $presaleProduct)
    {
        if ($presaleProduct->campaign_id !== $presaleCampaign->id) {
            abort(404);
        }

        $validated = $request->validate([
            'final_price'    => ['required', 'integer', 'min:0'],
            'penalty_amount' => ['required', 'integer', 'min:0'],
        ]);

        if ($presaleCampaign->status !== 'closed') {
            return back()->with('error', 'ابتدا کمپین را ببندید تا بتوانید قیمت نهایی تعیین کنید.');
        }

        $this->pricing->setProductPricing(
            $presaleProduct,
            (int) $validated['final_price'],
            (int) $validated['penalty_amount'],
            auth()->id(),
        );

        return back()->with('success', 'قیمت نهایی و جریمه ثبت شد و سفارش‌ها همگام‌سازی شدند.');
    }

    /** تعیین مهلت تسویه برای سفارش‌ها */
    public function setDeadline(Request $request, PresaleCampaign $presaleCampaign)
    {
        $validated = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $orders = Order::where('order_type', 'presale')
            ->where('presale_campaign_id', $presaleCampaign->id)
            ->whereIn('presale_status', [
                PresaleStatus::AwaitingPayment->value,
                PresaleStatus::PartiallyPaid->value,
            ])
            ->whereNull('presale_deadline_at')
            ->get();

        foreach ($orders as $order) {
            $this->pricing->extendDeadline($order, $validated['days'], auth()->id());
        }

        return back()->with('success', "مهلت {$validated['days']} روزه برای سفارش‌های واجد شرایط تعیین شد.");
    }
        /** لغو سفارش پیش‌فروش و استرداد به کیف پول */
    public function cancelPresaleOrder(Request $request, \App\Models\Order $order)
    {
        if ($order->order_type !== 'presale') {
            abort(404);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->pricing->cancelOrder($order, $validated['reason'], auth()->id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'سفارش پیش‌فروش لغو و مبالغ به کیف پول مشتری بازگردانده شد.');
    }
}