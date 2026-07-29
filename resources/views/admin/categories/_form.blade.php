@php
    $category = $category ?? null;
@endphp

<div class="space-y-6">
    <x-admin.form-section title="اطلاعات اصلی">
        <div>
            <label for="name" class="admin-field-label">نام دسته‌بندی</label>
            <input
                type="text"
                name="name"
                id="name"
                value="{{ old('name', $category?->name ?? '') }}"
                class="admin-input w-full"
                placeholder="مثلاً: لوازم خانگی"
                required
            >
            @error('name')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label for="description" class="admin-field-label">توضیحات</label>
            <textarea
                name="description"
                id="description"
                rows="4"
                class="admin-input w-full resize-y"
                placeholder="توضیح کوتاه درباره این دسته (اختیاری)"
            >{{ old('description', $category?->description ?? '') }}</textarea>
            @error('description')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="تنظیمات نمایش">
        <div class="max-w-xs">
            <label for="sort_order" class="admin-field-label">ترتیب نمایش</label>
            <x-admin.number-stepper
                name="sort_order"
                id="sort_order"
                :value="old('sort_order', $category?->sort_order ?? 0)"
                :min="0"
            />
            <p class="admin-field-hint">
                تعیین می‌کند این دسته در فروشگاه و فیلترها کجا قرار بگیرد. عدد کوچکتر = بالاتر در لیست.
            </p>
            @error('sort_order')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-center gap-2.5 cursor-pointer md:col-span-2">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                id="is_active"
                class="admin-checkbox"
                @checked(old('is_active', $category?->is_active ?? true))
            >
            <span class="text-sm font-medium text-zinc-700">فعال — نمایش در فروشگاه</span>
        </label>
    </x-admin.form-section>
</div>
