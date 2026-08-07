@php
    use App\Services\ProductFormUploadCache;

    $uploadCache = app(ProductFormUploadCache::class)->get();
    $cachedSort = is_array($uploadCache) ? ($uploadCache['image_sort'] ?? null) : null;

    $defaultSort = '[]';
    if ($product && $product->images->isNotEmpty()) {
        $defaultSort = json_encode($product->images->map(fn($img) => 'existing:' . $img->id)->values()->all());
    }

    $oldSort = old('image_sort');
    if (is_array($oldSort)) {
        $sortSource = json_encode($oldSort);
    } elseif (is_string($oldSort) && $oldSort !== '') {
        $sortSource = $oldSort;
    } else {
        $sortSource = $cachedSort ?: $defaultSort;
    }

    $initialSort = json_decode($sortSource, true) ?: [];
    $initialPrimary = old('primary_image', is_array($uploadCache) ? ($uploadCache['primary_image'] ?? null) : null);
    $removedImages = array_map('intval', (array) old('remove_images', is_array($uploadCache) ? ($uploadCache['remove_images'] ?? []) : []));

    $existingMap = [];
    if ($product) {
        if (! $product->relationLoaded('images')) {
            $product->load('images');
        }

        foreach ($product->images as $image) {
            $existingMap[$image->id] = [
                'type' => 'existing',
                'id' => $image->id,
                'url' => asset('storage/'.$image->path),
                'name' => basename($image->path),
            ];
        }
    }

    $cachedMap = [];
    foreach (is_array($uploadCache) ? ($uploadCache['items'] ?? []) : [] as $item) {
        $cachedMap[$item['key']] = [
            'type' => 'cached',
            'key' => $item['key'],
            'url' => route('admin.products.form-upload', $item['key']),
            'name' => $item['name'] ?? basename($item['path']),
        ];
    }

    $initialItems = [];

    if ($initialSort !== []) {
        foreach ($initialSort as $entry) {
            if (! is_string($entry)) {
                continue;
            }

            if (str_starts_with($entry, 'existing:')) {
                $id = (int) substr($entry, 9);

                if (isset($existingMap[$id]) && ! in_array($id, $removedImages, true)) {
                    $initialItems[] = $existingMap[$id];
                }

                continue;
            }

            if (str_starts_with($entry, 'cached:')) {
                $key = substr($entry, 7);

                if (isset($cachedMap[$key])) {
                    $initialItems[] = $cachedMap[$key];
                }
            }
        }
    } else {
        foreach ($existingMap as $id => $image) {
            if (! in_array($id, $removedImages, true)) {
                $initialItems[] = $image;
            }
        }

        foreach ($cachedMap as $image) {
            $initialItems[] = $image;
        }
    }

    if (! $initialPrimary && $initialItems !== []) {
        if ($product?->image) {
            foreach ($initialItems as $item) {
                if ($item['type'] === 'existing') {
                    $imageModel = $product->images->firstWhere('id', $item['id']);

                    if ($imageModel && $product->image === $imageModel->path) {
                        $initialPrimary = 'existing:'.$item['id'];
                        break;
                    }
                }
            }
        }

        if (! $initialPrimary) {
            $first = $initialItems[0];
            $initialPrimary = $first['type'].':'.($first['id'] ?? $first['key']);
        }
    }

    $fallbackId = 'img-fb-' . ($product?->id ?? 'new');
    $managerConfig = json_encode([
        'required' => $required,
        'initialItems' => array_values($initialItems),
        'initialPrimary' => $initialPrimary,
        'removedImages' => $removedImages,
    ], JSON_UNESCAPED_UNICODE);
@endphp

<style>
    [x-cloak] { display: none !important; }
</style>

{{-- DEBUG — موقت — بعداً پاک کن --}}
{{-- <div style="background:#ef4444;color:white;padding:12px;font-family:monospace;font-size:12px;">
    DEBUG: items={{ count($initialItems) }}, config={{ $managerConfig }}
</div> --}}

<div
    {{ $attributes->merge(['class' => 'admin-image-manager']) }}
    x-data="productImageManager({{ $managerConfig }})"
    x-init="console.log('Alpine init, items:', items.length); var fb = document.getElementById('{{ $fallbackId }}'); if(fb) fb.style.display='none';"
    x-cloak
>
    <input type="hidden" name="image_sort" x-ref="sortInput" value="{{ $sortSource }}">
    <input type="hidden" name="primary_image" x-ref="primaryInput" value="{{ old('primary_image', is_array($uploadCache) ? ($uploadCache['primary_image'] ?? '') : '') }}">

    <template x-for="id in removedImages" :key="'remove-' + id">
        <input type="hidden" name="remove_images[]" :value="id">
    </template>

    <input type="file" name="images[]" multiple accept="image/*" class="sr-only" x-ref="fileInput">

    @if($errors->any() && count($initialItems) > 0 && collect($initialItems)->contains(fn ($item) => $item['type'] === 'cached'))
        <div class="admin-alert-success mb-4 text-sm">
            {{ collect($initialItems)->where('type', 'cached')->count() }} تصویر از بارگذاری قبلی حفظ شده است. نیازی به انتخاب مجدد نیست.
        </div>
    @endif

    <div class="admin-image-manager-toolbar">
        <button type="button" class="admin-btn-secondary text-xs" @click="$refs.picker.click()">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            افزودن تصویر
        </button>
        <input type="file" accept="image/*" multiple class="sr-only" x-ref="picker" @change="addFiles($event)">
        <p class="admin-field-hint">ترتیب نمایش از راست به چپ است. تصویر «اصلی» در فروشگاه به‌عنوان تصویر پیش‌فرض استفاده می‌شود.</p>
    </div>

    <template x-if="items.length === 0">
        <div class="admin-image-manager-empty">
            <svg class="h-10 w-10 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            <p class="mt-2 text-sm font-medium text-zinc-500">هنوز تصویری اضافه نشده است</p>
            <p class="text-xs text-zinc-400">برای شروع، دکمه «افزودن تصویر» را بزنید</p>
        </div>
    </template>

    <div class="admin-image-manager-grid" x-show="items.length > 0">
        <template x-for="(item, index) in items" :key="item.uid">
            <div class="admin-image-card" :class="{ 'admin-image-card-primary': isPrimary(item) }">
                <div class="admin-image-card-preview">
                    <img :src="item.url" :alt="item.name" class="admin-image-card-img">
                    <span class="admin-image-order" x-text="toPersian(index + 1)"></span>
                    <span class="admin-image-primary-badge" x-show="isPrimary(item)">اصلی</span>
                </div>
                <div class="admin-image-card-body">
                    <p class="admin-image-card-name" x-text="item.name" :title="item.name"></p>
                    <div class="admin-image-card-actions">
                        <button type="button" class="admin-image-action" @click="moveLeft(index)" :disabled="index === 0" title="انتقال به راست">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </button>
                        <button type="button" class="admin-image-action" @click="moveRight(index)" :disabled="index === items.length - 1" title="انتقال به چپ">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <button type="button" class="admin-image-action admin-image-action-primary" @click="setPrimary(item)" :class="{ 'is-active': isPrimary(item) }" title="تنظیم به عنوان اصلی">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                        </button>
                        <button type="button" class="admin-image-action admin-image-action-danger" @click="removeItem(item, index)" title="حذف">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    @error('images')
        <p class="admin-field-error mt-2">{{ $message }}</p>
    @enderror
    @error('images.*')
        <p class="admin-field-error mt-2">{{ $message }}</p>
    @enderror
    @error('image_sort')
        <p class="admin-field-error mt-2">{{ $message }}</p>
    @enderror
</div>

{{-- Fallback pure HTML — وقتی Alpine fail می‌شه --}}
@if(count($initialItems) > 0)
<div id="{{ $fallbackId }}" class="admin-image-manager-grid" style="margin-top:1rem;">
    @foreach($initialItems as $item)
        <div class="admin-image-card">
            <div class="admin-image-card-preview">
                <img src="{{ $item['url'] }}" alt="{{ $item['name'] }}" class="admin-image-card-img">
            </div>
            <div class="admin-image-card-body">
                <p class="admin-image-card-name">{{ $item['name'] }}</p>
            </div>
        </div>
    @endforeach
</div>
@endif