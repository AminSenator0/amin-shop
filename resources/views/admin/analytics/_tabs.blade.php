@php
    $tabs = [
        'admin.analytics.visits' => ['label' => 'نمودار بازدیدها', 'route' => route('admin.analytics.visits')],
        'admin.analytics.products' => ['label' => 'آمار محصولات', 'route' => route('admin.analytics.products')],
        'admin.analytics.blog' => ['label' => 'آمار مقالات', 'route' => route('admin.analytics.blog')],
    ];
@endphp

<div class="mb-6 flex flex-wrap gap-2" role="tablist" aria-label="بخش‌های آمار">
    @foreach($tabs as $routeName => $tab)
        @php $active = request()->routeIs($routeName); @endphp
        <a href="{{ $tab['route'] }}" role="tab" aria-selected="{{ $active ? 'true' : 'false' }}"
           class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition
           {{ $active
               ? 'bg-[var(--surface-primary)] text-white shadow-md'
               : 'bg-white text-zinc-600 ring-1 ring-zinc-200 hover:bg-zinc-50 hover:text-zinc-900' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>