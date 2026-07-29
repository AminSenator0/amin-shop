<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Models\Brand;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function __construct(private FileUploadService $uploader) {}

    public function index(Request $request)
    {
        $query = Brand::withCount('products');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $brands = $query->latest()->paginate(15)->withQueryString();

        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(BrandRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->uploader->upload($request->file('logo'), 'brand_logo');
        }

        Brand::create($data);

        return redirect()->route('admin.brands.index')->with('success', 'برند ایجاد شد.');
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(BrandRequest $request, Brand $brand)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->uploader->replace($request->file('logo'), $brand->logo, 'brand_logo');
        } elseif ($request->boolean('remove_logo')) {
            $this->uploader->delete($brand->logo);
            $data['logo'] = null;
        }

        $brand->update($data);

        return redirect()->route('admin.brands.index')->with('success', 'برند به‌روزرسانی شد.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->exists()) {
            return back()->with('error', 'این برند دارای محصول است و قابل حذف نیست.');
        }

        $this->uploader->delete($brand->logo);

        $brand->delete();

        return redirect()->route('admin.brands.index')->with('success', 'برند حذف شد.');
    }
}
