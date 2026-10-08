@php
    /** @var string|int $cfIndex  @var string|int $optIndex  @var array $opt  @var array $galleryImages */
@endphp
<div class="cf-option-row grid grid-cols-2 md:grid-cols-12 gap-2 items-end rounded-md bg-white border border-zinc-200 p-2.5">
    <div class="col-span-2 md:col-span-3">
        <label class="admin-field-label !mb-1">نام گزینه <span class="text-rose-500">*</span></label>
        <input type="text" name="custom_fields[{{ $cfIndex }}][option_rows][{{ $optIndex }}][value]" value="{{ $opt['value'] ?? '' }}" class="admin-input w-full" placeholder="مثلاً: ساده">
    </div>
    <div class="col-span-1 md:col-span-2">
        <label class="admin-field-label !mb-1">موجودی <span class="text-zinc-400 text-[10px] font-normal">(خالی=محصول)</span></label>
        <input type="number" name="custom_fields[{{ $cfIndex }}][option_rows][{{ $optIndex }}][stock]" value="{{ $opt['stock'] ?? '' }}" min="0" class="admin-input w-full" placeholder="—">
    </div>
    <div class="col-span-1 md:col-span-2">
        <label class="admin-field-label !mb-1">قیمت <span class="text-zinc-400 text-[10px] font-normal">(خالی=پایه)</span></label>
        <input type="number" name="custom_fields[{{ $cfIndex }}][option_rows][{{ $optIndex }}][price]" value="{{ $opt['price'] ?? '' }}" min="0" class="admin-input w-full" placeholder="—">
    </div>
    <div class="col-span-2 md:col-span-4">
        <label class="admin-field-label !mb-1">تصویر گزینه <span class="text-zinc-400 text-[10px] font-normal">(اختیاری)</span></label>
        <div class="flex items-center gap-2">
            <input type="file" name="custom_fields[{{ $cfIndex }}][option_rows][{{ $optIndex }}][image_file]" accept="image/*" class="admin-input w-full !py-1.5 text-xs">
            @if(!empty($galleryImages))
                <select class="admin-select w-full !py-1.5 text-xs cf-gallery-select" data-target="cf-opt-img-{{ $cfIndex }}-{{ $optIndex }}">
                    <option value="">از گالری…</option>
                    @foreach($galleryImages as $path)
                        <option value="{{ $path }}" @selected(($opt['image'] ?? '') === $path)>{{ basename($path) }}</option>
                    @endforeach
                </select>
            @endif
        </div>
        <input type="hidden" name="custom_fields[{{ $cfIndex }}][option_rows][{{ $optIndex }}][image]" id="cf-opt-img-{{ $cfIndex }}-{{ $optIndex }}" value="{{ $opt['image'] ?? '' }}">
    </div>
    <div class="col-span-2 md:col-span-1 flex justify-end">
        <button type="button" class="cf-remove-option text-rose-500 hover:text-rose-700 p-1.5 transition-colors" title="حذف گزینه">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>