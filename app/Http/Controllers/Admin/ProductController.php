<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use App\Services\FileUploadService;
use App\Services\ProductFormUploadCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct(
        private FileUploadService $uploader,
        private ProductFormUploadCache $uploadCache,
    ) {}

    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->boolean('low_stock')) {
            $query->where('stock', '<', 5);
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        if (! session()->hasOldInput()) {
            $this->uploadCache->clear();
        }

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $attributes = ProductAttribute::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.products.create', compact('categories', 'brands', 'attributes'));
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        unset($data['images'], $data['image_sort'], $data['primary_image'], $data['remove_images']);

        $data['slug'] = $this->resolveSlug($request, $data['name']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sizes'] = Product::parseOptionsList($request->input('sizes'));
        $data['colors'] = Product::parseOptionsList($request->input('colors'));
        $data['size_chart'] = Product::parseSizeChart($request->input('size_chart'));

        DB::transaction(function () use ($data, $request) {
            $product = Product::create($data);
            $this->syncProductImages($product, $request);
            $this->syncVariants($product, $request);
            $this->syncAttributes($product, $request);
            $this->syncCustomFields($product, $request); // ← اضافه شده
        });
        $this->uploadCache->clear();

        return redirect()->route('admin.products.index')->with('success', 'محصول ایجاد شد.');
    }

    public function edit(Product $product)
    {
        $cache = $this->uploadCache->get();

        if (! session()->hasOldInput()) {
            $this->uploadCache->clear();
        } elseif ($cache !== null && ($cache['product_id'] ?? null) !== $product->id) {
            $this->uploadCache->clear();
        }

        $product->load('images', 'variants', 'attributeValues.attribute', 'customFields'); // ← customFields اضافه شد
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $attributes = ProductAttribute::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands', 'attributes'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();
        unset($data['images'], $data['image_sort'], $data['primary_image'], $data['remove_images']);

        $data['slug'] = $request->filled('slug')
            ? $request->slug
            : $this->resolveSlug($request, $data['name'], $product);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sizes'] = Product::parseOptionsList($request->input('sizes'));
        $data['colors'] = Product::parseOptionsList($request->input('colors'));
        $data['size_chart'] = Product::parseSizeChart($request->input('size_chart'));

        DB::transaction(function () use ($product, $data, $request) {
            $product->update($data);
            $this->syncProductImages($product, $request);
            $this->syncVariants($product, $request);
            $this->syncAttributes($product, $request);
            $this->syncCustomFields($product, $request); // ← اضافه شده
        });
        $this->uploadCache->clear();

        return redirect()->route('admin.products.index')->with('success', 'محصول به‌روزرسانی شد.');
    }

    public function destroy(Product $product)
    {
        $imagePaths = $product->images->pluck('path')->all();
        if ($product->image) {
            $imagePaths[] = $product->image;
        }

        DB::transaction(function () use ($product) {
            $product->images()->delete();
            $product->delete();
        });

        foreach (array_unique($imagePaths) as $path) {
            $this->uploader->delete($path);
        }

        return redirect()->route('admin.products.index')->with('success', 'محصول حذف شد.');
    }

    public function destroyImage(Product $product, ProductImage $image)
    {
        if ($image->product_id !== $product->id) {
            abort(403);
        }

        $path = $image->path;

        DB::transaction(function () use ($product, $image) {
            $wasMain = $product->image === $image->path;
            $image->delete();

            if ($wasMain) {
                $product->update(['image' => $product->images()->first()?->path]);
            }
        });

        $this->uploader->delete($path);

        return back()->with('success', 'تصویر حذف شد.');
    }

    public function formUpload(string $key): Response
    {
        $path = $this->uploadCache->getPathByKey($key);

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }

    // ─── متد جدید: همگام‌سازی Custom Fields ──────────────────
    private function syncCustomFields(Product $product, Request $request): void
    {
        $fields = $request->input('custom_fields', []);

        // حذف فیلدهای قبلی
        $product->customFields()->delete();

        foreach ($fields as $index => $field) {
            $label = trim($field['label'] ?? '');
            if ($label === '') {
                continue;
            }

            $type = $field['type'] ?? 'text';
            $options = null;

            // اگر نوع select بود، گزینه‌ها رو از textarea پارس کن (هر خط یک گزینه)
            if ($type === 'select' && !empty($field['options'])) {
                $lines = preg_split('/[\r\n]+/', $field['options']);
                $options = array_values(array_filter(array_map('trim', $lines)));
                $options = $options !== [] ? $options : null;
            }

            $product->customFields()->create([
                'label' => $label,
                'type' => $type,
                'options' => $options,
                'is_required' => !empty($field['is_required']),
                'sort_order' => (int) ($field['sort_order'] ?? $index),
            ]);
        }
    }
    // ─── پایان متد جدید ───────────────────────────────────────

    // ─── متد Attribute (قبلی) ─────────────────────────────────
    private function syncAttributes(Product $product, Request $request): void
    {
        $attributes = $request->input('attributes', []);

        $product->attributeValues()->delete();

        foreach ($attributes as $attr) {
            $attributeId = $attr['attribute_id'] ?? null;
            $value = trim($attr['value'] ?? '');

            if ($attributeId && $value !== '') {
                $product->attributeValues()->create([
                    'product_attribute_id' => (int) $attributeId,
                    'value' => $value,
                ]);
            }
        }
    }
    // ─── پایان متد Attribute ─────────────────────────────────

    // ─── متد واریانت (قبلی) ──────────────────────────────────
    private function syncVariants(Product $product, Request $request): void
    {
        $sizes = $product->sizes ?? [];
        $colors = $product->colors ?? [];

        if (empty($sizes) && empty($colors)) {
            $product->variants()->delete();
            return;
        }

        $stocks = $request->input('variant_stock', []);
        $inputSizes = $request->input('variant_size', []);
        $inputColors = $request->input('variant_color', []);

        $syncedIds = [];

        foreach ($stocks as $key => $stock) {
            $size = $inputSizes[$key] ?? null;
            $color = $inputColors[$key] ?? null;

            if ($size === '') $size = null;
            if ($color === '') $color = null;

            if ($size && !in_array($size, $sizes, true)) continue;
            if ($color && !in_array($color, $colors, true)) continue;

            $variant = $product->variants()->updateOrCreate(
                ['size' => $size, 'color' => $color],
                ['stock' => max(0, (int) $stock)]
            );

            $syncedIds[] = $variant->id;
        }

        $product->variants()->whereNotIn('id', $syncedIds)->delete();
        $product->update(['stock' => $product->variants()->sum('stock')]);
    }
    // ─── پایان متد واریانت ───────────────────────────────────

    private function syncProductImages(Product $product, Request $request): void
    {
        $sort = json_decode($request->input('image_sort', '[]'), true) ?: [];
        $primaryToken = $request->input('primary_image');
        $removeIds = array_map('intval', (array) $request->input('remove_images', []));
        $newFiles = array_values($request->file('images', []) ?? []);

        foreach ($removeIds as $imageId) {
            $image = $product->images()->whereKey($imageId)->first();

            if (! $image) {
                continue;
            }

            if ($product->image === $image->path) {
                $product->update(['image' => null]);
            }

            $this->uploader->delete($image->path);
            $image->delete();
        }

        $resolved = [];

        foreach ($sort as $entry) {
            if (! is_string($entry)) {
                continue;
            }

            if (str_starts_with($entry, 'existing:')) {
                $imageId = (int) substr($entry, 9);
                $image = $product->images()->whereKey($imageId)->first();

                if ($image) {
                    $resolved[] = [
                        'source_token' => $entry,
                        'path' => $image->path,
                        'image' => $image,
                    ];
                }

                continue;
            }

            if (str_starts_with($entry, 'cached:')) {
                $key = substr($entry, 7);
                $tempPath = $this->uploadCache->getPathByKey($key);

                if (! $tempPath) {
                    continue;
                }

                $path = $this->uploadCache->promote($tempPath, $this->uploader);
                $image = $product->images()->create([
                    'path' => $path,
                    'sort_order' => 0,
                ]);

                $resolved[] = [
                    'source_token' => $entry,
                    'path' => $path,
                    'image' => $image,
                ];

                continue;
            }

            if (str_starts_with($entry, 'new:')) {
                $index = (int) substr($entry, 4);

                if (! isset($newFiles[$index])) {
                    continue;
                }

                $path = $this->uploader->upload($newFiles[$index], 'product');
                $image = $product->images()->create([
                    'path' => $path,
                    'sort_order' => 0,
                ]);

                $resolved[] = [
                    'source_token' => $entry,
                    'path' => $path,
                    'image' => $image,
                ];
            }
        }

        $keptIds = collect($resolved)
            ->pluck('image')
            ->map->id
            ->all();

        foreach ($product->images()->whereNotIn('id', $keptIds)->get() as $orphan) {
            if ($product->image === $orphan->path) {
                $product->update(['image' => null]);
            }

            $this->uploader->delete($orphan->path);
            $orphan->delete();
        }

        $primaryPath = null;

        foreach ($resolved as $index => $item) {
            $item['image']->update(['sort_order' => $index]);

            if ($primaryToken && $item['source_token'] === $primaryToken) {
                $primaryPath = $item['path'];
            }
        }

        if (! $primaryPath && $resolved !== []) {
            $primaryPath = $resolved[0]['path'];
        }

        if ($primaryPath) {
            $product->update(['image' => $primaryPath]);
        }
    }

    private function resolveSlug(Request $request, string $name, ?Product $product = null): string
    {
        if ($request->filled('slug')) {
            return $request->slug;
        }

        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $counter = 1;

        while (Product::where('slug', $slug)->when($product, fn ($q) => $q->where('id', '!=', $product->id))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}