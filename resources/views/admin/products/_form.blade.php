@php
    $product = $product ?? null;
    $isEdit = isset($product);
    $apparelCategoryIds = $categories->filter(
        fn ($category) => (bool) preg_match('/(پوشاک|کفش|لباس|پیراهن|کت|هودی|شلوار)/u', $category->name)
    )->pluck('id')->values();

    $existingAttributes = $product
        ? $product->attributeValues->map(fn($av) => [
            'id' => $av->product_attribute_id,
            'name' => $av->attribute->name,
            'value' => $av->value,
        ])->values()
        : collect();

    $customFields = old('custom_fields', $isEdit && $product->relationLoaded('customFields') ? $product->customFields->sortBy('sort_order')->map(fn($f) => [
        'label' => $f->label,
        'type' => $f->type,
        'options' => is_array($f->options) ? implode("\n", $f->options) : ($f->options ?? ''),
        'is_required' => $f->is_required,
        'sort_order' => $f->sort_order,
    ])->values()->toArray() : []);
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

        {{-- موجودی واریانت‌ها (سایز + رنگ) --}}
        <div class="md:col-span-2">
            <div class="rounded-lg border border-zinc-200 bg-white" id="variants-card" style="display: none;">
                <div class="border-b border-zinc-100 bg-zinc-50 px-4 py-3">
                    <strong class="text-sm font-semibold text-zinc-800">موجودی بر اساس سایز و رنگ</strong>
                    <p class="mt-0.5 text-xs text-zinc-500">برای هر ترکیب، موجودی جداگانه وارد کنید.</p>
                </div>
                <div class="p-4">
                    <div id="variants-matrix" class="overflow-x-auto"></div>
                </div>
            </div>
        </div>

        <x-admin.size-chart-editor
            :value="$product?->size_chart"
            :apparel-category-ids="$apparelCategoryIds"
        />
        @error('size_chart')<p class="admin-field-error md:col-span-2">{{ $message }}</p>@enderror
    </x-admin.form-section>

    {{-- مشخصات فنی (Attributes) - فقط اگه Attribute توی دیتابیس باشه --}}
    @if($attributes->isNotEmpty())
    <x-admin.form-section title="مشخصات فنی (اختیاری)">
        <div class="md:col-span-2">
            <p class="text-xs text-zinc-500 mb-3">اگه می‌خوای این محصول مشخصات فنی داشته باشه، اضافه کن. در غیر این صورت خالی بذار.</p>
            
            <div id="attributes-container" class="space-y-3">
                @foreach($existingAttributes as $idx => $attr)
                    <div class="attribute-row flex gap-3 items-start" data-index="{{ $idx }}">
                        <select name="attributes[{{ $idx }}][attribute_id]" class="admin-select flex-1 min-w-0">
                            <option value="">انتخاب ویژگی...</option>
                            @foreach($attributes as $a)
                                <option value="{{ $a->id }}" @selected($attr['id'] == $a->id)>{{ $a->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="attributes[{{ $idx }}][value]" value="{{ $attr['value'] }}" 
                            class="admin-input flex-1 min-w-0" placeholder="مقدار (مثلاً: ۸ گیگابایت)">
                        <button type="button" class="text-rose-500 hover:text-rose-700 p-2 shrink-0 transition-colors remove-attr-btn" title="حذف">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                @endforeach
            </div>

            <button type="button" id="add-attribute-btn" class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-emerald-700 hover:text-emerald-800 transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                افزودن ویژگی
            </button>

            @error('attributes')<p class="admin-field-error mt-2">{{ $message }}</p>@enderror
            @error('attributes.*.attribute_id')<p class="admin-field-error mt-1">{{ $message }}</p>@enderror
            @error('attributes.*.value')<p class="admin-field-error mt-1">{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>
    @endif
       
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
            <label class="admin-field-label" for="stock">موجودی کل <span class="text-rose-500">*</span></label>
            <x-admin.number-stepper
                name="stock"
                id="stock"
                :value="old('stock', $product?->stock ?? 0)"
                :min="0"
                class="w-full max-w-[12rem]"
                required
            />
            @error('stock')<p class="admin-field-error">{{ $message }}</p>@enderror
            <p class="admin-field-hint" id="stock-hint">اگر سایز/رنگ وارد کنید، این عدد خودکار از مجموع واریانت‌ها محاسبه می‌شود.</p>
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

    {{-- ═══ فیلدهای سفارشی محصول ═══ --}}
    <x-admin.form-section title="فیلدهای سفارشی (قبل از سبد)">
        <div class="md:col-span-2">
            <p class="text-xs text-zinc-500 mb-3">
                این فیلدها در صفحه محصول، <strong>قبل از دکمه «افزودن به سبد»</strong> به مشتری نمایش داده می‌شوند (مثل: مدت زمان اشتراک، ایمیل اکانت، رمز عبور و...)
            </p>

            <div id="custom-fields-container" class="space-y-3">
                @forelse($customFields as $index => $field)
                    <div class="custom-field-row rounded-lg border border-zinc-200 bg-white p-4" data-index="{{ $index }}">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                            <div class="md:col-span-4">
                                <label class="admin-field-label">عنوان فیلد <span class="text-rose-500">*</span></label>
                                <input type="text" name="custom_fields[{{ $index }}][label]" value="{{ $field['label'] ?? '' }}" class="admin-input w-full" placeholder="مثال: مدت زمان اشتراک" required>
                            </div>
                            <div class="md:col-span-3">
                                <label class="admin-field-label">نوع فیلد <span class="text-rose-500">*</span></label>
                                <select name="custom_fields[{{ $index }}][type]" class="admin-select w-full cf-type" required>
                                    <option value="text" {{ ($field['type'] ?? 'text') == 'text' ? 'selected' : '' }}>متن تک‌خطی</option>
                                    <option value="email" {{ ($field['type'] ?? '') == 'email' ? 'selected' : '' }}>ایمیل</option>
                                    <option value="password" {{ ($field['type'] ?? '') == 'password' ? 'selected' : '' }}>رمز عبور</option>
                                    <option value="select" {{ ($field['type'] ?? '') == 'select' ? 'selected' : '' }}>لیست کشویی</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="admin-field-label">ترتیب</label>
                                <input type="number" name="custom_fields[{{ $index }}][sort_order]" value="{{ $field['sort_order'] ?? $index }}" class="admin-input w-full" min="0">
                            </div>
                            <div class="md:col-span-3 flex items-end gap-3">
                                <label class="flex items-center gap-2 cursor-pointer mb-2">
                                    <input type="checkbox" name="custom_fields[{{ $index }}][is_required]" value="1" class="admin-checkbox" {{ !empty($field['is_required']) ? 'checked' : '' }}>
                                    <span class="text-sm text-zinc-700">اجباری</span>
                                </label>
                                <button type="button" class="text-rose-500 hover:text-rose-700 p-2 transition-colors remove-cf-btn" title="حذف فیلد">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                            <div class="md:col-span-12 cf-options {{ ($field['type'] ?? 'text') !== 'select' ? 'hidden' : '' }}">
                                <label class="admin-field-label">گزینه‌ها <span class="text-zinc-400 text-xs font-normal">(هر خط یک گزینه)</span></label>
                                <textarea name="custom_fields[{{ $index }}][options]" rows="3" class="admin-input w-full resize-y" placeholder="1 ماهه&#10;3 ماهه&#10;6 ماهه">{{ $field['options'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-zinc-400 text-sm" id="no-custom-fields">
                        هنوز فیلد سفارشی تعریف نشده است.
                    </div>
                @endforelse
            </div>

            <button type="button" id="add-custom-field-btn" class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-emerald-700 hover:text-emerald-800 transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                افزودن فیلد سفارشی
            </button>

            @error('custom_fields')<p class="admin-field-error mt-2">{{ $message }}</p>@enderror
            @error('custom_fields.*.label')<p class="admin-field-error mt-1">عنوان فیلدها نمی‌تواند خالی باشد.</p>@enderror
        </div>
    </x-admin.form-section>
    {{-- ═══ پایان فیلدهای سفارشی ═══ --}}

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

@push('scripts')
<script>
(function() {
    // ─── Variants Logic ─────────────────────────────
    const sizesInput = document.querySelector('textarea[name="sizes"]');
    const colorsInput = document.querySelector('textarea[name="colors"]');
    const variantsCard = document.getElementById('variants-card');
    const matrixContainer = document.getElementById('variants-matrix');
    const stockHint = document.getElementById('stock-hint');

    const existingVariants = @json(
        ($product ?? null)
            ? $product->variants->keyBy(fn($v) => ($v->size ?? '_') . '|' . ($v->color ?? '_'))
            : []
    );

    function parseLines(input) {
        if (!input || !input.value) return [];
        return input.value.split(/[\r\n,،]+/).map(s => s.trim()).filter(s => s);
    }

    function buildMatrix() {
        const sizes = parseLines(sizesInput);
        const colors = parseLines(colorsInput);

        if (sizes.length === 0 && colors.length === 0) {
            variantsCard.style.display = 'none';
            matrixContainer.innerHTML = '';
            if (stockHint) stockHint.style.display = 'none';
            return;
        }

        variantsCard.style.display = 'block';
        if (stockHint) stockHint.style.display = 'block';

        let html = '<table class="min-w-full text-sm"><thead><tr class="border-b border-zinc-200">';
        html += '<th class="py-2 px-3 text-right font-medium text-zinc-600">سایز</th>';
        html += '<th class="py-2 px-3 text-right font-medium text-zinc-600">رنگ</th>';
        html += '<th class="py-2 px-3 text-right font-medium text-zinc-600">موجودی</th>';
        html += '</tr></thead><tbody class="divide-y divide-zinc-100">';

        const combinations = [];
        if (sizes.length > 0 && colors.length > 0) {
            sizes.forEach(size => colors.forEach(color => combinations.push({size, color})));
        } else if (sizes.length > 0) {
            sizes.forEach(size => combinations.push({size, color: null}));
        } else if (colors.length > 0) {
            colors.forEach(color => combinations.push({size: null, color}));
        }

        combinations.forEach(({size, color}) => {
            const key = (size ?? '_') + '|' + (color ?? '_');
            const existing = existingVariants[key] ?? null;
            const stock = existing ? existing.stock : 0;

            html += `<tr>
                <td class="py-2 px-3 text-zinc-700">${size ?? '-'}</td>
                <td class="py-2 px-3 text-zinc-700">${color ?? '-'}</td>
                <td class="py-2 px-3">
                    <input type="number" name="variant_stock[${key}]" value="${stock}" min="0"
                        class="admin-input w-28 text-left" dir="ltr">
                    <input type="hidden" name="variant_size[${key}]" value="${size ?? ''}">
                    <input type="hidden" name="variant_color[${key}]" value="${color ?? ''}">
                </td>
            </tr>`;
        });

        html += '</tbody></table>';
        matrixContainer.innerHTML = html;
    }

    if (sizesInput && colorsInput) {
        sizesInput.addEventListener('input', buildMatrix);
        colorsInput.addEventListener('input', buildMatrix);
        buildMatrix();
    }

    // ─── Attributes Logic ───────────────────────────
    const attrContainer = document.getElementById('attributes-container');
    const addAttrBtn = document.getElementById('add-attribute-btn');
    const allAttributes = @json($attributes->map(fn($a) => ['id' => $a->id, 'name' => $a->name]));

    function getNextAttrIndex() {
        const rows = attrContainer.querySelectorAll('.attribute-row');
        let max = -1;
        rows.forEach(row => {
            const idx = parseInt(row.dataset.index);
            if (idx > max) max = idx;
        });
        return max + 1;
    }

    function createAttributeRow(index, selectedId = '', value = '') {
        const div = document.createElement('div');
        div.className = 'attribute-row flex gap-3 items-start';
        div.dataset.index = index;

        let optionsHtml = '<option value="">انتخاب ویژگی...</option>';
        allAttributes.forEach(attr => {
            const selected = attr.id == selectedId ? 'selected' : '';
            optionsHtml += `<option value="${attr.id}" ${selected}>${attr.name}</option>`;
        });

        div.innerHTML = `
            <select name="attributes[${index}][attribute_id]" class="admin-select flex-1 min-w-0">
                ${optionsHtml}
            </select>
            <input type="text" name="attributes[${index}][value]" value="${value}" 
                class="admin-input flex-1 min-w-0" placeholder="مقدار (مثلاً: ۸ گیگابایت)">
            <button type="button" class="text-rose-500 hover:text-rose-700 p-2 shrink-0 transition-colors remove-attr-btn" title="حذف">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        `;

        div.querySelector('.remove-attr-btn').addEventListener('click', function() {
            div.remove();
        });

        return div;
    }

    if (addAttrBtn && attrContainer) {
        addAttrBtn.addEventListener('click', function() {
            const idx = getNextAttrIndex();
            attrContainer.appendChild(createAttributeRow(idx));
        });

        // Bind remove buttons for existing rows
        attrContainer.querySelectorAll('.remove-attr-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                btn.closest('.attribute-row').remove();
            });
        });
    }

    // ─── Custom Fields Logic ──────────────────────────
    const cfContainer = document.getElementById('custom-fields-container');
    const addCfBtn = document.getElementById('add-custom-field-btn');
    let cfIndex = {{ count($customFields) }};

    function getNextCfIndex() {
        const rows = cfContainer.querySelectorAll('.custom-field-row');
        let max = -1;
        rows.forEach(row => {
            const idx = parseInt(row.dataset.index);
            if (!isNaN(idx) && idx > max) max = idx;
        });
        return max + 1;
    }

    function createCustomFieldRow(index) {
        const div = document.createElement('div');
        div.className = 'custom-field-row rounded-lg border border-zinc-200 bg-white p-4';
        div.dataset.index = index;

        div.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-4">
                    <label class="admin-field-label">عنوان فیلد <span class="text-rose-500">*</span></label>
                    <input type="text" name="custom_fields[${index}][label]" class="admin-input w-full" placeholder="مثال: ایمیل اکانت" required>
                </div>
                <div class="md:col-span-3">
                    <label class="admin-field-label">نوع فیلد <span class="text-rose-500">*</span></label>
                    <select name="custom_fields[${index}][type]" class="admin-select w-full cf-type" required>
                        <option value="text">متن تک‌خطی</option>
                        <option value="email">ایمیل</option>
                        <option value="password">رمز عبور</option>
                        <option value="select">لیست کشویی</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="admin-field-label">ترتیب</label>
                    <input type="number" name="custom_fields[${index}][sort_order]" class="admin-input w-full" value="${index}" min="0">
                </div>
                <div class="md:col-span-3 flex items-end gap-3">
                    <label class="flex items-center gap-2 cursor-pointer mb-2">
                        <input type="checkbox" name="custom_fields[${index}][is_required]" value="1" class="admin-checkbox">
                        <span class="text-sm text-zinc-700">اجباری</span>
                    </label>
                    <button type="button" class="text-rose-500 hover:text-rose-700 p-2 transition-colors remove-cf-btn" title="حذف فیلد">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
                <div class="md:col-span-12 cf-options hidden">
                    <label class="admin-field-label">گزینه‌ها <span class="text-zinc-400 text-xs font-normal">(هر خط یک گزینه)</span></label>
                    <textarea name="custom_fields[${index}][options]" rows="3" class="admin-input w-full resize-y" placeholder="1 ماهه&#10;3 ماهه&#10;6 ماهه"></textarea>
                </div>
            </div>
        `;

        const select = div.querySelector('.cf-type');
        select.addEventListener('change', function() {
            const optionsBox = div.querySelector('.cf-options');
            if (this.value === 'select') {
                optionsBox.classList.remove('hidden');
            } else {
                optionsBox.classList.add('hidden');
            }
        });

        div.querySelector('.remove-cf-btn').addEventListener('click', function() {
            div.remove();
            if (cfContainer.querySelectorAll('.custom-field-row').length === 0) {
                cfContainer.innerHTML = '<div class="text-center py-6 text-zinc-400 text-sm" id="no-custom-fields">هنوز فیلد سفارشی تعریف نشده است.</div>';
            }
        });

        return div;
    }

    if (addCfBtn && cfContainer) {
        addCfBtn.addEventListener('click', function() {
            const emptyMsg = document.getElementById('no-custom-fields');
            if (emptyMsg) emptyMsg.remove();

            const idx = getNextCfIndex();
            cfContainer.appendChild(createCustomFieldRow(idx));
        });

        // Bind change and remove for existing rows
        cfContainer.querySelectorAll('.cf-type').forEach(select => {
            select.addEventListener('change', function() {
                const optionsBox = this.closest('.custom-field-row').querySelector('.cf-options');
                if (this.value === 'select') {
                    optionsBox.classList.remove('hidden');
                } else {
                    optionsBox.classList.add('hidden');
                }
            });
        });

        cfContainer.querySelectorAll('.remove-cf-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                btn.closest('.custom-field-row').remove();
                if (cfContainer.querySelectorAll('.custom-field-row').length === 0) {
                    cfContainer.innerHTML = '<div class="text-center py-6 text-zinc-400 text-sm" id="no-custom-fields">هنوز فیلد سفارشی تعریف نشده است.</div>';
                }
            });
        });
    }
})();
</script>
@endpush