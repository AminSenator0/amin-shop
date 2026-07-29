<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $query = Faq::query();

        if ($request->filled('search')) {
            $query->where('question', 'like', '%'.$request->search.'%');
        }

        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $faqs = $query->orderBy('sort_order')->paginate(15)->withQueryString();

        $stats = [
            'total' => Faq::count(),
            'active' => Faq::where('is_active', true)->count(),
            'inactive' => Faq::where('is_active', false)->count(),
        ];

        return view('admin.faqs.index', compact('faqs', 'stats'));
    }

    public function create()
    {
        return view('admin.faqs.create');
    }

    public function store(FaqRequest $request)
    {
        Faq::create([
            ...$request->validated(),
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'سوال متداول ایجاد شد.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    public function update(FaqRequest $request, Faq $faq)
    {
        $faq->update([
            ...$request->validated(),
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'سوال متداول به‌روزرسانی شد.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'سوال متداول حذف شد.');
    }
}
