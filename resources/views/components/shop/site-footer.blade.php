@props(['showAccountLinks' => false])

<footer {{ $attributes->merge(['class' => 'mt-16 border-t border-shop-border/60 bg-shop-surface/80']) }}>
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 md:grid-cols-2 {{ $showAccountLinks ? 'lg:grid-cols-4' : 'lg:grid-cols-4' }}">
            <div class="lg:col-span-1">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <x-shop.store-logo size="lg" />
                    <span class="text-lg font-black text-shop-text">{{ $store['name'] }}</span>
                </a>
                @if($store['footerDescription'])
                    <p class="mt-3 text-sm leading-7 text-shop-muted">{{ $store['footerDescription'] }}</p>
                @else
                    <p class="mt-3 text-sm leading-7 text-shop-muted">خرید آنلاین مطمئن با ارسال سریع، پشتیبانی پاسخگو و ضمانت اصالت کالا.</p>
                @endif
                @if($store['socialInstagram'] || $store['socialTelegram'] || $store['whatsappUrl'])
                    <div class="mt-4 flex items-center gap-2">
                        @if($store['socialInstagram'])
                            <a href="{{ $store['socialInstagram'] }}" target="_blank" rel="noopener" class="shop-social-link" title="اینستاگرام">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                            </a>
                        @endif
                        @if($store['socialTelegram'])
                            <a href="{{ $store['socialTelegram'] }}" target="_blank" rel="noopener" class="shop-social-link" title="تلگرام">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                            </a>
                        @endif
                        @if($store['whatsappUrl'])
                            <a href="{{ $store['whatsappUrl'] }}" target="_blank" rel="noopener" class="shop-social-link shop-social-link-whatsapp" title="واتساپ">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <div>
                <h3 class="text-sm font-black text-shop-text">دسترسی سریع</h3>
                <ul class="mt-4 space-y-2.5 text-sm text-shop-muted">
                    <li><a href="{{ route('products.index') }}" class="hover:text-shop-primary transition">محصولات</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-shop-primary transition">مقالات</a></li>
                    <li><a href="{{ route('pages.faq') }}" class="hover:text-shop-primary transition">سوالات متداول</a></li>
                    <li><a href="{{ route('pages.about') }}" class="hover:text-shop-primary transition">درباره ما</a></li>
                    <li><a href="{{ route('pages.rules') }}" class="hover:text-shop-primary transition">قوانین و مقررات</a></li>
                    <li><a href="{{ route('pages.contact') }}" class="hover:text-shop-primary transition">تماس با ما</a></li>
                    <li><a href="{{ route('orders.track') }}" class="hover:text-shop-primary transition">پیگیری سفارش</a></li>
                </ul>
            </div>

            @if($showAccountLinks)
                <div>
                    <h3 class="text-sm font-black text-shop-text">حساب کاربری</h3>
                    <ul class="mt-4 space-y-2.5 text-sm text-shop-muted">
                        <li><a href="{{ route('user.dashboard') }}" class="hover:text-shop-primary transition">داشبورد</a></li>
                        <li><a href="{{ route('user.orders.index') }}" class="hover:text-shop-primary transition">سفارشات من</a></li>
                        <li><a href="{{ route('user.wishlist.index') }}" class="hover:text-shop-primary transition">علاقه‌مندی‌ها</a></li>
                        <li><a href="{{ route('user.cart.index') }}" class="hover:text-shop-primary transition">سبد خرید</a></li>
                        <li><a href="{{ route('user.messages.index') }}" class="hover:text-shop-primary transition">پیام‌های من</a></li>
                        <li><a href="{{ route('profile.edit') }}" class="hover:text-shop-primary transition">پروفایل</a></li>
                    </ul>
                </div>
            @endif

            <div>
                <h3 class="text-sm font-black text-shop-text">تماس با ما</h3>
                <ul class="mt-4 space-y-3 text-sm text-shop-muted">
                    @if($store['contactPhone'])
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-shop-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                            <span dir="ltr">{{ $store['contactPhone'] }}</span>
                        </li>
                    @endif
                    @if($store['contactEmail'])
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-shop-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75"/></svg>
                            <a href="mailto:{{ $store['contactEmail'] }}" class="hover:text-shop-primary transition" dir="ltr">{{ $store['contactEmail'] }}</a>
                        </li>
                    @endif
                    @if($store['contactHours'])
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-shop-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ $store['contactHours'] }}</span>
                        </li>
                    @endif
                    @if($store['contactAddress'])
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-shop-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            <span>{{ $store['contactAddress'] }}</span>
                        </li>
                    @endif
                    @if($store['mapsUrl'])
                        <li>
                            <a href="{{ $store['mapsUrl'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-shop-primary hover:underline">
                                مشاهده روی نقشه
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            @if($store['newsletterEnabled'] && ! $showAccountLinks)
                <div>
                    <h3 class="text-sm font-black text-shop-text">خبرنامه</h3>
                    <p class="mt-2 text-sm text-shop-muted">از تخفیف‌ها و پیشنهادهای ویژه باخبر شوید.</p>
                    <form method="POST" action="{{ route('newsletter.subscribe') }}" class="mt-4 flex flex-col gap-2 sm:flex-row">
                        @csrf
                        <input type="email" name="email" placeholder="ایمیل شما" class="input-shop w-full px-3 py-2.5 text-sm" required>
                        <button type="submit" class="btn-primary shrink-0 !rounded-xl !px-4 !py-2.5 !text-sm">عضویت</button>
                    </form>
                </div>
            @endif
        </div>

        <div class="mt-10 border-t border-shop-border/60 pt-6 text-center text-xs text-shop-muted">
            &copy; {{ format_jalali(now(), 'Y', false) }} {{ $store['name'] }} — تمامی حقوق محفوظ است.
        </div>
    </div>
</footer>
