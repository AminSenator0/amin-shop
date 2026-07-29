@php
    $brand = $brand ?? null;
    $existingLogo = isset($brand) && $brand->logo ? asset('storage/'.$brand->logo) : null;
@endphp

<div class="space-y-6">
    <x-admin.form-section title="اطلاعات برند">
        <div class="md:col-span-2">
            <label for="name" class="admin-field-label">نام برند</label>
            <input
                type="text"
                name="name"
                id="name"
                value="{{ old('name', $brand?->name ?? '') }}"
                class="admin-input w-full"
                placeholder="مثلاً: سامسونگ، اپل، نایک"
                required
            >
            @error('name')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <x-admin.single-image-upload
                name="logo"
                remove-name="remove_logo"
                :existing-url="$existingLogo"
                label="لوگو"
                button="انتخاب لوگو"
                hint="فرمت‌های مجاز: JPG، PNG، WebP — حداکثر ۱ مگابایت. تصویر به‌صورت خودکار به ۲۰۰×۲۰۰ پیکسل تبدیل می‌شود."
            />
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="تنظیمات نمایش">
        <label class="flex cursor-pointer items-center gap-2.5 md:col-span-2">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                id="is_active"
                class="admin-checkbox"
                @checked(old('is_active', $brand?->is_active ?? true))
            >
            <span class="text-sm font-medium text-zinc-700">فعال — نمایش در فروشگاه و فیلتر محصولات</span>
        </label>
    </x-admin.form-section>
</div>
