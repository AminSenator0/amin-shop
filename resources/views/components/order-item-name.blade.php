@props(['item', 'link' => null, 'linkClass' => 'font-bold text-shop-primary hover:underline'])

<div>
    @if($link)
        <a href="{{ $link }}" class="{{ $linkClass }}">{{ $item->product_name }}</a>
    @else
        <span class="font-bold">{{ $item->product_name }}</span>
    @endif
    @if($item->optionsLabel())
        <p class="mt-0.5 text-xs text-shop-muted">{{ $item->optionsLabel() }}</p>
    @endif
    @if($item->product_sku)
        <p class="mt-0.5 text-[11px] text-shop-muted" dir="ltr">SKU: {{ $item->product_sku }}</p>
    @endif
</div>
