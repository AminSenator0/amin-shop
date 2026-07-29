<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-sm font-bold text-shop-text">عنوان آدرس</label>
        <input type="text" name="title" value="{{ old('title', $address->title ?? '') }}" class="input-shop w-full px-4 py-3 text-sm" placeholder="خانه، محل کار..." required>
        @error('title')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-bold text-shop-text">نام گیرنده</label>
        <input type="text" name="full_name" value="{{ old('full_name', $address->full_name ?? auth()->user()->name) }}" class="input-shop w-full px-4 py-3 text-sm" required>
        @error('full_name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-bold text-shop-text">شماره تماس</label>
        <input type="text" name="phone" value="{{ old('phone', $address->phone ?? auth()->user()->phone) }}" data-phone-input class="input-shop w-full px-4 py-3 text-sm" dir="ltr" placeholder="09121234567" required>
        @error('phone')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-bold text-shop-text">استان</label>
        <input type="text" name="province" value="{{ old('province', $address->province ?? '') }}" class="input-shop w-full px-4 py-3 text-sm" required>
        @error('province')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-bold text-shop-text">شهر</label>
        <input type="text" name="city" value="{{ old('city', $address->city ?? '') }}" class="input-shop w-full px-4 py-3 text-sm" required>
        @error('city')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-bold text-shop-text">کد پستی</label>
        <input type="text" name="postal_code" value="{{ old('postal_code', $address->postal_code ?? '') }}" data-numeric-input maxlength="10" class="input-shop w-full px-4 py-3 text-sm" dir="ltr" required>
        @error('postal_code')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="mb-1.5 block text-sm font-bold text-shop-text">آدرس کامل</label>
        <textarea name="address" rows="3" class="input-shop w-full px-4 py-3 text-sm" required>{{ old('address', $address->address ?? '') }}</textarea>
        @error('address')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="flex items-center gap-2.5">
            <input type="checkbox" name="is_default" value="1" class="rounded border-shop-border text-shop-primary focus:ring-shop-primary" @checked(old('is_default', $address->is_default ?? false))>
            <span class="text-sm font-bold text-shop-text">تنظیم به‌عنوان آدرس پیش‌فرض</span>
        </label>
    </div>
</div>
