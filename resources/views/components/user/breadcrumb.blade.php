@props(['items' => []])

@if(count($items))
<nav aria-label="مسیر صفحه" class="user-breadcrumb">
    <ol class="flex flex-wrap items-center gap-1.5 text-sm text-shop-muted">
        @foreach($items as $index => $item)
            @if($index > 0)
                <li aria-hidden="true" class="px-0.5 text-shop-border/80">‹</li>
            @endif
            <li>
                @if(!empty($item['url']) && $index < count($items) - 1)
                    <a href="{{ $item['url'] }}" class="font-medium transition hover:text-shop-primary">{{ $item['label'] }}</a>
                @else
                    <span class="font-bold text-shop-text">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif
