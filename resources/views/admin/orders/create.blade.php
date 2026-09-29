@extends('layouts.admin')

@section('header', 'ثبت سفارش دستی')

@section('content')
<form method="POST" action="{{ route('admin.orders.store') }}" class="admin-card w-full" x-data="{ rows: [{ product_id: '', quantity: 1, size: '', color: '' }] }">
    @csrf
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">ثبت سفارش برای مشتری</h2>
            <p class="mt-0.5 text-xs text-zinc-500">سفارش تلفنی یا حضوری را از اینجا ثبت کنید</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="admin-btn-secondary text-xs">بازگشت</a>
    </div>

    <div class="space-y-6 p-6">
        <div class="grid gap-4 md:grid-cols-2">
                        <div>
                <label class="admin-field-label">مشتری</label>
                @php $oldCustomer = old('user_id') ? \App\Models\User::find(old('user_id')) : null; @endphp
                <div class="relative"
                     x-data="{
                         query: '',
                         results: [],
                         open: false,
                         searched: false,
                         selectedId: '{{ old('user_id') }}',
                         selectedLabel: '{{ $oldCustomer ? addslashes($oldCustomer->name).' — '.addslashes($oldCustomer->phone ?? $oldCustomer->email) : '' }}',
                         async search() {
                             if (this.query.trim().length < 2) { this.results = []; this.open = false; return; }
                             const res = await fetch('{{ route('admin.users.search') }}?q=' + encodeURIComponent(this.query));
                             this.results = await res.json();
                             this.open = true; this.searched = true;
                         },
                         pick(u) {
                             this.selectedId = u.id;
                             this.selectedLabel = u.name + ' — ' + (u.phone || u.email || '');
                             this.query = ''; this.results = []; this.open = false;
                         },
                         clear() { this.selectedId = ''; this.selectedLabel = ''; this.query = ''; this.results = []; this.open = false; }
                     }"
                     @click.away="open = false">

                    <input type="hidden" name="user_id" :value="selectedId">

                    {{-- مشتری انتخاب‌شده --}}
                    <div x-show="selectedId !== ''" x-cloak
                         class="flex items-center justify-between gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm text-indigo-900">
                        <span x-text="selectedLabel" class="truncate"></span>
                        <button type="button" @click="clear()" class="shrink-0 text-indigo-400 hover:text-red-500" title="تغییر مشتری">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- باکس جستجو --}}
                    <input type="text" x-model="query" x-show="selectedId === ''"
                           @input.debounce.300ms="search()"
                           @focus="if (results.length) open = true"
                           class="admin-input w-full" dir="rtl"
                           placeholder="نام، ایمیل یا شماره موبایل مشتری را بنویسید (حداقل ۲ حرف)...">

                    {{-- نتایج --}}
                    <div x-show="open && results.length" x-cloak
                         class="absolute z-30 mt-1 max-h-60 w-full overflow-auto rounded-xl border border-zinc-200 bg-white shadow-lg">
                        <template x-for="u in results" :key="u.id">
                            <button type="button" @click="pick(u)"
                                    class="block w-full border-b border-zinc-50 px-3 py-2.5 text-right text-sm transition last:border-0 hover:bg-indigo-50">
                                <span class="block font-semibold text-zinc-800" x-text="u.name"></span>
                                <span class="mt-0.5 block text-xs text-zinc-500" x-text="(u.phone ? u.phone + ' — ' : '') + u.email"></span>
                            </button>
                        </template>
                    </div>

                    <p x-show="searched && !results.length && selectedId === ''" x-clasp class="mt-1 text-xs text-zinc-400">مشتری‌ای پیدا نشد.</p>
                </div>
                @error('user_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="admin-field-label">روش ارسال</label>
                <select name="shipping_method_id" class="admin-select w-full" required>
                    @foreach($shippingMethods as $method)
                        <option value="{{ $method->id }}" @selected(old('shipping_method_id') == $method->id)>{{ $method->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div><label class="admin-field-label">نام گیرنده</label><input type="text" name="full_name" value="{{ old('full_name') }}" class="admin-input w-full" required></div>
            <div><label class="admin-field-label">موبایل</label><input type="text" name="phone" value="{{ old('phone') }}" class="admin-input w-full" dir="ltr" required></div>
            <div><label class="admin-field-label">استان</label><input type="text" name="province" value="{{ old('province') }}" class="admin-input w-full" required></div>
            <div><label class="admin-field-label">شهر</label><input type="text" name="city" value="{{ old('city') }}" class="admin-input w-full" required></div>
            <div class="md:col-span-2"><label class="admin-field-label">آدرس</label><textarea name="address" rows="2" class="admin-input w-full" required>{{ old('address') }}</textarea></div>
            <div><label class="admin-field-label">کد پستی</label><input type="text" name="postal_code" value="{{ old('postal_code') }}" class="admin-input w-full" dir="ltr" required></div>
            <div><label class="admin-field-label">یادداشت مشتری</label><input type="text" name="notes" value="{{ old('notes') }}" class="admin-input w-full"></div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-zinc-700">محصولات</h3>
                <button type="button" @click="rows.push({ product_id: '', quantity: 1, size: '', color: '' })" class="admin-btn-secondary text-xs">افزودن ردیف</button>
            </div>
            <template x-for="(row, index) in rows" :key="index">
                <div class="grid gap-3 md:grid-cols-5 mb-3 items-end">
                    <div class="md:col-span-2">
                        <label class="admin-field-label text-xs">محصول</label>
                        <select :name="'items[' + index + '][product_id]'" class="admin-select w-full" required>
                            <option value="">انتخاب...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ format_price($product->price) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="admin-field-label text-xs">تعداد</label>
                        <input type="number" :name="'items[' + index + '][quantity]'" x-model="row.quantity" min="1" class="admin-input w-full" required>
                    </div>
                    <div>
                        <label class="admin-field-label text-xs">سایز</label>
                        <input type="text" :name="'items[' + index + '][size]'" x-model="row.size" class="admin-input w-full">
                    </div>
                    <div>
                        <label class="admin-field-label text-xs">رنگ</label>
                        <input type="text" :name="'items[' + index + '][color]'" x-model="row.color" class="admin-input w-full">
                    </div>
                </div>
            </template>
        </div>

        <label class="flex items-center gap-2 text-sm text-zinc-700 cursor-pointer">
            <input type="checkbox" name="mark_as_paid" value="1" class="rounded border-zinc-300 text-indigo-600" @checked(old('mark_as_paid'))>
            <span>ثبت به‌عنوان پرداخت‌شده</span>
        </label>

        <div class="flex gap-3 border-t border-zinc-100 pt-6">
            <button type="submit" class="admin-btn-primary">ثبت سفارش</button>
            <a href="{{ route('admin.orders.index') }}" class="admin-btn-secondary">انصراف</a>
        </div>
    </div>
</form>
@endsection
