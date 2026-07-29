@php $faq = $faq ?? null; @endphp

<div class="grid gap-6 lg:grid-cols-2">
    <div class="admin-slider-panel space-y-5">
        <div class="admin-slider-panel-head">
            <div class="admin-slider-panel-icon">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg>
            </div>
            <div>
                <p class="admin-slider-panel-title">سوال و پاسخ</p>
                <p class="admin-slider-panel-desc">متن نمایشی در صفحه اصلی و صفحه FAQ</p>
            </div>
        </div>

        <div>
            <label for="question" class="admin-field-label">سوال <span class="text-rose-500">*</span></label>
            <input type="text" name="question" id="question" value="{{ old('question', $faq?->question ?? '') }}" class="admin-input w-full" placeholder="مثلاً: چگونه سفارش خود را پیگیری کنم؟" required>
            @error('question')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="answer" class="admin-field-label">پاسخ <span class="text-rose-500">*</span></label>
            <textarea name="answer" id="answer" rows="6" class="admin-input w-full resize-y leading-relaxed" placeholder="پاسخ کامل و واضح برای مشتری بنویسید..." required>{{ old('answer', $faq?->answer ?? '') }}</textarea>
            @error('answer')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="admin-slider-panel space-y-5">
        <div class="admin-slider-panel-head">
            <div class="admin-slider-panel-icon">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" /></svg>
            </div>
            <div>
                <p class="admin-slider-panel-title">تنظیمات نمایش</p>
                <p class="admin-slider-panel-desc">ترتیب و وضعیت انتشار</p>
            </div>
        </div>

        <div class="max-w-xs">
            <label for="sort_order" class="admin-field-label">ترتیب نمایش</label>
            <x-admin.number-stepper name="sort_order" id="sort_order" :value="old('sort_order', $faq?->sort_order ?? 0)" :min="0" />
            <p class="admin-field-hint">عدد کمتر = نمایش بالاتر در لیست</p>
            @error('sort_order')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>

        <label class="admin-slider-active">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $faq?->is_active ?? true))>
            <div>
                <p class="admin-slider-active-title">فعال</p>
                <p class="admin-slider-active-desc">نمایش در صفحه اصلی و صفحه سوالات متداول</p>
            </div>
        </label>
    </div>
</div>
