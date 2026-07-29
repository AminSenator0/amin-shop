@php
    $post = $post ?? null;
    $existingImage = $post?->image ? asset('storage/'.$post->image) : null;
@endphp

<div class="space-y-6">
    <x-admin.form-section title="محتوا">
        <div class="md:col-span-2">
            <label for="title" class="admin-field-label">عنوان</label>
            <input type="text" name="title" id="title" value="{{ old('title', $post?->title ?? '') }}" class="admin-input w-full" required>
            @error('title')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="slug" class="admin-field-label">نامک (اختیاری)</label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $post?->slug ?? '') }}" class="admin-input w-full text-left" dir="ltr">
            @error('slug')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="published_at" class="admin-field-label">تاریخ انتشار</label>
            <input type="text" name="published_at" id="published_at" data-jalali-date value="{{ old('published_at', $post?->published_at ? format_jalali($post->published_at) : '') }}" class="admin-input w-full" placeholder="۱۴۰۳/۱۰/۱۵">
            @error('published_at')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="excerpt" class="admin-field-label">خلاصه</label>
            <textarea name="excerpt" id="excerpt" rows="2" class="admin-input w-full resize-y">{{ old('excerpt', $post?->excerpt ?? '') }}</textarea>
            @error('excerpt')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="content" class="admin-field-label">متن مقاله</label>
            <textarea name="content" id="content" rows="10" class="admin-input w-full resize-y font-mono text-sm leading-relaxed" required>{{ old('content', $post?->content ?? '') }}</textarea>
            @error('content')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <x-admin.single-image-upload name="image" remove-name="remove_image" :existing-url="$existingImage" label="تصویر شاخص" button="انتخاب تصویر" />
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="تنظیمات">
        <div class="max-w-xs">
            <label for="sort_order" class="admin-field-label">ترتیب نمایش</label>
            <x-admin.number-stepper name="sort_order" id="sort_order" :value="old('sort_order', $post?->sort_order ?? 0)" :min="0" />
        </div>
        <label class="flex cursor-pointer items-center gap-2.5 md:col-span-2">
            <input type="checkbox" name="is_published" value="1" class="admin-checkbox" @checked(old('is_published', $post?->is_published ?? true))>
            <span class="text-sm font-medium text-zinc-700">منتشر شده — نمایش در صفحه اصلی</span>
        </label>
    </x-admin.form-section>
</div>
