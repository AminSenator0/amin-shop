@php
    $coupon = $coupon ?? null;
    $isEdit = isset($coupon);
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

<div
    class="space-y-6"
    x-data="{
        type: '{{ old('type', $coupon?->type ?? 'percent') }}',
        generateCode() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            let code = '';
            for (let i = 0; i < 8; i++) {
                code += chars[Math.floor(Math.random() * chars.length)];
            }
            this.$refs.codeInput.value = code;
            this.$refs.codeInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }"
>
    <x-admin.form-section title="کد تخفیف">
        <div class="md:col-span-2">
            <label for="code" class="admin-field-label">کد <span class="text-rose-500">*</span></label>
            <div class="flex gap-2">
                <input
                    type="text"
                    name="code"
                    id="code"
                    x-ref="codeInput"
                    value="{{ old('code', $coupon?->code ?? '') }}"
                    class="admin-input w-full font-mono uppercase"
                    placeholder="مثلاً: SUMMER26"
                    dir="ltr"
                    autocomplete="off"
                    required
                >
                @unless($isEdit)
                    <button
                        type="button"
                        @click="generateCode()"
                        class="admin-btn-secondary shrink-0 whitespace-nowrap text-xs"
                    >
                        تولید خودکار
                    </button>
                @endunless
            </div>
            <p class="admin-field-hint">کدی که مشتری در تسویه حساب وارد می‌کند. حروف بزرگ انگلیسی و اعداد.</p>
            @error('code')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="مقدار تخفیف">
        <div>
            <label for="type" class="admin-field-label">نوع تخفیف <span class="text-rose-500">*</span></label>
            <select name="type" id="type" class="admin-select w-full" x-model="type" required>
                <option value="percent">درصدی</option>
                <option value="fixed">مبلغ ثابت (تومان)</option>
            </select>
            @error('type')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="value" class="admin-field-label">
                مقدار <span class="text-rose-500">*</span>
            </label>
            <input
                type="text"
                name="value"
                id="value"
                value="{{ old('value', $coupon ? format_number($coupon->value) : '') }}"
                data-price-input
                class="admin-input w-full"
                :placeholder="type === 'percent' ? 'مثلاً: ۱۵' : 'مثلاً: ۵۰٬۰۰۰'"
                dir="ltr"
                inputmode="numeric"
                required
            >
            <p class="admin-field-hint" x-show="type === 'percent'" x-cloak>درصد تخفیف از جمع سفارش (۱ تا ۱۰۰)</p>
            <p class="admin-field-hint" x-show="type === 'fixed'" x-cloak>مبلغ ثابت به تومان که از جمع سفارش کسر می‌شود</p>
            @error('value')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="محدودیت‌ها">
        <div>
            <label for="min_order" class="admin-field-label">حداقل سفارش (تومان)</label>
            <input
                type="text"
                name="min_order"
                id="min_order"
                value="{{ old('min_order', $coupon ? format_number($coupon->min_order ?? 0) : to_persian_digits('0')) }}"
                data-price-input
                class="admin-input w-full"
                placeholder="۰"
                dir="ltr"
                inputmode="numeric"
            >
            <p class="admin-field-hint">کد فقط برای سفارش‌های بالاتر از این مبلغ اعمال می‌شود</p>
            @error('min_order')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="max_uses" class="admin-field-label">حداکثر تعداد استفاده</label>
            <input
                type="text"
                name="max_uses"
                id="max_uses"
                value="{{ old('max_uses', $coupon?->max_uses ? to_persian_digits((string) $coupon->max_uses) : '') }}"
                data-numeric-input
                class="admin-input w-full"
                placeholder="نامحدود"
                dir="ltr"
                inputmode="numeric"
            >
            <p class="admin-field-hint">خالی بگذارید برای استفاده نامحدود</p>
            @error('max_uses')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="expires_at" class="admin-field-label">تاریخ انقضا</label>
            <input
                type="text"
                name="expires_at"
                id="expires_at"
                data-jalali-date
                value="{{ old('expires_at', $coupon?->expires_at ? format_jalali($coupon->expires_at) : '') }}"
                class="admin-input w-full"
                placeholder="۱۴۰۳/۱۲/۲۹"
                autocomplete="off"
            >
            <p class="admin-field-hint">خالی بگذارید اگر کد تاریخ انقضا ندارد</p>
            @error('expires_at')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center md:col-span-2">
            <label class="flex cursor-pointer items-center gap-2.5">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    id="is_active"
                    class="admin-checkbox"
                    @checked(old('is_active', $coupon?->is_active ?? true))
                >
                <span class="text-sm font-medium text-zinc-700">فعال — قابل استفاده در فروشگاه</span>
            </label>
        </div>
    </x-admin.form-section>
</div>
