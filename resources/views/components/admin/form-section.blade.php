@props(['title'])

<section {{ $attributes->merge(['class' => 'admin-form-section']) }}>
    <h3 class="admin-form-section-title">{{ $title }}</h3>
    <div class="grid gap-4 md:grid-cols-2">
        {{ $slot }}
    </div>
</section>
