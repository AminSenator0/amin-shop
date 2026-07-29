@php
    $items = [
        ['route' => 'user.dashboard', 'pattern' => 'user.dashboard', 'label' => 'داشبورد', 'icon' => 'home'],
        ['route' => 'user.orders.index', 'pattern' => 'user.orders.*', 'label' => 'سفارشات من', 'icon' => 'orders'],
        ['route' => 'user.returns.index', 'pattern' => 'user.returns.*', 'label' => 'مرجوعی‌ها', 'icon' => 'return', 'badge' => $openReturnsCount ?? 0],
        ['route' => 'user.wishlist.index', 'pattern' => 'user.wishlist.*', 'label' => 'علاقه‌مندی‌ها', 'icon' => 'heart', 'badge' => $wishlistCount ?? 0],
        ['route' => 'user.cart.index', 'pattern' => 'user.cart.*', 'label' => 'سبد خرید', 'icon' => 'cart', 'badge' => $cartCount ?? 0],
        ['route' => 'user.reviews.index', 'pattern' => 'user.reviews.*', 'label' => 'نظرات من', 'icon' => 'star'],
        ['route' => 'user.messages.index', 'pattern' => 'user.messages.*', 'label' => 'پیام‌های من', 'icon' => 'message', 'badge' => $unreadUserMessagesCount ?? 0],
        ['route' => 'user.coupons.index', 'pattern' => 'user.coupons.*', 'label' => 'کدهای تخفیف', 'icon' => 'coupon'],
        ['route' => 'user.addresses.index', 'pattern' => 'user.addresses.*', 'label' => 'آدرس‌ها', 'icon' => 'location'],
        ['route' => 'profile.edit', 'pattern' => 'profile.*', 'label' => 'پروفایل', 'icon' => 'user'],
    ];
@endphp

<nav {{ $attributes->merge(['class' => 'user-sidebar-nav']) }}>
    <div class="user-sidebar-nav-list">
        @foreach($items as $item)
            <a
                href="{{ route($item['route']) }}"
                @class(['user-nav-link', 'is-active' => request()->routeIs($item['pattern'])])
            >
                <span class="user-nav-icon" aria-hidden="true">
                    @include('components.user.sidebar-icon', ['name' => $item['icon']])
                </span>
                <span class="user-nav-label">{{ $item['label'] }}</span>
                @if(!empty($item['badge']) && $item['badge'] > 0)
                    <span class="user-nav-badge">{{ $item['badge'] > 99 ? '۹۹+' : $item['badge'] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <form method="POST" action="{{ route('logout') }}" class="user-sidebar-logout">
        @csrf
        <button type="submit" class="user-nav-link w-full text-shop-muted hover:bg-rose-50 hover:text-rose-600">
            <span class="user-nav-icon" aria-hidden="true">
                @include('components.user.sidebar-icon', ['name' => 'logout'])
            </span>
            <span class="user-nav-label">خروج از حساب</span>
        </button>
    </form>
</nav>
