@php
    $tabs = [
        'admin.analytics.visits' => [
            'label' => 'نمودار بازدیدها',
            'route' => route('admin.analytics.visits'),
            'icon' => 'visits',
            'active' => 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-200/60',
            'inactive' => 'bg-indigo-50 text-indigo-700 border-indigo-100 hover:bg-indigo-100 hover:border-indigo-200',
        ],

        'admin.analytics.products' => [
            'label' => 'آمار محصولات',
            'route' => route('admin.analytics.products'),
            'icon' => 'products',
            'active' => 'bg-emerald-600 text-white border-emerald-600 shadow-lg shadow-emerald-200/60',
            'inactive' => 'bg-emerald-50 text-emerald-700 border-emerald-100 hover:bg-emerald-100 hover:border-emerald-200',
        ],

        'admin.analytics.blog' => [
            'label' => 'آمار مقالات',
            'route' => route('admin.analytics.blog'),
            'icon' => 'blog',
            'active' => 'bg-orange-500 text-white border-orange-500 shadow-lg shadow-orange-200/60',
            'inactive' => 'bg-orange-50 text-orange-700 border-orange-100 hover:bg-orange-100 hover:border-orange-200',
        ],
    ];
@endphp

<div class="mb-6" dir="rtl">
    <div
        class="flex flex-wrap gap-3 rounded-2xl border border-zinc-200 bg-zinc-50/80 p-3"
        role="tablist"
        aria-label="بخش‌های آمار"
    >
        @foreach($tabs as $routeName => $tab)
            @php
                $active = request()->routeIs($routeName);
            @endphp

            <a
                href="{{ $tab['route'] }}"
                role="tab"
                aria-selected="{{ $active ? 'true' : 'false' }}"
                @if($active) aria-current="page" @endif
                class="group inline-flex min-h-[50px] flex-1 items-center justify-center gap-2.5 rounded-xl border px-4 py-3 text-sm font-bold transition-all duration-200 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 focus-visible:ring-offset-2
                {{ $active ? $tab['active'] : $tab['inactive'] }}"
            >
                {{-- آیکون --}}
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                    {{ $active ? 'bg-white/20 text-white' : 'bg-white/80' }}">

                    @if($tab['icon'] === 'visits')
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 3v18h18M7 14l4-4 4 4 6-7"
                            />
                        </svg>

                    @elseif($tab['icon'] === 'products')
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m12 3 9 5-9 5-9-5 9-5Z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m3 12 9 5 9-5M3 16l9 5 9-5"
                            />
                        </svg>

                    @elseif($tab['icon'] === 'blog')
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <rect
                                x="3"
                                y="3"
                                width="18"
                                height="18"
                                rx="2.5"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M7 8h10M7 12h10M7 16h6"
                            />
                        </svg>
                    @endif
                </span>

                {{-- عنوان دکمه --}}
                <span class="whitespace-nowrap">
                    {{ $tab['label'] }}
                </span>

                {{-- نشان صفحه فعال --}}
                @if($active)
                    <span class="mr-auto flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/20">
                        <svg
                            class="h-3.5 w-3.5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m5 12 4 4L19 6"
                            />
                        </svg>
                    </span>
                @endif
            </a>
        @endforeach
    </div>
</div>