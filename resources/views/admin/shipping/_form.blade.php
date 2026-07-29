@php
    $shipping = $shipping ?? null;
    $isEdit = isset($shipping);
    $hasPresets = ! $isEdit && ! empty($presets);
@endphp

<div
    class="space-y-6 p-6"
    x-data="{
        selectedPreset: 'custom',
        presets: @js($presets ?? []),
        name: @js(old('name', $shipping?->name ?? '')),
        description: @js(old('description', $shipping?->description ?? '')),
        costRaw: @js(old('cost', $shipping ? format_number($shipping->cost, false) : format_number(0, false))),
        freeAboveRaw: @js(old('free_above', $shipping?->free_above ? format_number($shipping->free_above, false) : '')),
        estimatedDays: @js(old('estimated_days', $shipping?->estimated_days ? to_persian_digits((string) $shipping->estimated_days) : '')),
        isActive: @js((bool) old('is_active', $shipping?->is_active ?? true)),
        fieldErrors: {},
        showValidationAlert: false,
        toEnglish(val) {
            if (!val) return '';
            const persian = '۰۱۲۳۴۵۶۷۸۹';
            const arabic = '٠١٢٣٤٥٦٧٨٩';
            return String(val).replace(/[۰-۹٠-٩]/g, (d) => {
                let i = persian.indexOf(d);
                if (i >= 0) return String(i);
                i = arabic.indexOf(d);
                return i >= 0 ? String(i) : d;
            });
        },
        toPersianDigits(val) {
            return String(val).replace(/[0-9]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
        },
        formatPriceForInput(num) {
            if (!num) return '';
            return this.toPersianDigits(Number(num).toLocaleString('en-US'));
        },
        parseAmount(val) {
            const digits = this.toEnglish(val).replace(/[^\d]/g, '');
            return digits ? parseInt(digits, 10) : 0;
        },
        formatAmount(num) {
            if (!num) return 'رایگان';
            return Number(num).toLocaleString('fa-IR') + ' تومان';
        },
        get costAmount() { return this.parseAmount(this.costRaw); },
        get freeAboveAmount() { return this.parseAmount(this.freeAboveRaw); },
        get daysLabel() {
            const days = this.parseAmount(this.estimatedDays);
            if (!days) return null;
            return 'تحویل تا ' + days.toLocaleString('fa-IR') + ' روز کاری';
        },
        get previewDescription() {
            if (this.description.trim()) return this.description.trim();
            return this.daysLabel;
        },
        get selectedPresetLabel() {
            if (this.selectedPreset === 'custom') return null;
            const preset = this.presets.find((item) => item.id === this.selectedPreset);
            return preset ? preset.name : null;
        },
        markCustom() {
            this.selectedPreset = 'custom';
        },
        applyPreset(preset) {
            this.selectedPreset = preset.id;
            this.name = preset.name;
            this.description = preset.description;
            this.costRaw = this.formatPriceForInput(preset.cost);
            this.freeAboveRaw = preset.free_above ? this.formatPriceForInput(preset.free_above) : '';
            this.estimatedDays = this.toPersianDigits(String(preset.estimated_days));
            this.isActive = true;
        },
        selectCustom() {
            this.selectedPreset = 'custom';
        },
        presetSummary(preset) {
            const cost = preset.cost ? Number(preset.cost).toLocaleString('fa-IR') + ' تومان' : 'رایگان';
            const days = Number(preset.estimated_days).toLocaleString('fa-IR') + ' روز';
            return cost + ' · ' + days;
        },
        clearFieldError(field) {
            if (this.fieldErrors[field]) {
                delete this.fieldErrors[field];
            }
            if (Object.keys(this.fieldErrors).length === 0) {
                this.showValidationAlert = false;
            }
        },
        hasDigits(val) {
            return this.toEnglish(val).replace(/[^\d]/g, '') !== '';
        },
        validateForm() {
            this.fieldErrors = {};
            let valid = true;

            if (!this.name.trim()) {
                this.fieldErrors.name = 'نام روش الزامی است.';
                valid = false;
            }

            if (!this.hasDigits(this.costRaw)) {
                this.fieldErrors.cost = 'هزینه پایه الزامی است.';
                valid = false;
            }

            if (!valid) {
                this.showValidationAlert = true;
                this.$nextTick(() => {
                    const firstInvalid = this.$el.querySelector('[data-invalid]');
                    firstInvalid?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid?.focus();
                });
            }

            return valid;
        },
        initFormValidation() {
            const form = this.$el.closest('form');
            if (!form || form.dataset.shippingValidationInit === 'true') {
                return;
            }
            form.dataset.shippingValidationInit = 'true';
            form.addEventListener('submit', (event) => {
                if (!this.validateForm()) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                }
            }, true);
        }
    }"
    x-init="initFormValidation()"
>
    <div x-show="showValidationAlert" x-cloak class="admin-alert-error">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <div>
            <p class="font-bold">لطفاً فیلدهای الزامی را تکمیل کنید:</p>
            <ul class="mt-1 list-inside list-disc text-sm">
                <template x-for="message in Object.values(fieldErrors)" :key="message">
                    <li x-text="message"></li>
                </template>
            </ul>
        </div>
    </div>

    @if($errors->any())
        <div class="admin-alert-error">
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

    @if($hasPresets)
        <section class="rounded-2xl border border-zinc-200/80 bg-white p-5">
            <div class="mb-4">
                <h3 class="text-sm font-bold text-zinc-800">انتخاب از روش‌های آماده</h3>
                <p class="mt-1 text-xs text-zinc-500">یکی را انتخاب کنید تا فیلدها پر شوند — یا «سفارشی» را بزنید و خودتان وارد کنید</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <template x-for="preset in presets" :key="preset.id">
                    <button
                        type="button"
                        @click="applyPreset(preset)"
                        class="rounded-xl border p-4 text-right transition-all"
                        :class="selectedPreset === preset.id
                            ? 'border-indigo-500 bg-indigo-50/60 ring-1 ring-indigo-500/30'
                            : 'border-zinc-200 bg-zinc-50/40 hover:border-indigo-200 hover:bg-indigo-50/30'"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <span class="font-medium text-zinc-900" x-text="preset.name"></span>
                            <span
                                class="shrink-0 whitespace-nowrap rounded-full px-2 py-0.5 text-[10px] font-medium"
                                :class="selectedPreset === preset.id ? 'bg-indigo-100 text-indigo-700' : 'bg-zinc-200/80 text-zinc-500'"
                                x-show="selectedPreset === preset.id"
                            >انتخاب‌شده</span>
                        </div>
                        <p class="mt-1 text-xs text-zinc-500" x-text="preset.description"></p>
                        <p class="mt-2 text-xs font-medium text-zinc-600" x-text="presetSummary(preset)"></p>
                    </button>
                </template>

                <button
                    type="button"
                    @click="selectCustom()"
                    class="rounded-xl border border-dashed p-4 text-right transition-all"
                    :class="selectedPreset === 'custom'
                        ? 'border-indigo-500 bg-indigo-50/60 ring-1 ring-indigo-500/30'
                        : 'border-zinc-300 bg-white hover:border-indigo-200 hover:bg-indigo-50/20'"
                >
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        <span class="font-medium text-zinc-900">روش سفارشی</span>
                    </div>
                    <p class="mt-1 text-xs text-zinc-500">نام، هزینه و زمان تحویل را خودتان وارد کنید</p>
                </button>
            </div>
        </section>
    @endif

    <div class="rounded-2xl border border-zinc-200/80 bg-zinc-50/50 p-5">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h3 class="text-sm font-bold text-zinc-700">پیش‌نمایش در تسویه حساب</h3>
            <div class="flex items-center gap-2">
                <span
                    x-show="selectedPresetLabel"
                    x-cloak
                    class="inline-flex shrink-0 items-center whitespace-nowrap rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700"
                    x-text="'الگو: ' + selectedPresetLabel"
                ></span>
                <span
                    class="inline-flex shrink-0 items-center whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-medium"
                    :class="isActive ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500'"
                    x-text="isActive ? 'فعال' : 'غیرفعال'"
                ></span>
            </div>
        </div>

        <div class="rounded-xl border-2 border-indigo-400 bg-white p-4">
            <div class="flex items-center justify-between gap-3">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="mt-1 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-[5px] border-indigo-600 bg-white"></span>
                    <div class="min-w-0">
                        <p class="font-medium text-zinc-900" x-text="name.trim() || 'نام روش ارسال'"></p>
                        <p
                            class="mt-0.5 text-sm text-zinc-500"
                            x-show="previewDescription"
                            x-text="previewDescription"
                        ></p>
                        <p
                            class="mt-1.5 text-xs text-emerald-600"
                            x-show="freeAboveAmount > 0"
                            x-text="'بالای ' + freeAboveAmount.toLocaleString('fa-IR') + ' تومان رایگان'"
                        ></p>
                    </div>
                </div>
                <span class="shrink-0 text-sm font-medium text-zinc-700" x-text="formatAmount(costAmount)"></span>
            </div>
        </div>

        <p class="mt-3 text-xs leading-relaxed text-zinc-500">
            هزینه نهایی با توجه به جمع سفارش و آستانه ارسال رایگان محاسبه می‌شود.
        </p>
    </div>

    @if($isEdit && $shipping->orders()->exists())
        <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4">
            <p class="flex items-center gap-2 text-sm font-medium text-amber-800">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span>این روش در {{ to_persian_digits((string) $shipping->orders()->count()) }} سفارش استفاده شده</span>
            </p>
            <p class="mt-1.5 text-xs text-amber-700/80">تغییر نام یا هزینه روی سفارش‌های قبلی اثر نمی‌گذارد.</p>
        </div>
    @endif

    <x-admin.form-section title="اطلاعات روش ارسال">
        <div class="md:col-span-2">
            <label for="shipping_name" class="admin-field-label">نام روش <span class="text-rose-500">*</span></label>
            <input
                type="text"
                name="name"
                id="shipping_name"
                value="{{ old('name', $shipping?->name ?? '') }}"
                class="admin-input w-full"
                placeholder="مثلاً: پست پیشتاز، پیک موتوری"
                x-model="name"
                @input="markCustom(); clearFieldError('name')"
                :data-invalid="fieldErrors.name ? true : null"
                :class="fieldErrors.name ? 'border-rose-500 ring-1 ring-rose-200' : ''"
            >
            <p class="admin-field-hint">نامی که مشتری در صفحه تسویه حساب می‌بیند</p>
            @error('name')<p class="admin-field-error">{{ $message }}</p>@else
            <p x-show="fieldErrors.name" x-text="fieldErrors.name" class="admin-field-error" x-cloak></p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <label for="shipping_description" class="admin-field-label">توضیحات</label>
            <textarea
                name="description"
                id="shipping_description"
                rows="3"
                class="admin-input w-full resize-none"
                placeholder="مثلاً: ارسال به سراسر کشور با بیمه مرسوله"
                x-model="description"
                @input="markCustom()"
            ></textarea>
            <p class="admin-field-hint">اختیاری — زیر نام روش در تسویه حساب نمایش داده می‌شود</p>
            @error('description')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="هزینه و زمان تحویل">
        <div>
            <label for="shipping_cost" class="admin-field-label">هزینه پایه <span class="text-rose-500">*</span></label>
            <input
                type="text"
                name="cost"
                id="shipping_cost"
                value="{{ old('cost', format_number($shipping?->cost ?? 0, false)) }}"
                data-price-input
                class="admin-input w-full"
                placeholder="۰"
                dir="ltr"
                inputmode="numeric"
                x-model="costRaw"
                @input="markCustom(); clearFieldError('cost')"
                :data-invalid="fieldErrors.cost ? true : null"
                :class="fieldErrors.cost ? 'border-rose-500 ring-1 ring-rose-200' : ''"
            >
            <p class="admin-field-hint">مبلغ به تومان — برای ارسال رایگان همیشگی، صفر وارد کنید</p>
            @error('cost')<p class="admin-field-error">{{ $message }}</p>@else
            <p x-show="fieldErrors.cost" x-text="fieldErrors.cost" class="admin-field-error" x-cloak></p>
            @enderror
        </div>

        <div>
            <label for="shipping_free_above" class="admin-field-label">ارسال رایگان بالای</label>
            <input
                type="text"
                name="free_above"
                id="shipping_free_above"
                value="{{ old('free_above', $shipping?->free_above ? format_number($shipping->free_above, false) : '') }}"
                data-price-input
                class="admin-input w-full"
                placeholder="خالی = بدون آستانه"
                dir="ltr"
                inputmode="numeric"
                x-model="freeAboveRaw"
                @input="markCustom()"
            >
            <p class="admin-field-hint">اگر جمع سفارش از این مبلغ بیشتر باشد، هزینه ارسال صفر می‌شود</p>
            @error('free_above')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="shipping_estimated_days" class="admin-field-label">زمان تحویل تقریبی</label>
            <div class="relative">
                <input
                    type="text"
                    name="estimated_days"
                    id="shipping_estimated_days"
                    value="{{ old('estimated_days', $shipping?->estimated_days ? to_persian_digits((string) $shipping->estimated_days) : '') }}"
                    data-numeric-input
                    class="admin-input w-full !pl-12"
                    placeholder="مثلاً: ۳"
                    dir="ltr"
                    inputmode="numeric"
                    x-model="estimatedDays"
                    @input="markCustom()"
                >
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-xs text-zinc-400">روز</span>
            </div>
            <p class="admin-field-hint">تعداد روز کاری تا تحویل</p>
            @error('estimated_days')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="وضعیت انتشار">
        <div class="md:col-span-2">
            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 transition-colors hover:border-indigo-200 hover:bg-indigo-50/40">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    id="shipping_is_active"
                    class="admin-checkbox mt-0.5"
                    x-model="isActive"
                >
                <span>
                    <span class="block text-sm font-medium text-zinc-800">فعال — قابل انتخاب در تسویه حساب</span>
                    <span class="mt-1 block text-xs text-zinc-500">روش‌های غیرفعال در فروشگاه نمایش داده نمی‌شوند</span>
                </span>
            </label>
        </div>
    </x-admin.form-section>
</div>
