@php($isEdit = isset($license) && $license->exists)

<form method="POST"
      action="{{ $isEdit ? route('admin.footer-licenses.update', $license) : route('admin.footer-licenses.store') }}"
      enctype="multipart/form-data"
      class="space-y-6">

@csrf
@if($isEdit)
    @method('PUT')
@endif

{{-- Header --}}
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="mb-2 flex items-center gap-2 text-xs text-zinc-400">
            <a href="{{ route('admin.footer-licenses.index') }}"
               class="transition-colors hover:text-zinc-700">
                مجوزهای فوتر
            </a>

            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                      d="M7.21 14.77a.75.75 0 01.02-1.06L10.94 10 7.23 6.29a.75.75 0 111.06-1.06l4.24 4.24a.75.75 0 010 1.06l-4.24 4.24a.75.75 0 01-1.06 0z"
                      clip-rule="evenodd"/>
            </svg>

            <span class="text-zinc-600">
                {{ $isEdit ? 'ویرایش مجوز' : 'مجوز جدید' }}
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
                    {{ $isEdit ? 'ویرایش مجوز' : 'افزودن مجوز جدید' }}
                </h1>

                <p class="mt-0.5 text-sm text-zinc-500">
                    {{ $isEdit
                        ? 'اطلاعات و نحوه نمایش این مجوز را ویرایش کنید.'
                        : 'یک نماد یا مجوز جدید برای نمایش در فوتر اضافه کنید.'
                    }}
                </p>
            </div>
        </div>
    </div>
</div>


{{-- Main Grid --}}
<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">

    {{-- Form --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm">

        <div class="border-b border-zinc-100 px-5 py-4">
            <h2 class="text-sm font-black text-zinc-900">
                اطلاعات مجوز
            </h2>

            <p class="mt-1 text-xs text-zinc-400">
                اطلاعات اصلی مجوز را وارد کنید.
            </p>
        </div>

        <div class="space-y-6 p-5">

            {{-- Title --}}
            <div>
                <label for="license-title" class="admin-field-label">
                    عنوان
                    <span class="ml-1 text-[11px] font-normal text-zinc-400">(اختیاری)</span>
                </label>

                <input
                    id="license-title"
                    type="text"
                    name="title"
                    value="{{ old('title', $license->title ?? '') }}"
                    class="admin-input w-full"
                    placeholder="مثلاً: نماد اعتماد الکترونیکی"
                >

                <p class="admin-field-hint">
                    برای tooltip تصویر و بهبود دسترس‌پذیری استفاده می‌شود.
                </p>

                @error('title')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror
            </div>


            {{-- Image --}}
            <div>
                <label for="license-image" class="admin-field-label">
                    تصویر مجوز
                    <span class="ml-1 text-rose-500">*</span>
                </label>

                @if($isEdit && $license->imageUrl())
                    <div class="mb-4 flex items-center gap-4 rounded-2xl border border-zinc-200 bg-zinc-50/70 p-4">

                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl border border-zinc-200 bg-white p-2 shadow-sm">
                            <img
                                src="{{ $license->imageUrl() }}"
                                alt="{{ $license->title ?? 'مجوز' }}"
                                class="h-full w-full object-contain"
                            >
                        </div>

                        <div>
                            <p class="text-sm font-bold text-zinc-700">
                                تصویر فعلی
                            </p>

                            <p class="mt-1 text-xs leading-5 text-zinc-400">
                                برای جایگزینی، فایل جدید انتخاب کنید.
                            </p>
                        </div>
                    </div>
                @endif

                <label for="license-image"
                       class="group flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-zinc-200 bg-zinc-50/50 px-6 py-9 text-center transition-all hover:border-teal-300 hover:bg-teal-50/30">

                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-white text-zinc-400 shadow-sm ring-1 ring-zinc-200 transition-colors group-hover:text-teal-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M12 16V4m0 0l-4 4m4-4l4 4M4 16.5v1.75A1.75 1.75 0 005.75 20h12.5A1.75 1.75 0 0020 18.25V16.5"/>
                        </svg>
                    </div>

                    <span class="text-sm font-bold text-zinc-700">
                        برای انتخاب تصویر کلیک کنید
                    </span>

                    <span class="mt-1 text-xs text-zinc-400">
                        JPG، PNG، WebP، GIF یا SVG
                    </span>

                    <input
                        id="license-image"
                        type="file"
                        name="image"
                        {{ $isEdit ? '' : 'required' }}
                        accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml"
                        class="sr-only"
                    >
                </label>

                <p class="admin-field-hint mt-2">
                    حداکثر حجم فایل ۲ مگابایت است. برای لوگوها PNG یا SVG شفاف پیشنهاد می‌شود.
                </p>

                @error('image')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror
            </div>


            {{-- Link --}}
            <div>
                <label for="license-link" class="admin-field-label">
                    لینک مقصد
                    <span class="ml-1 text-[11px] font-normal text-zinc-400">(اختیاری)</span>
                </label>

                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 11-5.656-5.656l1.5-1.5m7.328-7.328l1.5-1.5a4 4 0 015.656 5.656l-3 3a4 4 0 01-5.656 0"/>
                        </svg>
                    </div>

                    <input
                        id="license-link"
                        type="text"
                        name="link"
                        value="{{ old('link', $license->link ?? '') }}"
                        class="admin-input w-full pl-10 text-left"
                        dir="ltr"
                        placeholder="https://example.com/..."
                    >
                </div>

                <p class="admin-field-hint">
                    در صورت وجود، با کلیک روی مجوز لینک در تب جدید باز می‌شود.
                </p>

                @error('link')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror
            </div>


            {{-- Sort + Status --}}
            <div class="grid gap-5 sm:grid-cols-2">

                <div>
                    <label for="sort-order" class="admin-field-label">
                        ترتیب نمایش
                    </label>

                    <input
                        id="sort-order"
                        type="number"
                        name="sort_order"
                        value="{{ old('sort_order', $license->sort_order ?? 0) }}"
                        min="0"
                        max="9999"
                        class="admin-input w-full"
                        dir="ltr"
                    >

                    <p class="admin-field-hint">
                        عدد کمتر، مجوز را زودتر نمایش می‌دهد.
                    </p>

                    @error('sort_order')
                        <p class="admin-field-error">{{ $message }}</p>
                    @enderror
                </div>


                <div>
                    <label class="admin-field-label">
                        وضعیت نمایش
                    </label>

                    <div class="flex min-h-[46px] items-center justify-between rounded-xl border border-zinc-200 bg-zinc-50/50 px-4">

                        <div>
                            <p class="text-sm font-bold text-zinc-700">
                                نمایش مجوز
                            </p>

                            <p class="mt-0.5 text-[11px] text-zinc-400">
                                در فوتر سایت نمایش داده شود
                            </p>
                        </div>

                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="hidden" name="is_active" value="0">

                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                @checked(old('is_active', $license->is_active ?? true))
                                class="peer sr-only"
                            >

                            <div class="h-6 w-11 rounded-full bg-zinc-200 transition-colors peer-checked:bg-teal-500 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-100 after:absolute after:left-[3px] after:top-[3px] after:h-4 after:w-4 after:rounded-full after:bg-white after:shadow-sm after:transition-transform peer-checked:after:translate-x-5 rtl:peer-checked:after:-translate-x-0">
                            </div>
                        </label>

                    </div>
                </div>

            </div>

        </div>


        {{-- Actions --}}
        <div class="flex flex-col-reverse gap-3 border-t border-zinc-100 bg-zinc-50/40 px-5 py-4 sm:flex-row sm:items-center">

            <a href="{{ route('admin.footer-licenses.index') }}"
               class="admin-btn-secondary inline-flex justify-center">
                انصراف
            </a>

            <button type="submit"
                    class="admin-btn-primary inline-flex items-center justify-center gap-2 sm:min-w-[120px]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M5 13l4 4L19 7"/>
                </svg>

                {{ $isEdit ? 'ذخیره تغییرات' : 'ذخیره مجوز' }}
            </button>

        </div>

    </div>


    {{-- Sidebar --}}
    <aside class="space-y-4">

        {{-- Preview / Guide --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm">

            <div class="border-b border-zinc-100 px-5 py-4">
                <h2 class="text-sm font-black text-zinc-900">
                    راهنمای مجوز
                </h2>
            </div>

            <div class="space-y-4 p-5">

                <div class="flex gap-3">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-xs font-black text-teal-600">
                        ۱
                    </div>

                    <div>
                        <p class="text-xs font-bold text-zinc-700">
                            تصویر مناسب انتخاب کنید
                        </p>

                        <p class="mt-1 text-[11px] leading-5 text-zinc-400">
                            بهتر است تصویر پس‌زمینه شفاف و نسبت ابعاد مناسب داشته باشد.
                        </p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-xs font-black text-teal-600">
                        ۲
                    </div>

                    <div>
                        <p class="text-xs font-bold text-zinc-700">
                            لینک را در صورت نیاز وارد کنید
                        </p>

                        <p class="mt-1 text-[11px] leading-5 text-zinc-400">
                            اگر مجوز صفحه تأیید یا مرجع آنلاین دارد، لینک آن را ثبت کنید.
                        </p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-xs font-black text-teal-600">
                        ۳
                    </div>

                    <div>
                        <p class="text-xs font-bold text-zinc-700">
                            وضعیت را بررسی کنید
                        </p>

                        <p class="mt-1 text-[11px] leading-5 text-zinc-400">
                            فقط مجوزهای فعال در فوتر سایت نمایش داده خواهند شد.
                        </p>
                    </div>
                </div>

            </div>
        </div>


        {{-- Tip --}}
        <div class="rounded-2xl border border-teal-100 bg-teal-50/60 p-4">
            <div class="flex gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-teal-600 shadow-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M11.25 11.25l.041-.02a.75.75 0 011.059.68v3.09a.75.75 0 01-1.5 0v-2.39l-.309.154a.75.75 0 01-.67-1.342l1.379-.689z"/>
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M12 21a9 9 0 100-18 9 9 0 000 18z"/>
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black text-teal-800">
                        نکته
                    </p>

                    <p class="mt-1 text-[11px] leading-5 text-teal-700/80">
                        عنوان را کوتاه و واضح انتخاب کنید تا برای کاربران و ابزارهای دسترس‌پذیری مناسب باشد.
                    </p>
                </div>
            </div>
        </div>

    </aside>

</div>

</form>
