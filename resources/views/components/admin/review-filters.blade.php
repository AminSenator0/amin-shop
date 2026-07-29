@props([
    'stats',
    'activeStatus' => '',
    'filterParams' => [],
])

@php
    $tabs = [
        [
            'key' => '',
            'label' => 'همه',
            'count' => $stats['total'],
            'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>',
            'activeClass' => 'bg-indigo-600 text-white shadow-sm',
            'inactiveClass' => 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200',
        ],
        [
            'key' => 'pending',
            'label' => 'در انتظار',
            'count' => $stats['pending'],
            'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            'activeClass' => 'bg-amber-500 text-white shadow-sm',
            'inactiveClass' => 'bg-amber-50 text-amber-700 hover:bg-amber-100',
        ],
        [
            'key' => 'approved',
            'label' => 'تایید شده',
            'count' => $stats['approved'],
            'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            'activeClass' => 'bg-emerald-600 text-white shadow-sm',
            'inactiveClass' => 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
        ],
    ];
@endphp

<div class="mb-6 space-y-4">
  <div class="admin-page-header !mb-0">
    <form method="GET" class="admin-search-form flex-1">
      <input
        type="search"
        name="search"
        value="{{ request('search') }}"
        placeholder="جستجوی کاربر، محصول یا متن نظر..."
        class="admin-input admin-search-input"
      >
      @if($activeStatus)
        <input type="hidden" name="status" value="{{ $activeStatus }}">
      @endif
      @if(request('rating'))
        <input type="hidden" name="rating" value="{{ request('rating') }}">
      @endif
      <button type="submit" class="admin-btn-secondary admin-search-btn">جستجو</button>
      @if(count(request()->query()) > 0)
        <a href="{{ route('admin.reviews.index') }}" class="admin-btn-secondary admin-search-btn admin-search-clear">حذف فیلتر</a>
      @endif
    </form>

    <form method="GET" class="admin-status-filter shrink-0">
      @if(request('search'))
        <input type="hidden" name="search" value="{{ request('search') }}">
      @endif
      @if($activeStatus)
        <input type="hidden" name="status" value="{{ $activeStatus }}">
      @endif
      <select name="rating" class="admin-select" data-auto-submit>
        <option value="">همه امتیازها</option>
        @for($i = 5; $i >= 1; $i--)
          <option value="{{ $i }}" @selected(request('rating') == $i)>{{ $i }} ستاره</option>
        @endfor
      </select>
    </form>
  </div>

  <div class="flex flex-wrap gap-2">
    @foreach($tabs as $tab)
      @php
        $isActive = $activeStatus === $tab['key'];
        $href = $tab['key'] === ''
          ? route('admin.reviews.index', $filterParams)
          : route('admin.reviews.index', array_merge($filterParams, ['status' => $tab['key']]));
      @endphp
      <a
        href="{{ $href }}"
        @if($isActive) aria-current="page" @endif
        class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition-all duration-150 {{ $isActive ? $tab['activeClass'] : $tab['inactiveClass'] }}"
      >
        {!! $tab['icon'] !!}
        {{ $tab['label'] }}
        <span class="inline-flex min-w-[1.35rem] items-center justify-center rounded-full px-1.5 py-0.5 text-[11px] font-black {{ $isActive ? 'bg-white/25 text-inherit' : 'bg-white/80 text-inherit' }}">
          {{ $tab['count'] }}
        </span>
      </a>
    @endforeach
  </div>
</div>
