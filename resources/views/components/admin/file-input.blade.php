@props([
    'name',
    'multiple' => false,
    'accept' => 'image/*',
    'button' => 'انتخاب فایل',
    'empty' => 'فایلی انتخاب نشده',
    'required' => false,
])

<label {{ $attributes->merge(['class' => 'admin-file-input']) }}>
    <input
        type="file"
        name="{{ $name }}"
        accept="{{ $accept }}"
        @if($multiple) multiple @endif
        @if($required) required @endif
        class="sr-only"
        data-admin-file
        @if($multiple) data-admin-file-multiple @endif
    >
    <span class="admin-file-input-btn">{{ $button }}</span>
    <span class="admin-file-input-name" data-file-name>{{ $empty }}</span>
</label>
