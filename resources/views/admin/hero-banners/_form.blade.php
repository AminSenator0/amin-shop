@php($isEdit = isset($banner) && $banner->exists)

<form method="POST"
      action="{{ $isEdit ? route('admin.hero-banners.update', $banner) : route('admin.hero-banners.store') }}"
      enctype="multipart/form-data"
      class="space-y-6">

@csrf

@if($isEdit)
    @method('PUT')
@endif

{{-- Page Header --}}
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

    <div>
        <div class="mb-2 flex items-center gap-2 text-xs text-zinc-400">
            <a href="{{ route('admin.hero-banners.index') }}"
               class="transition-colors hover:text-zinc-700">
                بنرهای هیرو
            </a>

            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                      d="M7.21 14.77a.75.75 0 01.02-1.06L10.94 10 7.23 6.29a.75.75 0 111.06-1.06l4.24 4.24a.75.75 0 010 1.06l-4.24 4.24a.75.75 0 01-1.06 0z"
                      clip-rule="evenodd"/>
            </svg>

            <span class="text-zinc-600">
                {{ $isEdit ? 'ویرایش بنر' : 'بنر جدید' }}
            </span>
        </div>

        <div class="flex items-center gap-3">

            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-teal-50 text-teal-600">
                @if($isEdit)
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 15.07a4.5 4.5 0 01-1.897 1.13L6 17l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                    </svg>
                @else
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M12 4v16m8-8H4"/>
                    </svg>
                @endif
            </div>

            <div>
                <h1 class="text-lg font-black tracking-tight text-zinc-900">
                    {{ $isEdit ? 'ویرایش بنر هیرو' : 'افزودن بنر هیرو' }}
                </h1>

                <p class="mt-0.5 text-sm text-zinc-500">
                    {{ $isEdit
                        ? 'اطلاعات و تصویر بنر را مدیریت کنید.'
                        : 'یک بنر جدید برای بخش هیرو سایت ایجاد کنید.'
                    }}
                </p>
            </div>

        </div>
    </div>

</div>


{{-- Main Content --}}
<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">

    {{-- Main Form --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm">

        {{-- Card Header --}}
        <div class="border-b border-zinc-100 px-5 py-4">
            <h2 class="text-sm font-black text-zinc-900">
                اطلاعات بنر
            </h2>

            <p class="mt-1 text-xs text-zinc-400">
                عنوان، تصویر و لینک مقصد بنر را تنظیم کنید.
            </p>
        </div>


        <div class="space-y-6 p-5">

            {{-- Title --}}
            <div>
                <label for="banner-title" class="admin-field-label">
                    عنوان بنر
                    <span class="ml-1 text-rose-500">*</span>
                </label>

                <input
                    id="banner-title"
                    type="text"
                    name="title"
                    value="{{ old('title', $banner->title ?? '') }}"
                    required
                    class="admin-input w-full"
                    placeholder="مثلاً: تخفیف ویژه کتاب‌های داستان"
                >

                <div class="mt-2 flex items-center gap-1.5 text-xs text-zinc-400">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="1.7">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z"/>
                    </svg>

                    <span>
                        این عنوان برای alt تصویر و سئو استفاده می‌شود.
                    </span>
                </div>

                @error('title')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror
            </div>


            {{-- Image --}}
            <div>

                <div class="mb-2 flex items-center justify-between gap-3">
                    <label for="banner-image" class="admin-field-label !mb-0">
                        تصویر بنر
                        <span class="ml-1 text-rose-500">*</span>
                    </label>

                    <span class="text-[11px] font-medium text-zinc-400">
                        پیشنهاد: 1200 × 800
                    </span>
                </div>


                {{-- Current Image --}}
                @if($isEdit && $banner->imageUrl())

                    <div class="mb-4 overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50">

                        <div class="relative aspect-[3/2] w-full overflow-hidden bg-zinc-100">

                            <img
                                src="{{ $banner->imageUrl() }}"
                                alt="{{ $banner->title }}"
                                class="h-full w-full object-cover"
                            >

                            <div class="absolute right-3 top-3">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-black/60 px-2.5 py-1 text-[10px] font-bold text-white backdrop-blur-md">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                    تصویر فعلی
                                </span>
                            </div>

                        </div>

                        <div class="flex items-center justify-between gap-3 px-4 py-3">
                            <p class="text-xs text-zinc-500">
                                برای جایگزینی، تصویر جدید انتخاب کنید.
                            </p>

                            <span class="shrink-0 text-[10px] font-bold text-zinc-400">
                                JPG / PNG / WEBP
                            </span>
                        </div>

                    </div>

                @endif


                {{-- Upload Zone --}}
                <label
                    for="banner-image"
                    class="group relative flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-zinc-200 bg-zinc-50/60 px-6 py-10 text-center transition-all hover:border-teal-300 hover:bg-teal-50/30"
                >

                    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-zinc-400 shadow-sm ring-1 ring-zinc-200 transition-all group-hover:-translate-y-0.5 group-hover:text-teal-600 group-hover:shadow-md">

                        <svg class="h-7 w-7" fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="1.5">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M12 16V4m0 0l-4 4m4-4l4 4M4 16.5v1.75A1.75 1.75 0 005.75 20h12.5A1.75 1.75 0 0020 18.25V16.5"/>
                        </svg>

                    </div>

                    <span class="text-sm font-black text-zinc-700">
                        تصویر بنر را انتخاب کنید
                    </span>

                    <span class="mt-1.5 text-xs text-zinc-400">
                        برای انتخاب فایل کلیک کنید
                    </span>

                    <span class="mt-3 rounded-lg bg-white px-2.5 py-1 text-[10px] font-bold text-zinc-400 ring-1 ring-zinc-200">
                        حداکثر ۴ مگابایت
                    </span>

                    <input
                        id="banner-image"
                        type="file"
                        name="image"
                        {{ $isEdit ? '' : 'required' }}
                        accept="image/jpeg,image/png,image/webp"
                        class="sr-only"
                    >

                </label>

                @error('image')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror

            </div>


            {{-- Link --}}
            <div>

                <label for="banner-link" class="admin-field-label">
                    لینک مقصد
                    <span class="ml-1 text-[11px] font-normal text-zinc-400">
                        اختیاری
                    </span>
                </label>

                <div class="relative">

                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400">
                        <svg class="h-4 w-4" fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="1.7">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 11-5.656-5.656l1.5-1.5m7.328-7.328l1.5-1.5a4 4 0 015.656 5.656l-3 3a4 4 0 01-5.656 0"/>
                        </svg>
                    </div>

                    <input
                        id="banner-link"
                        type="text"
                        name="link"
                        value="{{ old('link', $banner->link ?? '') }}"
                        class="admin-input w-full pl-10 text-left"
                        dir="ltr"
                        placeholder="/products/... یا https://..."
                    >

                </div>

                <p class="admin-field-hint">
                    اگر وارد شود، با کلیک روی بنر کاربر به این آدرس هدایت می‌شود.
                </p>

                @error('link')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror

            </div>


            {{-- Settings --}}
            <div class="grid gap-5 sm:grid-cols-2">

                {{-- Sort Order --}}
                <div>

                    <label for="sort-order" class="admin-field-label">
                        ترتیب نمایش
                    </label>

                    <div class="relative">
                        <input
                            id="sort-order"
                            type="number"
                            name="sort_order"
                            value="{{ old('sort_order', $banner->sort_order ?? 0) }}"
                            min="0"
                            max="9999"
                            class="admin-input w-full"
                            dir="ltr"
                        >
                    </div>

                    <p class="admin-field-hint">
                        عدد کمتر، بنر را زودتر نمایش می‌دهد.
                    </p>

                    @error('sort_order')
                        <p class="admin-field-error">{{ $message }}</p>
                    @enderror

                </div>


                {{-- Active --}}
                <div>

                    <label class="admin-field-label">
                        وضعیت نمایش
                    </label>

                    <div class="flex min-h-[46px] items-center justify-between rounded-xl border border-zinc-200 bg-zinc-50/50 px-4">

                        <div>
                            <p class="text-sm font-bold text-zinc-700">
                                بنر فعال باشد
                            </p>

                            <p class="mt-0.5 text-[11px] text-zinc-400">
                                در سایت نمایش داده شود
                            </p>
                        </div>

                        <label class="relative inline-flex cursor-pointer items-center">

                            <input type="hidden" name="is_active" value="0">

                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                @checked(old('is_active', $banner->is_active ?? true))
                                class="peer sr-only"
                            >

                            <div class="relative h-6 w-11 rounded-full bg-zinc-200 transition-colors peer-checked:bg-teal-500 peer-focus:ring-4 peer-focus:ring-teal-100 after:absolute after:left-[3px] after:top-[3px] after:h-4 after:w-4 after:rounded-full after:bg-white after:shadow-sm after:transition-transform peer-checked:after:translate-x-5 rtl:peer-checked:after:translate-x-0">
                            </div>

                        </label>

                    </div>

                </div>

            </div>

        </div>


        {{-- Actions --}}
        <div class="flex flex-col-reverse gap-3 border-t border-zinc-100 bg-zinc-50/40 px-5 py-4 sm:flex-row sm:items-center">

            <a href="{{ route('admin.hero-banners.index') }}"
               class="admin-btn-secondary inline-flex justify-center">
                انصراف
            </a>

            <button
                type="submit"
                class="admin-btn-primary inline-flex items-center justify-center gap-2 sm:min-w-[140px]"
            >
                <svg class="h-4 w-4" fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="2">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M5 13l4 4L19 7"/>
                </svg>

                {{ $isEdit ? 'ذخیره تغییرات' : 'ذخیره بنر' }}
            </button>

        </div>

    </div>


    {{-- Sidebar --}}
    <aside class="space-y-4">

        {{-- Banner Tips --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm">

            <div class="border-b border-zinc-100 px-5 py-4">
                <h2 class="text-sm font-black text-zinc-900">
                    نکات طراحی بنر
                </h2>

                <p class="mt-1 text-xs text-zinc-400">
                    برای نمایش بهتر در سایت
                </p>
            </div>

            <div class="space-y-4 p-5">

                <div class="flex gap-3">

                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-600">
                        <svg class="h-4 w-4" fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="1.7">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M4 5h16M4 19h16M5 5v14m14-14v14"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-zinc-700">
                            نسبت تصویر ۳:۲
                        </p>

                        <p class="mt-1 text-[11px] leading-5 text-zinc-400">
                            تصاویر ۱۲۰۰×۸۰۰ یا مشابه، ظاهر یکدست‌تری ایجاد می‌کنند.
                        </p>
                    </div>

                </div>


                <div class="flex gap-3">

                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-600">
                        <svg class="h-4 w-4" fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="1.7">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M12 18h.01M9.05 9a3 3 0 115.9 0c0 1.5-1.95 2.25-2.45 3.25-.2.4-.25.75-.25 1.25"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-zinc-700">
                            عنوان کوتاه و واضح
                        </p>

                        <p class="mt-1 text-[11px] leading-5 text-zinc-400">
                            عنوان علاوه بر نمایش مدیریتی، برای alt تصویر و سئو استفاده می‌شود.
                        </p>
                    </div>

                </div>


                <div class="flex gap-3">

                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-600">
                        <svg class="h-4 w-4" fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="1.7">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 11-5.656-5.656l1.5-1.5m7.328-7.328l1.5-1.5a4 4 0 015.656 5.656l-3 3a4 4 0 01-5.656 0"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-zinc-700">
                            لینک اختیاری است
                        </p>

                        <p class="mt-1 text-[11px] leading-5 text-zinc-400">
                            اگر لینک وارد نشود، بنر صرفاً به‌صورت تصویری نمایش داده می‌شود.
                        </p>
                    </div>

                </div>

            </div>
        </div>


        {{-- Recommended Specs --}}
        <div class="rounded-2xl border border-teal-100 bg-teal-50/60 p-4">

            <div class="flex gap-3">

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-teal-600 shadow-sm">
                    <svg class="h-4 w-4" fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.7">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M4 6.75A2.75 2.75 0 016.75 4h10.5A2.75 2.75 0 0120 6.75v10.5A2.75 2.75 0 0117.25 20H6.75A2.75 2.75 0 014 17.25V6.75z"/>
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M8 15l2.5-2.5L13 15l2-2 2 2"/>
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black text-teal-800">
                        مشخصات پیشنهادی
                    </p>

                    <div class="mt-2 space-y-1 text-[11px] leading-5 text-teal-700/80">
                        <p>• فرمت: JPG، PNG یا WebP</p>
                        <p>• حداکثر حجم: ۴MB</p>
                        <p>• نسبت تصویر: ۳:۲</p>
                        <p>• ابعاد پیشنهادی: ۱۲۰۰×۸۰۰</p>
                    </div>
                </div>

            </div>

        </div>

    </aside>

</div>

</form>
