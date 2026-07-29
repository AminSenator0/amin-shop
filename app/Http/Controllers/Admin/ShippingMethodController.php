<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShippingMethodRequest;
use App\Models\ShippingMethod;
use App\Support\ShippingMethodPresets;
use Illuminate\Http\Request;

class ShippingMethodController extends Controller
{
    public function index(Request $request)
    {
        $query = ShippingMethod::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $methods = $query->latest()->paginate(15)->withQueryString();

        return view('admin.shipping.index', compact('methods'));
    }

    public function create()
    {
        return view('admin.shipping.create', [
            'presets' => ShippingMethodPresets::all(),
        ]);
    }

    public function store(ShippingMethodRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        ShippingMethod::create($data);

        return redirect()->route('admin.shipping.index')->with('success', 'روش ارسال ایجاد شد.');
    }

    public function edit(ShippingMethod $shipping)
    {
        return view('admin.shipping.edit', compact('shipping'));
    }

    public function update(ShippingMethodRequest $request, ShippingMethod $shipping)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $shipping->update($data);

        return redirect()->route('admin.shipping.index')->with('success', 'روش ارسال به‌روزرسانی شد.');
    }

    public function destroy(ShippingMethod $shipping)
    {
        if ($shipping->orders()->exists()) {
            return back()->with('error', 'این روش ارسال در سفارش‌ها استفاده شده و قابل حذف نیست.');
        }

        $shipping->delete();

        return redirect()->route('admin.shipping.index')->with('success', 'روش ارسال حذف شد.');
    }
}
