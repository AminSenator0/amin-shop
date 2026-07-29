@php
    use App\Services\BannerFormUploadCache;

    $banner = $banner ?? null;
    $uploadCache = app(BannerFormUploadCache::class);
    $cached = $uploadCache->get();
    $cachedImageKey = $cached['key'] ?? null;
    $cachedPreviewUrl = $uploadCache->previewUrl();
    $existingImage = $cachedPreviewUrl ?? (isset($banner) && $banner->imageUrl() ? $banner->imageUrl() : null);
@endphp

@if($errors->any())
    <div class="admin-alert-error">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <div>
            <p class="font-bold">لطفاً خطاهای فرم را بررسی کنید</p>
            <ul class="mt-1 list-inside list-disc text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div
    class="space-y-6"
    x-data="{
        title: @js(old('title', $banner?->title ?? '')),
        description: @js(old('description', $banner?->description ?? '')),
        imagePreview: @js($existingImage),
        hasCachedImage: @js((bool) $cachedImageKey),
        pickImage() { this.$refs.imageInput.click(); },
        onImageChange(event) {
            const file = event.target.files?.[0];
            if (!file) return;
            if (this.imagePreview?.startsWith('blob:')) URL.revokeObjectURL(this.imagePreview);
            this.imagePreview = URL.createObjectURL(file);
            this.hasCachedImage = false;
        },
    }"
>
    @if($cachedImageKey)
        <input type="hidden" name="cached_image" value="{{ $cachedImageKey }}" x-ref="cachedImageInput">
    @endif

    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="sr-only" x-ref="imageInput" @change="onImageChange($event)">

    <section class="admin-banner-preview-wrap">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <p class="admin-banner-preview-label mb-0">پیش‌نمایش بنر در صفحه اصلی</p>
            <p class="text-xs text-emerald-600" x-show="hasCachedImage" x-cloak>تصویر انتخاب‌شده حفظ شده است</p>
        </div>

        <article class="admin-banner-preview-card">
            <div class="admin-banner-preview-image">
                <template x-if="imagePreview">
                    <div class="relative h-full w-full">
                        <img :src="imagePreview" alt="" class="h-full w-full object-cover">
                        <div class="admin-banner-preview-image-overlay">
                            <button type="button" class="admin-banner-preview-image-btn" @click="pickImage()">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                تغییر تصویر
                            </button>
                        </div>
                    </div>
                </template>
                <template x-if="!imagePreview">
                    <button type="button" class="admin-banner-preview-upload w-full" @click="pickImage()">
                        <svg class="h-8 w-8 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" /></svg>
                        <span class="text-sm font-bold text-zinc-600">انتخاب تصویر بنر</span>
                        <span class="text-xs text-zinc-400">JPG، PNG، WebP — حداکثر ۴ مگابایت</span>
                    </button>
                </template>
            </div>
            <div class="admin-banner-preview-body">
                <h3 class="admin-banner-preview-title" x-text="title || 'عنوان بنر شما'"></h3>
                <p class="admin-banner-preview-desc" x-show="description" x-text="description"></p>
                <p class="admin-banner-preview-desc is-placeholder" x-show="!description">توضیح کوتاه بنر (اختیاری)</p>
            </div>
        </article>

        <p class="mt-3 text-center text-xs text-zinc-400">بنرها در صفحه اصلی فروشگاه به صورت ۳ ستونه نمایش داده می‌شوند.</p>
    </section>

    <div class="flex items-center gap-3 md:hidden">
        <template x-if="imagePreview">
            <img :src="imagePreview" alt="" class="h-16 w-16 shrink-0 rounded-xl object-cover ring-2 ring-zinc-200">
        </template>
        <button type="button" class="admin-btn-secondary min-w-0 flex-1" @click="pickImage()">
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            <span class="truncate" x-text="imagePreview ? 'تغییر تصویر' : 'انتخاب تصویر'"></span>
        </button>
    </div>
    @error('image')<p class="admin-field-error">{{ $message }}</p>@enderror

    <div class="admin-slider-form-grid">
        <div class="admin-slider-panel">
            <div class="admin-slider-panel-head">
                <div class="admin-slider-panel-icon">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                </div>
                <div>
                    <p class="admin-slider-panel-title">محتوا</p>
                    <p class="admin-slider-panel-desc">متن و لینک بنر تبلیغاتی</p>
                </div>
            </div>

            <div>
                <label for="title" class="admin-field-label">عنوان <span class="text-rose-500">*</span></label>
                <input type="text" name="title" id="title" x-model="title" class="admin-input w-full" placeholder="مثلاً: ارسال رایگان" required>
                @error('title')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="admin-field-label">توضیح</label>
                <textarea name="description" id="description" rows="3" x-model="description" class="admin-input w-full resize-y" placeholder="توضیح کوتاه زیر عنوان بنر"></textarea>
                @error('description')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="link" class="admin-field-label">لینک مقصد</label>
                <input type="text" name="link" id="link" value="{{ old('link', $banner?->link ?? '') }}" class="admin-input w-full text-left" dir="ltr" placeholder="/products">
                <p class="admin-field-hint">خالی = صفحه محصولات</p>
                @error('link')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="admin-slider-panel">
            <div class="admin-slider-panel-head">
                <div class="admin-slider-panel-icon">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                </div>
                <div>
                    <p class="admin-slider-panel-title">تنظیمات</p>
                    <p class="admin-slider-panel-desc">موقعیت، ترتیب و وضعیت نمایش</p>
                </div>
            </div>

            <div>
                <label for="position" class="admin-field-label">موقعیت نمایش</label>
                <select name="position" id="position" class="admin-select w-full">
                    <option value="home" @selected(old('position', $banner?->position ?? 'home') === 'home')>صفحه اصلی — بخش بنرهای تبلیغاتی</option>
                </select>
                @error('position')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="sort_order" class="admin-field-label">ترتیب نمایش</label>
                <x-admin.number-stepper
                    name="sort_order"
                    id="sort_order"
                    :value="old('sort_order', $banner?->sort_order ?? 0)"
                    :min="0"
                    class="max-w-[12rem]"
                />
                <p class="admin-field-hint">عدد کمتر = نمایش زودتر (حداکثر ۳ بنر در هر ردیف)</p>
                @error('sort_order')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>

            <label class="admin-slider-active">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner?->is_active ?? true))>
                <div>
                    <p class="admin-slider-active-title">نمایش در صفحه اصلی</p>
                    <p class="admin-slider-active-desc">با غیرفعال کردن، این بنر در فروشگاه دیده نمی‌شود.</p>
                </div>
            </label>

            <div class="rounded-xl border border-dashed border-zinc-200 bg-zinc-50/80 p-4 text-xs leading-relaxed text-zinc-500">
                <p class="font-bold text-zinc-700">راهنمای تصویر</p>
                <p class="mt-1">ابعاد پیشنهادی ۶۰۰×۴۰۰ پیکسل (نسبت ۳:۲). در صورت خطای فرم، تصویر انتخاب‌شده حفظ می‌شود و نیازی به انتخاب مجدد نیست.</p>
            </div>
        </div>
    </div>
</div>
