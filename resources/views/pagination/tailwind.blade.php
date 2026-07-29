@if ($paginator->hasPages())
    @php
        $pageBtn = 'inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-bold text-zinc-700 shadow-sm transition hover:border-zinc-300 hover:bg-zinc-50';
        $pageActive = 'inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-transparent bg-shop-primary px-3 text-sm font-bold text-white shadow-md';
        $pageDisabled = 'inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-zinc-100 bg-zinc-50 px-3 text-sm font-bold text-zinc-400';
        $arrowBtn = 'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-700 shadow-sm transition hover:border-zinc-300 hover:bg-zinc-50';
        $arrowDisabled = 'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-zinc-100 bg-zinc-50 text-zinc-400 cursor-not-allowed';
    @endphp

    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="w-full" dir="rtl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-center text-sm text-zinc-500 sm:text-right">
                @if ($paginator->firstItem())
                    {{ __('Showing :from to :to of :total results', [
                        'from' => to_persian_digits((string) $paginator->firstItem()),
                        'to' => to_persian_digits((string) $paginator->lastItem()),
                        'total' => to_persian_digits((string) $paginator->total()),
                    ]) }}
                @else
                    {{ __('Showing :from to :to of :total results', [
                        'from' => to_persian_digits('0'),
                        'to' => to_persian_digits('0'),
                        'total' => to_persian_digits((string) $paginator->total()),
                    ]) }}
                @endif
            </p>

            <div class="flex items-center justify-center gap-2 sm:justify-start">
                @if ($paginator->onFirstPage())
                    <span class="{{ $arrowDisabled }}" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $arrowBtn }}" aria-label="{{ __('pagination.previous') }}">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif

                <div class="flex items-center gap-1.5">
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="{{ $pageDisabled }}" aria-disabled="true">{{ to_persian_digits($element) }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span class="{{ $pageActive }}" aria-current="page">{{ to_persian_digits((string) $page) }}</span>
                                @else
                                    <a href="{{ $url }}" class="{{ $pageBtn }}" aria-label="{{ __('Go to page :page', ['page' => to_persian_digits((string) $page)]) }}">
                                        {{ to_persian_digits((string) $page) }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $arrowBtn }}" aria-label="{{ __('pagination.next') }}">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @else
                    <span class="{{ $arrowDisabled }}" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
