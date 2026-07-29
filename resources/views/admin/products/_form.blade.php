@php
    $product = $product ?? null;
    $isEdit = isset($product);
    $apparelCategoryIds = $categories->filter(
        fn ($category) => (bool) preg_match('/(پوشاک|کفش|لباس|پیراهن|کت|هودی|شلوار)/u', $category->name)
    )->pluck('id')->values();
@endphp

@if($errors->any())
    <div class="admin-alert-error mb-6">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <div>
            <p class="font-bold">لطفاً خطاهای فرم را بررسی کنید:</p>
            <ul class="mt-1 list-inside list-disc text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="space-y-8">
    <x-admin.form-section title="اطلاعات اصلی">
        <div>
            <label class="admin-field-label" for="category_id">دسته‌بندی <span class="text-rose-500">*</span></label>
            <select name="category_id" id="category_id" class="admin-select w-full" required>
                <option value="">انتخاب دسته‌بندی...</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id', $product?->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            @error('category_id')<p class="admin-field-error">{{ $message }}</p>@enderror
            @if($categories->isEmpty())
                <p class="admin-field-hint text-amber-600">هنوز دسته‌بندی فعالی وجود ندارد. <a href="{{ route('admin.categories.create') }}" class="font-bold underline">ایجاد دسته‌بندی</a></p>
            @endif
        </div>
        <div>
            <label class="admin-field-label" for="brand_id">برند</label>
            <select name="brand_id" id="brand_id" class="admin-select w-full">
                <option value="">بدون برند</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" @selected(old('brand_id', $product?->brand_id ?? '') == $brand->id)>{{ $brand->name }}</option>
                @endforeach
            </select>
            @error('brand_id')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="admin-field-label" for="name">نام محصول <span class="text-rose-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name', $product?->name ?? '') }}" class="admin-input w-full" placeholder="مثلاً: گوشی سامسونگ گلکسی S24" required>
            @error('name')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="admin-field-label" for="slug">نشانی اینترنتی (اسلاگ)</label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $product?->slug ?? '') }}" placeholder="خالی بگذارید تا خودکار ساخته شود" dir="ltr" class="admin-input w-full text-left">
            @error('slug')<p class="admin-field-error">{{ $message }}</p>@enderror
            <p class="admin-field-hint">فقط حروف انگلیسی کوچک، اعداد و خط تیره</p>
        </div>
        <div>
            <label class="admin-field-label" for="sku">کد SKU <span class="text-rose-500">*</span></label>
            <input type="text" name="sku" id="sku" value="{{ old('sku', $product?->sku ?? '') }}" class="admin-input w-full" placeholder="مثلاً: PRD-001" dir="ltr" required>
            @error('sku')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="گزینه‌های خرید">
        <div>
            <label class="admin-field-label" for="sizes">سایزهای موجود</label>
            <textarea name="sizes" id="sizes" rows="3" class="admin-input w-full resize-y" placeholder="هر سایز در یک خط&#10;مثلاً: S&#10;M&#10;L&#10;XL">{{ old('sizes', isset($product) && $product->sizes ? implode("\n", $product->sizes) : '') }}</textarea>
            @error('sizes')<p class="admin-field-error">{{ $message }}</p>@enderror
            <p class="admin-field-hint">اختیاری — اگر خالی باشد، انتخاب سایز در فروشگاه نمایش داده نمی‌شود</p>
        </div>
        <div>
            <label class="admin-field-label" for="colors">رنگ‌های موجود</label>
            <textarea name="colors" id="colors" rows="3" class="admin-input w-full resize-y" placeholder="هر رنگ در یک خط&#10;مثلاً: مشکی&#10;سفید&#10;آبی">{{ old('colors', isset($product) && $product->colors ? implode("\n", $product->colors) : '') }}</textarea>
            @error('colors')<p class="admin-field-error">{{ $message }}</p>@enderror
            <p class="admin-field-hint">اختیاری — اگر خالی باشد، انتخاب رنگ در فروشگاه نمایش داده نمی‌شود</p>
        </div>

        <x-admin.size-chart-editor
            :value="$product?->size_chart"
            :apparel-category-ids="$apparelCategoryIds"
        />
        @error('size_chart')<p class="admin-field-error md:col-span-2">{{ $message }}</p>@enderror
    </x-admin.form-section>

    <x-admin.form-section title="قیمت و موجودی">
        <div>
            <label class="admin-field-label" for="price">قیمت (تومان) <span class="text-rose-500">*</span></label>
            <input type="text" name="price" id="price" value="{{ old('price', $product ? format_number($product->price, false) : '') }}" data-price-input class="admin-input w-full" placeholder="۰" dir="ltr" inputmode="numeric" required>
            @error('price')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="admin-field-label" for="compare_price">قیمت قبل از تخفیف (تومان)</label>
            <input type="text" name="compare_price" id="compare_price" value="{{ old('compare_price', $product?->compare_price ? format_number($product->compare_price, false) : '') }}" data-price-input class="admin-input w-full" placeholder="اختیاری" dir="ltr" inputmode="numeric">
            @error('compare_price')<p class="admin-field-error">{{ $message }}</p>@enderror
            <p class="admin-field-hint">برای نمایش خط‌خورده روی فروشگاه</p>
        </div>
        <div>
            <label class="admin-field-label" for="stock">موجودی <span class="text-rose-500">*</span></label>
            <x-admin.number-stepper
                name="stock"
                id="stock"
                :value="old('stock', $product?->stock ?? 0)"
                :min="0"
                class="w-full max-w-[12rem]"
                required
            />
            @error('stock')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="admin-field-label" for="weight">وزن (گرم)</label>
            <input type="text" name="weight" id="weight" value="{{ old('weight', $product?->weight ? to_persian_digits((string) $product->weight) : '') }}" data-numeric-input class="admin-input w-full" placeholder="اختیاری" dir="ltr" inputmode="numeric">
            @error('weight')<p class="admin-field-error">{{ $message }}</p>@enderror
            <p class="admin-field-hint">برای محاسبه هزینه ارسال</p>
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="تصاویر">
        <div class="md:col-span-2">
            <label class="admin-field-label">مدیریت تصاویر @unless($isEdit)<span class="text-rose-500">*</span>@endunless</label>
            <x-admin.product-image-manager :product="$product" :required="! $isEdit" />
            <p class="admin-field-hint mt-2">فرمت‌های مجاز: JPG، PNG، WebP — حداکثر ۲ مگابایت برای هر تصویر</p>
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="توضیحات">
        <div class="md:col-span-2">
            <label class="admin-field-label" for="short_description">توضیح کوتاه</label>
            <input type="text" name="short_description" id="short_description" value="{{ old('short_description', $product?->short_description ?? '') }}" class="admin-input w-full" placeholder="خلاصه‌ای کوتاه برای نمایش در لیست محصولات" maxlength="500">
            @error('short_description')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="admin-field-label" for="description">توضیحات کامل</label>
            <textarea name="description" id="description" rows="5" class="admin-input w-full resize-y" placeholder="جزئیات، مشخصات و ویژگی‌های محصول...">{{ old('description', $product?->description ?? '') }}</textarea>
            @error('description')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="بهینه‌سازی موتور جستجو (SEO)">
        <div>
            <label class="admin-field-label" for="meta_title">عنوان SEO</label>
            <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $product?->meta_title ?? '') }}" class="admin-input w-full" placeholder="عنوانی برای نتایج گوگل">
            @error('meta_title')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="admin-field-label" for="meta_description">توضیح SEO</label>
            <input type="text" name="meta_description" id="meta_description" value="{{ old('meta_description', $product?->meta_description ?? '') }}" class="admin-input w-full" placeholder="توضیح کوتاه برای موتورهای جستجو">
            @error('meta_description')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>

    <section class="admin-form-section">
        <h3 class="admin-form-section-title">تنظیمات نمایش</h3>
        <div class="flex flex-wrap gap-6">
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" id="is_active" class="admin-checkbox" @checked(old('is_active', $product?->is_active ?? true))>
                <span class="text-sm font-medium text-zinc-700">فعال — نمایش در فروشگاه</span>
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1" id="is_featured" class="admin-checkbox" @checked(old('is_featured', $product?->is_featured ?? false))>
                <span class="text-sm font-medium text-zinc-700">ویژه — نمایش در بخش محصولات ویژه</span>
            </label>
        </div>
    </section>
</div>
