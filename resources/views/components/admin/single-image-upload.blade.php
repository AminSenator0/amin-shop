@props([
    'name' => 'logo',
    'removeName' => 'remove_logo',
    'existingUrl' => null,
    'label' => 'تصویر',
    'hint' => null,
    'button' => 'انتخاب تصویر',
    'accept' => 'image/jpeg,image/png,image/webp',
])

<div
    {{ $attributes->merge(['class' => 'admin-single-image']) }}
    x-data="singleImageUpload({ existingUrl: @js($existingUrl) })"
>
    <label class="admin-field-label">{{ $label }}</label>

    <input type="hidden" name="{{ $removeName }}" :value="removed && hasExisting ? '1' : '0'">

    <input
        type="file"
        name="{{ $name }}"
        accept="{{ $accept }}"
        class="sr-only"
        x-ref="fileInput"
        @change="onFileChange($event)"
    >

    <div class="admin-single-image-body">
        <template x-if="preview">
            <div class="admin-single-image-preview">
                <img :src="preview" class="admin-single-image-img" alt="پیش‌نمایش {{ $label }}">
                <div class="admin-single-image-actions">
                    <button type="button" class="admin-single-image-btn" @click="pick()" title="تغییر تصویر">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </button>
                    <button type="button" class="admin-single-image-btn admin-single-image-btn-danger" @click="remove()" title="حذف تصویر">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </div>
        </template>

        <template x-if="!preview">
            <button type="button" class="admin-single-image-empty" @click="pick()">
                <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                </svg>
                <span class="text-xs font-medium text-zinc-500">{{ $button }}</span>
            </button>
        </template>

        <div class="min-w-0 flex-1 space-y-1.5">
            <p class="text-xs text-zinc-600" x-show="fileName" x-text="fileName"></p>
            @if($hint)
                <p class="admin-field-hint">{{ $hint }}</p>
            @endif
            @error($name)
                <p class="admin-field-error">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
