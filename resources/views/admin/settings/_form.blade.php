@php
    use App\Support\StoreSettings;

    $existingLogo = $settings['logo'] ? asset('storage/'.$settings['logo']) : null;
    $existingFavicon = $settings['favicon'] ? asset('storage/'.$settings['favicon']) : null;
    $trustBadges = old('trust_badges', $settings['trust_badges'] ?? []);
    $paletteLabels = StoreSettings::paletteLabels();
    $colorPresets = StoreSettings::colorPresets();
    $activeColorPreset = old(
        'color_preset',
        StoreSettings::resolveColorPresetId(
            old('primary_color', $settings['primary_color']),
            old('accent_color', $settings['accent_color'])
        )
    );
@endphp

@if($errors->any())
    <div class="admin-alert-error mb-6">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <div>
            <p class="font-bold">لطفاً خطاهای فرم را بررسی کنید</p>
            <ul class="mt-1 list-inside list-disc text-sm">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    </div>
@endif

<div
    class="admin-settings-layout"
    x-data="{
        tab: @js(in_array(request('tab'), ['identity', 'appearance', 'contact', 'content', 'business', 'payment', 'sms', 'mail', 'auth'], true) ? request('tab') : 'identity'),
        activeTab: @js(in_array(request('tab'), ['identity', 'appearance', 'contact', 'content', 'business', 'payment', 'sms', 'mail', 'auth'], true) ? request('tab') : 'identity'),
        paletteLabels: @js($paletteLabels),
        colorPresets: @js($colorPresets),
        selectedPreset: @js($activeColorPreset),
        storeName: @js(old('store_name', $settings['store_name'])),
        tagline: @js(old('tagline', $settings['tagline'])),
        currency: @js(old('currency', $settings['currency'])),
        primaryColor: @js(StoreSettings::colorsFromPreset($activeColorPreset)['primary']),
        accentColor: @js(StoreSettings::colorsFromPreset($activeColorPreset)['accent']),
        footerDescription: @js(old('footer_description', $settings['footer_description'])),
        contactEmail: @js(old('contact_email', $settings['contact_email'])),
        contactPhone: @js(old('contact_phone', $settings['contact_phone'])),
        contactAddress: @js(old('contact_address', $settings['contact_address'])),
        contactHours: @js(old('contact_hours', $settings['contact_hours'])),
        logoPreview: @js($existingLogo),
        logoRemoved: false,
        logoFileName: '',
        faviconPreview: @js($existingFavicon),
        faviconRemoved: false,
        pickLogo() { this.$refs.logoInput.click(); },
        pickFavicon() { this.$refs.faviconInput.click(); },
        onLogoChange(e) {
            const f = e.target.files?.[0]; if (!f) return;
            if (this.logoPreview?.startsWith('blob:')) URL.revokeObjectURL(this.logoPreview);
            this.logoPreview = URL.createObjectURL(f); this.logoFileName = f.name; this.logoRemoved = false;
        },
        onFaviconChange(e) {
            const f = e.target.files?.[0]; if (!f) return;
            if (this.faviconPreview?.startsWith('blob:')) URL.revokeObjectURL(this.faviconPreview);
            this.faviconPreview = URL.createObjectURL(f); this.faviconRemoved = false;
        },
        removeLogo() {
            if (this.logoPreview?.startsWith('blob:')) URL.revokeObjectURL(this.logoPreview);
            this.logoPreview = null; this.$refs.logoInput.value = ''; this.logoRemoved = @js((bool) $existingLogo);
        },
        removeFavicon() {
            if (this.faviconPreview?.startsWith('blob:')) URL.revokeObjectURL(this.faviconPreview);
            this.faviconPreview = null; this.$refs.faviconInput.value = ''; this.faviconRemoved = @js((bool) $existingFavicon);
        },
        setCurrency(v) { this.currency = v; },
        selectColorPreset(id) {
            const preset = this.colorPresets[id];
            if (!preset) return;
            this.selectedPreset = id;
            this.primaryColor = preset.primary;
            this.accentColor = preset.accent;
            this.syncDerivedTheme();
        },
        get themePalette() {
            return window.deriveThemePalette(this.primaryColor, this.accentColor);
        },
        syncDerivedTheme() {
            window.applyThemePalette(this.primaryColor, this.accentColor);
        },
    }"
    x-init="syncDerivedTheme()"
    @submit.window="activeTab = tab"
>
    <input type="hidden" name="active_tab" :value="tab">
    <div class="admin-settings-main space-y-5">
        <div class="admin-settings-tabs">
            @foreach(['identity' => 'هویت', 'appearance' => 'ظاهر', 'contact' => 'تماس', 'content' => 'محتوا', 'business' => 'فروش', 'payment' => 'پرداخت', 'sms' => 'پیامک', 'mail' => 'ایمیل', 'auth' => 'ورود'] as $key => $label)
                <button type="button" class="admin-settings-tab" :class="tab === @js($key) && 'is-active'" @click="tab = @js($key); activeTab = @js($key)">{{ $label }}</button>
            @endforeach
        </div>

        {{-- هویت --}}
        <div x-show="tab === 'identity'" class="admin-slider-panel space-y-5">
            <div class="admin-slider-panel-head">
                <div class="admin-slider-panel-icon"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72"/></svg></div>
                <div><p class="admin-slider-panel-title">هویت فروشگاه</p><p class="admin-slider-panel-desc">نام، شعار و لوگو در هدر و فوتر نمایش داده می‌شود</p></div>
            </div>
            <div>
                <label for="store_name" class="admin-field-label">نام فروشگاه <span class="text-rose-500">*</span></label>
                <input type="text" name="store_name" id="store_name" x-model="storeName" class="admin-input w-full" required>
                @error('store_name')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="tagline" class="admin-field-label">شعار</label>
                <input type="text" name="tagline" id="tagline" x-model="tagline" class="admin-input w-full">
                @error('tagline')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="footer_description" class="admin-field-label">توضیح فوتر</label>
                <textarea name="footer_description" id="footer_description" rows="2" x-model="footerDescription" class="admin-input w-full resize-y" placeholder="معرفی کوتاه فروشگاه در فوتر"></textarea>
                @error('footer_description')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>
            @include('admin.settings._image-field', ['field' => 'logo', 'label' => 'لوگو', 'hint' => 'در هدر، فوتر و فاکتور'])
            @include('admin.settings._image-field', ['field' => 'favicon', 'label' => 'فاویکون', 'hint' => 'آیکون تب مرورگر — PNG یا WebP'])
        </div>

        {{-- ظاهر --}}
        <div x-show="tab === 'appearance'" x-cloak class="admin-slider-panel space-y-5">
            <input type="hidden" name="color_preset" x-model="selectedPreset">
            <div>
                <label class="admin-field-label">پالت رنگ فروشگاه</label>
                <p class="admin-field-hint mb-3">یکی از ۵ پالت را انتخاب کنید — بقیه رنگ‌ها (پس‌زمینه، متن، حاشیه، بنر و دکمه) خودکار ساخته می‌شود.</p>
                <div class="admin-color-presets">
                    @foreach($colorPresets as $id => $preset)
                        <button
                            type="button"
                            class="admin-color-preset"
                            :class="selectedPreset === @js($id) && 'is-active'"
                            @click="selectColorPreset(@js($id))"
                        >
                            <span
                                class="admin-color-preset-swatch"
                                style="background: linear-gradient(135deg, {{ $preset['primary'] }} 0%, {{ $preset['accent'] }} 100%);"
                                aria-hidden="true"
                            ></span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-bold text-zinc-800">{{ $preset['name'] }}</span>
                                <span class="mt-0.5 block text-xs text-zinc-500">{{ $preset['description'] }}</span>
                                @if($id === StoreSettings::defaultColorPresetId())
                                    <span class="mt-1 inline-block whitespace-nowrap rounded-full bg-shop-primary/10 px-2 py-0.5 text-[10px] font-bold text-shop-primary">پیش‌فرض پروژه</span>
                                @endif
                            </span>
                            <span
                                class="admin-color-preset-check"
                                x-show="selectedPreset === @js($id)"
                                x-cloak
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </span>
                        </button>
                    @endforeach
                </div>
                @error('color_preset')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>
            <div class="rounded-xl border border-zinc-200 bg-zinc-50/60 p-4">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <p class="text-xs font-bold text-zinc-600">پالت مشتق‌شده (خودکار)</p>
                    <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-medium text-zinc-500 ring-1 ring-zinc-200">۱۵ رنگ</span>
                </div>
                <div class="grid grid-cols-3 gap-2 sm:grid-cols-5">
                    <template x-for="[key, label] in Object.entries(paletteLabels)" :key="key">
                        <div class="text-center">
                            <div class="mx-auto h-9 w-full rounded-lg border border-zinc-200 shadow-sm" :style="{ background: themePalette[key] }" :title="themePalette[key]"></div>
                            <p class="mt-1 text-[10px] font-medium text-zinc-500" x-text="label"></p>
                            <p class="font-mono text-[9px] text-zinc-400" dir="ltr" x-text="themePalette[key]"></p>
                        </div>
                    </template>
                </div>
            </div>
            <div>
                <label for="currency" class="admin-field-label">واحد پول <span class="text-rose-500">*</span></label>
                <input type="text" name="currency" id="currency" x-model="currency" class="admin-input max-w-xs w-full" required>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach(['تومان', 'ریال'] as $preset)
                        <button type="button" class="admin-settings-preset" :class="currency === @js($preset) && 'is-active'" @click="setCurrency(@js($preset))">{{ $preset }}</button>
                    @endforeach
                </div>
                @error('currency')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="meta_description" class="admin-field-label">توضیح SEO</label>
                <textarea name="meta_description" id="meta_description" rows="2" class="admin-input w-full resize-y">{{ old('meta_description', $settings['meta_description']) }}</textarea>
                <p class="admin-field-hint">برای موتورهای جستجو — صفحه اصلی و صفحات بدون توضیح اختصاصی</p>
                @error('meta_description')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-3">
                <p class="admin-field-label">نشان‌های اعتماد (صفحه اصلی)</p>
                @foreach($trustBadges as $i => $badge)
                    <input type="hidden" name="trust_badges[{{ $i }}][icon]" value="{{ $badge['icon'] ?? '' }}">
                    <div class="grid gap-2 rounded-xl border border-zinc-200 bg-zinc-50/60 p-3 sm:grid-cols-2">
                        <input type="text" name="trust_badges[{{ $i }}][title]" value="{{ old("trust_badges.$i.title", $badge['title'] ?? '') }}" class="admin-input w-full" placeholder="عنوان">
                        <input type="text" name="trust_badges[{{ $i }}][desc]" value="{{ old("trust_badges.$i.desc", $badge['desc'] ?? '') }}" class="admin-input w-full" placeholder="توضیح کوتاه">
                    </div>
                @endforeach
            </div>
        </div>

        {{-- تماس --}}
        <div x-show="tab === 'contact'" x-cloak class="admin-slider-panel space-y-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="contact_email" class="admin-field-label">ایمیل</label>
                    <input type="email" name="contact_email" id="contact_email" x-model="contactEmail" class="admin-input w-full text-left" dir="ltr">
                    @error('contact_email')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="contact_phone" class="admin-field-label">تلفن</label>
                    <input type="text" name="contact_phone" id="contact_phone" x-model="contactPhone" data-iran-phone-input class="admin-input w-full" dir="ltr" inputmode="numeric" placeholder="02112345678">
                    @error('contact_phone')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="contact_address" class="admin-field-label">آدرس</label>
                <textarea name="contact_address" id="contact_address" rows="2" x-model="contactAddress" class="admin-input w-full resize-y"></textarea>
                @error('contact_address')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="contact_hours" class="admin-field-label">ساعات پاسخگویی</label>
                <input type="text" name="contact_hours" id="contact_hours" x-model="contactHours" class="admin-input w-full">
                @error('contact_hours')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="social_instagram" class="admin-field-label">اینستاگرام</label>
                    <input type="url" name="social_instagram" id="social_instagram" value="{{ old('social_instagram', $settings['social_instagram']) }}" class="admin-input w-full text-left" dir="ltr" placeholder="https://instagram.com/...">
                    @error('social_instagram')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="social_telegram" class="admin-field-label">تلگرام</label>
                    <input type="url" name="social_telegram" id="social_telegram" value="{{ old('social_telegram', $settings['social_telegram']) }}" class="admin-input w-full text-left" dir="ltr" placeholder="https://t.me/...">
                    @error('social_telegram')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="social_whatsapp" class="admin-field-label">واتساپ (شماره موبایل)</label>
                    <input type="text" name="social_whatsapp" id="social_whatsapp" value="{{ old('social_whatsapp', $settings['social_whatsapp']) }}" data-phone-input class="admin-input w-full text-left" dir="ltr" placeholder="09121234567">
                    <p class="admin-field-hint">دکمه شناور واتساپ و لینک فوتر</p>
                    @error('social_whatsapp')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="maps_url" class="admin-field-label">لینک نقشه</label>
                    <input type="url" name="maps_url" id="maps_url" value="{{ old('maps_url', $settings['maps_url']) }}" class="admin-input w-full text-left" dir="ltr" placeholder="https://maps.google.com/...">
                    @error('maps_url')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <label class="admin-slider-active">
                <input type="checkbox" name="whatsapp_float_enabled" value="1" @checked(old('whatsapp_float_enabled', $settings['whatsapp_float_enabled']) == '1' || old('whatsapp_float_enabled') === '1')>
                <div><p class="admin-slider-active-title">دکمه شناور واتساپ</p><p class="admin-slider-active-desc">نمایش دکمه سبز واتساپ در گوشه صفحه فروشگاه</p></div>
            </label>
        </div>

        {{-- محتوا --}}
        <div x-show="tab === 'content'" x-cloak class="admin-slider-panel space-y-5">
            <div>
                <label for="about_content" class="admin-field-label">صفحه درباره ما</label>
                <textarea name="about_content" id="about_content" rows="8" class="admin-input w-full resize-y font-mono text-sm leading-relaxed">{{ old('about_content', $settings['about_content']) }}</textarea>
                <p class="admin-field-hint">هر پاراگراف با خط خالی جدا شود. خطوط با • برای لیست</p>
                @error('about_content')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="rules_content" class="admin-field-label">صفحه قوانین</label>
                <textarea name="rules_content" id="rules_content" rows="10" class="admin-input w-full resize-y font-mono text-sm leading-relaxed">{{ old('rules_content', $settings['rules_content']) }}</textarea>
                <p class="admin-field-hint">هر بخش: عنوان در خط اول، متن در خط بعد — بخش‌ها با خط خالی جدا شوند</p>
                @error('rules_content')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- فروش --}}
        <div x-show="tab === 'business'" x-cloak class="admin-slider-panel space-y-5">
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="return_days" class="admin-field-label">مهلت مرجوعی (روز)</label>
                    <input type="text" name="return_days" id="return_days" value="{{ old('return_days', $settings['return_days']) }}" data-numeric-input class="admin-input w-full" dir="ltr" inputmode="numeric" maxlength="2">
                    @error('return_days')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="min_order_amount" class="admin-field-label">حداقل سفارش</label>
                    <input type="text" name="min_order_amount" id="min_order_amount" value="{{ old('min_order_amount', $settings['min_order_amount'] ? format_number((int)$settings['min_order_amount'], false) : '0') }}" data-price-input class="admin-input w-full" dir="ltr" inputmode="numeric">
                    <p class="admin-field-hint">۰ = بدون محدودیت</p>
                    @error('min_order_amount')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="free_shipping_threshold" class="admin-field-label">آستانه ارسال رایگان</label>
                    <input type="text" name="free_shipping_threshold" id="free_shipping_threshold" value="{{ old('free_shipping_threshold', $settings['free_shipping_threshold'] ? format_number((int)$settings['free_shipping_threshold'], false) : '0') }}" data-price-input class="admin-input w-full" dir="ltr" inputmode="numeric">
                    @error('free_shipping_threshold')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="promo_banner_title" class="admin-field-label">عنوان بنر تبلیغاتی (صفحه اصلی)</label>
                <input type="text" name="promo_banner_title" id="promo_banner_title" value="{{ old('promo_banner_title', $settings['promo_banner_title']) }}" class="admin-input w-full" placeholder="مثلاً: ارسال رایگان برای سفارش‌های بالای {threshold}">
                <p class="admin-field-hint">از <code dir="ltr">{threshold}</code> برای نمایش خودکار مبلغ آستانه ارسال رایگان استفاده کنید</p>
                @error('promo_banner_title')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="promo_banner_text" class="admin-field-label">متن بنر تبلیغاتی</label>
                <input type="text" name="promo_banner_text" id="promo_banner_text" value="{{ old('promo_banner_text', $settings['promo_banner_text']) }}" class="admin-input w-full">
                @error('promo_banner_text')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>
            <label class="admin-slider-active">
                <input type="checkbox" name="auto_approve_reviews" value="1" @checked(old('auto_approve_reviews', $settings['auto_approve_reviews']) == '1')>
                <div><p class="admin-slider-active-title">تأیید خودکار نظرات</p><p class="admin-slider-active-desc">نظرات بلافاصله در صفحه محصول نمایش داده می‌شوند</p></div>
            </label>
            <label class="admin-slider-active">
                <input type="checkbox" name="newsletter_enabled" value="1" @checked(old('newsletter_enabled', $settings['newsletter_enabled']) == '1')>
                <div><p class="admin-slider-active-title">خبرنامه</p><p class="admin-slider-active-desc">نمایش فرم عضویت در فوتر و صفحه اصلی</p></div>
            </label>
            <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-4 space-y-3">
                <label class="admin-slider-active !border-amber-300 !bg-white">
                    <input type="checkbox" name="maintenance_mode" value="1" @checked(old('maintenance_mode', $settings['maintenance_mode']) == '1')>
                    <div><p class="admin-slider-active-title text-amber-900">حالت تعمیرات</p><p class="admin-slider-active-desc">فروشگاه برای مشتریان بسته می‌شود (مدیران دسترسی دارند)</p></div>
                </label>
                <div>
                    <label for="maintenance_message" class="admin-field-label">پیام تعمیرات</label>
                    <input type="text" name="maintenance_message" id="maintenance_message" value="{{ old('maintenance_message', $settings['maintenance_message']) }}" class="admin-input w-full">
                    @error('maintenance_message')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- پرداخت --}}
        <div
            x-show="tab === 'payment'"
            x-cloak
            class="admin-slider-panel space-y-5"
            x-data="{
                base: @js(old('zarinpal_callback_base_url', $settings['zarinpal_callback_base_url'] ?? '')),
                fallback: @js($settings['zarinpal_callback_fallback_base'] ?? rtrim((string) config('app.url'), '/')),
                path: '/payment/callback',
                normalizeBase(raw) {
                    let b = (raw || '').trim().replace(/\/+$/, '');
                    if (!b) return this.fallback;
                    if (!/^https?:\/\//i.test(b)) b = 'https://' + b;
                    try {
                        const u = new URL(b);
                        let out = u.protocol + '//' + u.host;
                        const p = (u.pathname || '').replace(/\/+$/, '');
                        if (p && p !== '/') out += p;
                        return out;
                    } catch (e) {
                        return this.fallback;
                    }
                },
                get effectiveBase() { return this.normalizeBase(this.base); },
                get callbackUrl() { return this.effectiveBase + this.path; },
                get domain() {
                    try { return new URL(this.effectiveBase).hostname; } catch (e) { return '—'; }
                }
            }"
        >
            <div class="admin-slider-panel-head">
                <div class="admin-slider-panel-icon"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m3 0h3m-9.75 0H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg></div>
                <div>
                    <p class="admin-slider-panel-title">درگاه زرین‌پال</p>
                    <p class="admin-slider-panel-desc">مرچنت‌کد، دامنهٔ callback و حالت تست/واقعی</p>
                </div>
            </div>

            @if(!empty($settings['zarinpal_configured']))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-800">
                    درگاه فعال است
                    @if(old('zarinpal_sandbox', $settings['zarinpal_sandbox']) == '1')
                        — حالت <strong>سندباکس (تست)</strong>
                    @else
                        — حالت <strong>واقعی (تولید)</strong>
                    @endif
                </div>
            @elseif(old('zarinpal_sandbox', $settings['zarinpal_sandbox'] ?? '1') == '1')
                <div class="rounded-xl border border-sky-200 bg-sky-50/80 px-4 py-3 text-sm text-sky-900">
                    مرچنت‌کد خالی است؛ در حالت سندباکس با مرچنت پیش‌فرض به درگاه تست زرین‌پال متصل می‌شود (بدون پرداخت آزمایشی داخلی).
                </div>
            @else
                <div class="rounded-xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-sm text-amber-900">
                    مرچنت‌کد تنظیم نشده — برای حالت واقعی حتماً مرچنت‌کد پنل زرین‌پال را وارد کنید.
                </div>
            @endif

            <div>
                <label for="zarinpal_merchant_id" class="admin-field-label">مرچنت‌کد (Merchant ID)</label>
                <input
                    type="text"
                    name="zarinpal_merchant_id"
                    id="zarinpal_merchant_id"
                    value="{{ old('zarinpal_merchant_id', $settings['zarinpal_merchant_id'] ?? '') }}"
                    class="admin-input w-full font-mono text-sm text-left"
                    dir="ltr"
                    maxlength="36"
                    autocomplete="off"
                    placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                >
                <p class="admin-field-hint">کد ۳۶ کاراکتری پنل زرین‌پال. در حالت سندباکس هر رشته ۳۶ کاراکتری دلخواه قابل قبول است.</p>
                @error('zarinpal_merchant_id')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="zarinpal_callback_base_url" class="admin-field-label">دامنه / آدرس پایهٔ Callback</label>
                <input
                    type="text"
                    name="zarinpal_callback_base_url"
                    id="zarinpal_callback_base_url"
                    x-model="base"
                    value="{{ old('zarinpal_callback_base_url', $settings['zarinpal_callback_base_url'] ?? '') }}"
                    class="admin-input w-full font-mono text-sm text-left"
                    dir="ltr"
                    maxlength="255"
                    autocomplete="off"
                    placeholder="https://shop.example.com"
                >
                <p class="admin-field-hint">
                    همین دامنه برای ساخت آدرس بازگشت از درگاه استفاده می‌شود.
                    اگر خالی بماند از <code dir="ltr" x-text="fallback">{{ $settings['zarinpal_callback_fallback_base'] ?? '' }}</code> استفاده می‌شود.
                </p>
                @error('zarinpal_callback_base_url')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>

            <div class="rounded-xl border border-sky-200 bg-sky-50/70 p-4 space-y-3 text-sm text-sky-950">
                <p class="font-bold text-sky-900">آدرس Callback فعلی (ارسال به زرین‌پال)</p>
                <code class="block break-all rounded-lg bg-white/80 px-3 py-2 font-mono text-xs text-left text-zinc-800 border border-sky-100" dir="ltr" x-text="callbackUrl">{{ $settings['zarinpal_callback_url'] ?? '' }}</code>
                <div class="space-y-1 text-xs leading-relaxed text-sky-900/90">
                    <p>
                        در پنل زرین‌پال، دامنهٔ درگاه را روی این مقدار ست کنید:
                        <strong class="font-mono" dir="ltr" x-text="domain">{{ $settings['zarinpal_callback_domain'] ?? '' }}</strong>
                    </p>
                    <p>دامنهٔ callback باید دقیقاً با دامنهٔ ثبت‌شده برای ترمینال یکی باشد؛ در غیر این صورت خطای «callback URL domain does not match» می‌گیرید.</p>
                </div>
            </div>

            <div>
                <p class="admin-field-label mb-2">حالت درگاه</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="admin-slider-active !items-start">
                        <input type="radio" name="zarinpal_sandbox" value="1" @checked(old('zarinpal_sandbox', $settings['zarinpal_sandbox'] ?? '1') == '1')>
                        <div>
                            <p class="admin-slider-active-title">تست (Sandbox)</p>
                            <p class="admin-slider-active-desc">بدون پرداخت واقعی — مناسب توسعه و آزمایش</p>
                        </div>
                    </label>
                    <label class="admin-slider-active !items-start">
                        <input type="radio" name="zarinpal_sandbox" value="0" @checked(old('zarinpal_sandbox', $settings['zarinpal_sandbox'] ?? '1') == '0')>
                        <div>
                            <p class="admin-slider-active-title">واقعی (Production)</p>
                            <p class="admin-slider-active-desc">پرداخت واقعی با مرچنت‌کد پنل زرین‌پال</p>
                        </div>
                    </label>
                </div>
                @error('zarinpal_sandbox')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>

            <div class="rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 text-xs leading-relaxed text-zinc-600 space-y-2">
                <p class="font-bold text-zinc-700">نکات اتصال (مستندات رسمی)</p>
                <ul class="list-inside list-disc space-y-1">
                    <li>مبلغ به <strong>ریال</strong> ارسال می‌شود (اگر واحد فروشگاه تومان باشد، خودکار ×۱۰ می‌شود).</li>
                    <li>پس از پرداخت، وضعیت <code dir="ltr">OK</code> در callback و سپس متد <code dir="ltr">verify</code> الزامی است.</li>
                    <li>کد <code dir="ltr">100</code> تایید موفق و <code dir="ltr">101</code> به معنای تایید قبلی است.</li>
                    <li>در سندباکس هر سه آدرس request / verify / StartPay روی دامنه sandbox هستند.</li>
                    <li>برای حالت واقعی، دامنهٔ بالا را در پنل زرین‌پال ثبت کنید؛ بعداً با تغییر همین فیلد در ادمین، callback هم عوض می‌شود.</li>
                </ul>
            </div>

            {{-- ───────────── کارت‌های کارت‌به‌کارت ───────────── --}}
            @php
                $c2cCards = old('c2c_cards', $settings['c2c_cards'] ?? '[]');
                if (is_string($c2cCards)) {
                    $c2cCards = json_decode($c2cCards, true) ?: [];
                }
                $c2cCards = array_values(array_filter($c2cCards, fn ($c) => is_array($c) && trim((string) ($c['number'] ?? '')) !== ''));
                if ($c2cCards === []) {
                    $c2cCards = [['number' => '', 'owner' => '', 'bank' => '']];
                }
            @endphp

            <div class="admin-slider-panel-head !mt-6">
                <div class="admin-slider-panel-icon">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M3.75 5.25h16.5A1.5 1.5 0 0121.75 6.75v10.5a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5V6.75a1.5 1.5 0 011.5-1.5z"/></svg>
                </div>
                <div>
                    <p class="admin-slider-panel-title">کارت‌های کارت‌به‌کارت</p>
                    <p class="admin-slider-panel-desc">این کارت‌ها در صفحه پرداخت کارت‌به‌کارت سفارش و شارژ کیف پول نمایش داده می‌شوند</p>
                </div>
            </div>

            <div x-data="{ cards: @js($c2cCards) }" class="space-y-3">
                <template x-for="(card, index) in cards" :key="index">
                    <div class="grid items-end gap-2 rounded-xl border border-zinc-200 bg-zinc-50/60 p-3 sm:grid-cols-[1fr_1fr_140px_auto]">
                        <div>
                            <label class="admin-field-label">شماره کارت <span class="text-rose-500">*</span></label>
                            <input type="text"
                                   :name="`c2c_cards[${index}][number]`"
                                   x-model="card.number"
                                   class="admin-input w-full text-left"
                                   dir="ltr"
                                   maxlength="19"
                                   inputmode="numeric"
                                   placeholder="6104337360260150">
                        </div>
                        <div>
                            <label class="admin-field-label">نام صاحب کارت <span class="text-rose-500">*</span></label>
                            <input type="text"
                                   :name="`c2c_cards[${index}][owner]`"
                                   x-model="card.owner"
                                   class="admin-input w-full"
                                   placeholder="مثلاً: محمدرضا برجی">
                        </div>
                        <div>
                            <label class="admin-field-label">بانک</label>
                            <input type="text"
                                   :name="`c2c_cards[${index}][bank]`"
                                   x-model="card.bank"
                                   class="admin-input w-full"
                                   placeholder="ملت">
                        </div>
                        <button type="button"
                                @click="cards.splice(index, 1)"
                                x-show="cards.length > 1"
                                x-cloak
                                class="admin-btn-secondary !px-3 text-xs !text-rose-600 hover:!border-rose-300">
                            حذف
                        </button>
                    </div>
                </template>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="button"
                            @click="cards.push({ number: '', owner: '', bank: '' })"
                            class="admin-btn-secondary text-xs">
                        + افزودن کارت جدید
                    </button>
                    <span class="text-xs text-zinc-400">حداکثر ۱۰ کارت</span>
                </div>

                @error('c2c_cards.*.number')
                    <p class="admin-field-error">شماره کارت‌ها باید فقط عدد باشد (۱۶ رقم).</p>
                @enderror
                @error('c2c_cards.*.owner')
                    <p class="admin-field-error">نام صاحب کارت برای همه کارت‌ها الزامی است.</p>
                @enderror
            </div>
        </div>

        {{-- پیامک --}}
        @php
            use App\Services\SmsService;
            $smsTemplates = SmsService::templateCatalog();
            $smsDriver = old('sms_driver', $settings['sms_driver'] ?? 'log');
            $smsMode = old('sms_mode', $settings['sms_mode'] ?? 'simple');
            $smsMeliAuth = old('sms_meli_auth', $settings['sms_meli_auth'] ?? 'credentials');
        @endphp
        <div
            x-show="tab === 'sms'"
            x-cloak
            class="admin-slider-panel space-y-5"
            x-data="{ smsDriver: @js($smsDriver), smsMode: @js($smsMode), meliAuth: @js($smsMeliAuth) }"
        >
            <div class="admin-slider-panel-head">
                <div class="admin-slider-panel-icon"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg></div>
                <div>
                    <p class="admin-slider-panel-title">پیامک</p>
                    <p class="admin-slider-panel-desc">کاوه‌نگار یا ملی‌پیامک — اتصال ساده و قابل فهم</p>
                </div>
            </div>

            @if(!empty($settings['sms_configured']))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-800">
                    پیامک فعال است
                    (<span x-text="smsDriver === 'melipayamak' ? 'ملی‌پیامک' : (smsDriver === 'kavenegar' ? 'کاوه‌نگار' : '')"></span>)
                    — حالت
                    <strong x-text="smsMode === 'lookup' ? 'پترن / الگو' : 'ارسال ساده'"></strong>
                </div>
            @else
                <div class="rounded-xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-sm text-amber-900">
                    حالت تست فعال است — پیامک واقعی ارسال نمی‌شود. کد OTP مستقیماً در صفحه <a href="{{ route('login') }}" class="font-bold underline" target="_blank" rel="noopener">ورود مشتری</a> / ثبت‌نام نمایش داده می‌شود.
                </div>
            @endif

            <div>
                <p class="admin-field-label mb-2">۱) ارائه‌دهنده</p>
                <div class="grid gap-3 sm:grid-cols-3">
                    <label class="admin-slider-active !items-start">
                        <input type="radio" name="sms_driver" value="log" x-model="smsDriver">
                        <div>
                            <p class="admin-slider-active-title">غیرفعال / تست</p>
                            <p class="admin-slider-active-desc">فقط لاگ — بدون ارسال واقعی</p>
                        </div>
                    </label>
                    <label class="admin-slider-active !items-start">
                        <input type="radio" name="sms_driver" value="kavenegar" x-model="smsDriver">
                        <div>
                            <p class="admin-slider-active-title">کاوه‌نگار</p>
                            <p class="admin-slider-active-desc">API Key</p>
                        </div>
                    </label>
                    <label class="admin-slider-active !items-start">
                        <input type="radio" name="sms_driver" value="melipayamak" x-model="smsDriver">
                        <div>
                            <p class="admin-slider-active-title">ملی‌پیامک</p>
                            <p class="admin-slider-active-desc">رمز پنل یا API Key</p>
                        </div>
                    </label>
                </div>
                @error('sms_driver')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>

            {{-- کاوه‌نگار --}}
            <div x-show="smsDriver === 'kavenegar'" x-cloak class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="sms_kavenegar_api_key" class="admin-field-label">۲) API Key کاوه‌نگار</label>
                    <input type="password" name="sms_kavenegar_api_key" id="sms_kavenegar_api_key" value="" class="admin-input w-full font-mono text-sm text-left" dir="ltr" autocomplete="new-password" placeholder="{{ !empty($settings['sms_kavenegar_api_key_set']) ? 'برای تغییر، کلید جدید را وارد کنید' : 'کلید API' }}">
                    @if(!empty($settings['sms_kavenegar_api_key_set']))
                        <p class="admin-field-hint">ذخیره‌شده: <bdi dir="ltr" class="font-mono">{{ $settings['sms_kavenegar_api_key_masked'] }}</bdi></p>
                        <label class="mt-2 inline-flex items-center gap-2 text-xs text-zinc-600">
                            <input type="checkbox" name="sms_clear_api_key" value="1" class="rounded border-zinc-300"> پاک کردن کلید
                        </label>
                    @endif
                    @error('sms_kavenegar_api_key')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="sms_kavenegar_sender" class="admin-field-label">۳) شماره خط فرستنده</label>
                    <input type="text" name="sms_kavenegar_sender" id="sms_kavenegar_sender" value="{{ old('sms_kavenegar_sender', $settings['sms_kavenegar_sender'] ?? '') }}" class="admin-input w-full font-mono text-sm text-left" dir="ltr" placeholder="10004346">
                    <p class="admin-field-hint">برای ارسال ساده الزامی است</p>
                    @error('sms_kavenegar_sender')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- ملی‌پیامک --}}
            <div x-show="smsDriver === 'melipayamak'" x-cloak class="space-y-4">
                <div>
                    <p class="admin-field-label mb-2">۲) احراز هویت</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="admin-slider-active !items-start">
                            <input type="radio" name="sms_meli_auth" value="api_key" x-model="meliAuth">
                            <div>
                                <p class="admin-slider-active-title">کلید API کنسول (پیشنهادی)</p>
                                <p class="admin-slider-active-desc">بدون نام کاربری/رمز — از <bdi dir="ltr">console.melipayamak.com</bdi></p>
                            </div>
                        </label>
                        <label class="admin-slider-active !items-start">
                            <input type="radio" name="sms_meli_auth" value="credentials" x-model="meliAuth">
                            <div>
                                <p class="admin-slider-active-title">نام کاربری + رمز / API Key پنل</p>
                                <p class="admin-slider-active-desc">وب‌سرویس کلاسیک REST</p>
                            </div>
                        </label>
                    </div>
                    @error('sms_meli_auth')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

                <div x-show="meliAuth === 'api_key'" x-cloak>
                    <label for="sms_meli_api_key" class="admin-field-label">۳) کلید API کنسول</label>
                    <input type="password" name="sms_meli_api_key" id="sms_meli_api_key" value="" class="admin-input w-full font-mono text-sm text-left" dir="ltr" autocomplete="new-password" placeholder="{{ !empty($settings['sms_meli_api_key_set']) ? 'برای تغییر، کلید جدید وارد کنید' : 'API Key از تنظیمات کنسول' }}">
                    @if(!empty($settings['sms_meli_api_key_set']))
                        <p class="admin-field-hint">ذخیره‌شده: <bdi dir="ltr" class="font-mono">{{ $settings['sms_meli_api_key_masked'] }}</bdi></p>
                        <label class="mt-2 inline-flex items-center gap-2 text-xs text-zinc-600">
                            <input type="checkbox" name="sms_clear_meli_api_key" value="1" class="rounded border-zinc-300"> پاک کردن کلید
                        </label>
                    @endif
                    <p class="admin-field-hint">مسیر: کنسول ملی‌پیامک <span aria-hidden="true">←</span> تنظیمات <span aria-hidden="true">←</span> کلید API</p>
                    @error('sms_meli_api_key')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

                <div x-show="meliAuth === 'credentials'" x-cloak class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="sms_meli_username" class="admin-field-label">۳) نام کاربری پنل</label>
                        <input type="text" name="sms_meli_username" id="sms_meli_username" value="{{ old('sms_meli_username', $settings['sms_meli_username'] ?? '') }}" class="admin-input w-full font-mono text-sm text-left" dir="ltr" autocomplete="off">
                        @error('sms_meli_username')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="sms_meli_password" class="admin-field-label">۴) رمز عبور یا API Key پنل</label>
                        <input type="password" name="sms_meli_password" id="sms_meli_password" value="" class="admin-input w-full font-mono text-sm text-left" dir="ltr" autocomplete="new-password" placeholder="{{ !empty($settings['sms_meli_password_set']) ? 'برای تغییر، مقدار جدید وارد کنید' : 'رمز یا API Key' }}">
                        @if(!empty($settings['sms_meli_password_set']))
                            <p class="admin-field-hint">ذخیره‌شده: <bdi dir="ltr" class="font-mono">{{ $settings['sms_meli_password_masked'] }}</bdi></p>
                            <label class="mt-2 inline-flex items-center gap-2 text-xs text-zinc-600">
                                <input type="checkbox" name="sms_clear_meli_password" value="1" class="rounded border-zinc-300"> پاک کردن
                            </label>
                        @endif
                        <p class="admin-field-hint">طبق مستندات ملی‌پیامک می‌توانید API Key پنل را به‌جای رمز بگذارید.</p>
                        @error('sms_meli_password')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="sms_meli_from" class="admin-field-label">
                        <span x-text="meliAuth === 'api_key' ? '۴) شماره خط فرستنده' : '۵) شماره خط فرستنده'"></span>
                    </label>
                    <input type="text" name="sms_meli_from" id="sms_meli_from" value="{{ old('sms_meli_from', $settings['sms_meli_from'] ?? '') }}" class="admin-input w-full font-mono text-sm text-left sm:max-w-xs" dir="ltr" placeholder="5000...">
                    <p class="admin-field-hint">برای ارسال ساده الزامی است؛ در پترن/الگو لازم نیست</p>
                    @error('sms_meli_from')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

                <p class="text-xs text-zinc-500" x-show="meliAuth === 'api_key'" x-cloak>
                    کنسول RESTFul: <code dir="ltr">console.melipayamak.com/api/send</code>
                </p>
                <p class="text-xs text-zinc-500" x-show="meliAuth === 'credentials'" x-cloak>
                    وب‌سرویس رسمی REST: <code dir="ltr">rest.payamak-panel.com/api/SendSMS</code>
                </p>
            </div>

            <div x-show="smsDriver !== 'log'" x-cloak>
                <p class="admin-field-label mb-2">روش ارسال</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="admin-slider-active !items-start">
                        <input type="radio" name="sms_mode" value="simple" x-model="smsMode">
                        <div>
                            <p class="admin-slider-active-title">ارسال ساده</p>
                            <p class="admin-slider-active-desc">
                                <span x-show="smsDriver === 'kavenegar'">متد <code dir="ltr">sms/send</code></span>
                                <span x-show="smsDriver === 'melipayamak' && meliAuth === 'credentials'">متد <code dir="ltr">SendSMS</code></span>
                                <span x-show="smsDriver === 'melipayamak' && meliAuth === 'api_key'">متد <code dir="ltr">send/simple</code></span>
                                — متن آزاد
                            </p>
                        </div>
                    </label>
                    <label class="admin-slider-active !items-start">
                        <input type="radio" name="sms_mode" value="lookup" x-model="smsMode">
                        <div>
                            <p class="admin-slider-active-title">پترن / الگو (پیشنهادی)</p>
                            <p class="admin-slider-active-desc">
                                <span x-show="smsDriver === 'kavenegar'">متد <code dir="ltr">verify/lookup</code></span>
                                <span x-show="smsDriver === 'melipayamak' && meliAuth === 'credentials'">متد <code dir="ltr">BaseServiceNumber</code> — خط خدماتی</span>
                                <span x-show="smsDriver === 'melipayamak' && meliAuth === 'api_key'">متد <code dir="ltr">send/shared</code> — خط خدماتی</span>
                            </p>
                        </div>
                    </label>
                </div>
                @error('sms_mode')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>

            {{-- الگوهای کاوه‌نگار --}}
            <div x-show="smsDriver === 'kavenegar' && smsMode === 'lookup'" x-cloak class="space-y-4 rounded-xl border border-sky-200 bg-sky-50/50 p-4" dir="rtl">
                <div>
                    <p class="text-sm font-bold text-sky-900">الگوها در پنل کاوه‌نگار</p>
                    <p class="mt-1 text-xs leading-relaxed text-sky-800">
                        مسیر معمول: اعتبارسنجی <span aria-hidden="true">←</span> تعریف الگو.
                        نام فقط انگلیسی بدون فاصله و <bdi dir="ltr">_</bdi>.
                        بعد از تأیید، همان نام را اینجا بگذارید.
                        <strong class="font-bold">مهم:</strong> متن الگو نباید با متغیر تمام شود؛ در غیر این صورت تأیید نمی‌شود.
                    </p>
                </div>
                @foreach($smsTemplates as $key => $meta)
                    @php $field = 'sms_template_'.$key; @endphp
                    <div class="rounded-xl border border-white bg-white/90 p-4 space-y-3">
                        <div>
                            <p class="text-sm font-bold text-zinc-800">{{ $meta['label'] }}</p>
                            <p class="text-xs text-zinc-500">{{ $meta['description'] }}</p>
                        </div>
                        <div>
                            <label for="{{ $field }}" class="admin-field-label">نام الگو</label>
                            <input type="text" name="{{ $field }}" id="{{ $field }}" value="{{ old($field, $settings[$field] ?? '') }}" class="admin-input w-full font-mono text-sm text-left" dir="ltr">
                            @error($field)<p class="admin-field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <p class="admin-field-label">متن نمونه برای ثبت در کاوه‌نگار</p>
                            <pre class="sms-template-sample mt-1 overflow-x-auto whitespace-pre-wrap rounded-lg bg-zinc-900 px-3 py-2 text-start text-xs leading-relaxed text-emerald-300" dir="rtl">{!! rtl_bidi_tokens($meta['kavenegar_sample']) !!}</pre>
                            <ul class="mt-2 list-outside list-disc space-y-1 pe-4 text-[11px] text-zinc-500">
                                @foreach($meta['kavenegar_tokens'] as $tokenHint)
                                    <li>{!! rtl_bidi_tokens($tokenHint) !!}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- پترن‌های ملی‌پیامک --}}
            <div x-show="smsDriver === 'melipayamak' && smsMode === 'lookup'" x-cloak class="space-y-4 rounded-xl border border-violet-200 bg-violet-50/50 p-4" dir="rtl">
                <div>
                    <p class="text-sm font-bold text-violet-900">پترن خدماتی در پنل ملی‌پیامک</p>
                    <p class="mt-1 text-xs leading-relaxed text-violet-800">
                        مسیر معمول: پنل <span aria-hidden="true">←</span> ارسال بر اساس پترن / خط خدماتی اشتراکی.
                        متن زیر را ثبت کنید؛ بعد از تأیید، <strong>کد متن</strong> عددی (<bdi dir="ltr">bodyId</bdi>) را اینجا وارد کنید.
                        متغیرها با <bdi dir="ltr">{0}</bdi>، <bdi dir="ltr">{1}</bdi> و … مشخص می‌شوند و در ارسال با <bdi dir="ltr">;</bdi> جدا می‌گردند.
                        <strong class="font-bold">مهم:</strong> متن پترن نباید با متغیر تمام شود (مثلاً با <bdi dir="ltr">{1}</bdi>)؛ وگرنه تأیید نمی‌شود.
                    </p>
                </div>
                @foreach($smsTemplates as $key => $meta)
                    @php $field = 'sms_meli_body_'.$key; @endphp
                    <div class="rounded-xl border border-white bg-white/90 p-4 space-y-3">
                        <div>
                            <p class="text-sm font-bold text-zinc-800">{{ $meta['label'] }}</p>
                            <p class="text-xs text-zinc-500">{{ $meta['description'] }}</p>
                        </div>
                        <div>
                            <label for="{{ $field }}" class="admin-field-label">کد متن (<bdi dir="ltr">bodyId</bdi>)</label>
                            <input type="text" name="{{ $field }}" id="{{ $field }}" value="{{ old($field, $settings[$field] ?? '') }}" class="admin-input w-full font-mono text-sm text-left" dir="ltr" inputmode="numeric" placeholder="مثلاً 12345">
                            @error($field)<p class="admin-field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <p class="admin-field-label">متن نمونه برای ثبت در ملی‌پیامک</p>
                            <pre class="sms-template-sample mt-1 overflow-x-auto whitespace-pre-wrap rounded-lg bg-zinc-900 px-3 py-2 text-start text-xs leading-relaxed text-emerald-300" dir="rtl">{!! rtl_bidi_tokens($meta['meli_sample']) !!}</pre>
                            <ul class="mt-2 list-outside list-disc space-y-1 pe-4 text-[11px] text-zinc-500">
                                @foreach($meta['meli_tokens'] as $tokenHint)
                                    <li>{!! rtl_bidi_tokens($tokenHint) !!}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 text-xs leading-relaxed text-zinc-600 space-y-2">
                <p class="font-bold text-zinc-700">راهنمای خیلی کوتاه</p>
                <ol class="list-inside list-decimal space-y-1">
                    <li>یکی از سرویس‌ها را انتخاب کنید.</li>
                    <li>اطلاعات ورود را ذخیره کنید.</li>
                    <li>برای شروع سریع: «ارسال ساده» + شماره خط.</li>
                    <li>برای پیامک خدماتی بدون فیلتر: «پترن / الگو» و متن‌های نمونه بالا را در پنل ثبت کنید.</li>
                </ol>
                <p>
                    مستندات:
                    <a href="https://kavenegar.com/rest.html" target="_blank" rel="noopener" class="text-shop-primary underline" dir="ltr">کاوه‌نگار</a>
                    ·
                    <a href="https://www.melipayamak.com/api/" target="_blank" rel="noopener" class="text-shop-primary underline" dir="ltr">ملی‌پیامک</a>
                </p>
            </div>
        </div>

        {{-- ایمیل --}}
        @php
            $mailMailer = old('mail_mailer', $settings['mail_mailer'] ?? 'log');
            $mailEncryption = old('mail_encryption', $settings['mail_encryption'] ?? 'tls');
        @endphp
        <div
            x-show="tab === 'mail'"
            x-cloak
            class="admin-slider-panel space-y-5"
            x-data="{ mailMailer: @js($mailMailer) }"
        >
            <div class="admin-slider-panel-head">
                <div class="admin-slider-panel-icon"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg></div>
                <div>
                    <p class="admin-slider-panel-title">ایمیل</p>
                    <p class="admin-slider-panel-desc">ارسال از طریق SMTP — تایید سفارش، وضعیت و پاسخ پیام‌ها</p>
                </div>
            </div>

            @if(!empty($settings['mail_configured']))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-800">
                    ارسال ایمیل از طریق SMTP فعال است
                    @if(!empty($settings['mail_host']))
                        — <span dir="ltr" class="font-mono text-xs">{{ $settings['mail_host'] }}:{{ $settings['mail_port'] }}</span>
                    @endif
                </div>
            @elseif(($settings['mail_mailer'] ?? 'log') === 'log')
                <div class="rounded-xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-sm text-amber-900">
                    حالت فعلی: فقط ثبت در لاگ — ایمیل واقعی ارسال نمی‌شود. برای ارسال واقعی، SMTP را انتخاب کنید.
                </div>
            @else
                <div class="rounded-xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-sm text-amber-900">
                    SMTP انتخاب شده ولی هاست یا آدرس فرستنده کامل نیست.
                </div>
            @endif

            <div>
                <p class="admin-field-label mb-2">۱) نحوه ارسال</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="admin-slider-active !items-start">
                        <input type="radio" name="mail_mailer" value="log" x-model="mailMailer">
                        <div>
                            <p class="admin-slider-active-title">فقط لاگ (توسعه)</p>
                            <p class="admin-slider-active-desc">بدون ارسال واقعی — مناسب تست محلی</p>
                        </div>
                    </label>
                    <label class="admin-slider-active !items-start">
                        <input type="radio" name="mail_mailer" value="smtp" x-model="mailMailer">
                        <div>
                            <p class="admin-slider-active-title">SMTP</p>
                            <p class="admin-slider-active-desc">ارسال واقعی از سرور ایمیل (Gmail، سرویس‌دهنده و …)</p>
                        </div>
                    </label>
                </div>
                @error('mail_mailer')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>

            <div x-show="mailMailer === 'smtp'" x-cloak class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="mail_host" class="admin-field-label">۲) هاست SMTP</label>
                        <input type="text" name="mail_host" id="mail_host" value="{{ old('mail_host', $settings['mail_host'] ?? '') }}" class="admin-input w-full font-mono text-sm text-left" dir="ltr" autocomplete="off" placeholder="smtp.example.com">
                        @error('mail_host')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="mail_port" class="admin-field-label">۳) پورت</label>
                        <input type="number" name="mail_port" id="mail_port" value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}" class="admin-input w-full font-mono text-sm text-left" dir="ltr" min="1" max="65535" placeholder="587">
                        <p class="admin-field-hint">معمولاً ۵۸۷ (TLS) یا ۴۶۵ (SSL)</p>
                        @error('mail_port')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="mail_username" class="admin-field-label">۴) نام کاربری</label>
                        <input type="text" name="mail_username" id="mail_username" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}" class="admin-input w-full font-mono text-sm text-left" dir="ltr" autocomplete="off">
                        @error('mail_username')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="mail_password" class="admin-field-label">۵) رمز عبور</label>
                        <input type="password" name="mail_password" id="mail_password" value="" class="admin-input w-full font-mono text-sm text-left" dir="ltr" autocomplete="new-password" placeholder="{{ !empty($settings['mail_password_set']) ? 'برای تغییر، رمز جدید وارد کنید' : 'رمز یا App Password' }}">
                        @if(!empty($settings['mail_password_set']))
                            <p class="admin-field-hint" dir="ltr">ذخیره‌شده: {{ $settings['mail_password_masked'] }}</p>
                            <label class="mt-2 flex items-center gap-2 text-xs text-zinc-600">
                                <input type="checkbox" name="mail_clear_password" value="1" class="rounded border-zinc-300"> پاک کردن رمز
                            </label>
                        @endif
                        @error('mail_password')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <p class="admin-field-label mb-2">۶) رمزنگاری اتصال</p>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <label class="admin-slider-active !items-start">
                            <input type="radio" name="mail_encryption" value="tls" @checked($mailEncryption === 'tls')>
                            <div>
                                <p class="admin-slider-active-title">TLS</p>
                                <p class="admin-slider-active-desc">پورت ۵۸۷ — رایج‌ترین</p>
                            </div>
                        </label>
                        <label class="admin-slider-active !items-start">
                            <input type="radio" name="mail_encryption" value="ssl" @checked($mailEncryption === 'ssl')>
                            <div>
                                <p class="admin-slider-active-title">SSL</p>
                                <p class="admin-slider-active-desc">پورت ۴۶۵</p>
                            </div>
                        </label>
                        <label class="admin-slider-active !items-start">
                            <input type="radio" name="mail_encryption" value="none" @checked($mailEncryption === 'none')>
                            <div>
                                <p class="admin-slider-active-title">بدون رمزنگاری</p>
                                <p class="admin-slider-active-desc">فقط سرور داخلی</p>
                            </div>
                        </label>
                    </div>
                    @error('mail_encryption')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="mail_from_address" class="admin-field-label">آدرس فرستنده (From)</label>
                    <input type="email" name="mail_from_address" id="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}" class="admin-input w-full text-left" dir="ltr" placeholder="noreply@example.com">
                    <p class="admin-field-hint">باید در سرویس SMTP شما مجاز باشد.</p>
                    @error('mail_from_address')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="mail_from_name" class="admin-field-label">نام فرستنده</label>
                    <input type="text" name="mail_from_name" id="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? '') }}" class="admin-input w-full" placeholder="{{ $settings['store_name'] ?? 'فروشگاه' }}">
                    @error('mail_from_name')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 text-xs leading-relaxed text-zinc-600 space-y-2">
                <p class="font-bold text-zinc-700">نکات</p>
                <ul class="list-inside list-disc space-y-1">
                    <li>اگر فیلدها خالی بمانند، مقادیر <code dir="ltr">.env</code> (در صورت وجود) استفاده می‌شوند.</li>
                    <li>برای Gmail معمولاً App Password لازم است، نه رمز اصلی حساب.</li>
                    <li>رمز عبور فقط هنگام وارد کردن مقدار جدید ذخیره می‌شود؛ خالی گذاشتن، رمز قبلی را پاک نمی‌کند.</li>
                </ul>
            </div>
        </div>

        {{-- ورود / OTP --}}
        @php
            $otpChannel = old('auth_otp_channel', $settings['auth_otp_channel'] ?? 'mobile');
            $otpEnabled = old('auth_otp_enabled', $settings['auth_otp_enabled'] ?? '1') == '1';
        @endphp
        <div
            x-show="tab === 'auth'"
            x-cloak
            class="admin-slider-panel space-y-5"
            x-data="{ otpChannel: @js($otpChannel) }"
        >
            <div class="admin-slider-panel-head">
                <div class="admin-slider-panel-icon"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg></div>
                <div>
                    <p class="admin-slider-panel-title">ورود و ثبت‌نام</p>
                    <p class="admin-slider-panel-desc">رمز عبور همیشه فعال است — کانال ارسال کد یکبارمصرف (OTP) را اینجا مشخص کنید</p>
                </div>
            </div>

            <input type="hidden" name="auth_otp_enabled" value="0">
            <label class="admin-slider-active !items-start">
                <input type="checkbox" name="auth_otp_enabled" value="1" @checked($otpEnabled)>
                <div>
                    <p class="admin-slider-active-title">فعال‌سازی ورود و ثبت‌نام با OTP</p>
                    <p class="admin-slider-active-desc">در صفحات ورود/ثبت‌نام، گزینه «کد یکبارمصرف» کنار رمز عبور نمایش داده می‌شود</p>
                </div>
            </label>
            @error('auth_otp_enabled')<p class="admin-field-error">{{ $message }}</p>@enderror

            <div>
                <p class="admin-field-label mb-2">کانال ارسال کد OTP</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="admin-slider-active !items-start">
                        <input type="radio" name="auth_otp_channel" value="mobile" x-model="otpChannel">
                        <div>
                            <p class="admin-slider-active-title">موبایل (پیامک)</p>
                            <p class="admin-slider-active-desc">کد به شماره موبایل مشتری ارسال می‌شود</p>
                        </div>
                    </label>
                    <label class="admin-slider-active !items-start">
                        <input type="radio" name="auth_otp_channel" value="email" x-model="otpChannel">
                        <div>
                            <p class="admin-slider-active-title">ایمیل</p>
                            <p class="admin-slider-active-desc">کد به آدرس ایمیل مشتری ارسال می‌شود</p>
                        </div>
                    </label>
                </div>
                @error('auth_otp_channel')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>

            <div x-show="otpChannel === 'mobile'" x-cloak>
                @if(!empty($settings['auth_otp_sms_ready']))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-800">
                        پیامک برای OTP آماده است (از تنظیمات تب پیامک).
                    </div>
                @else
                    <div class="rounded-xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-sm text-amber-900">
                        برای ارسال واقعی OTP با پیامک، ابتدا در تب <strong>پیامک</strong> سرویس را پیکربندی کنید. در حالت تست، کد در همان تب پیامک و صفحه ورود نمایش داده می‌شود.
                    </div>
                @endif
            </div>

            <div x-show="otpChannel === 'email'" x-cloak>
                @if(!empty($settings['auth_otp_mail_ready']))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-800">
                        ایمیل برای OTP آماده است (از تنظیمات تب ایمیل).
                    </div>
                @else
                    <div class="rounded-xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-sm text-amber-900">
                        برای ارسال واقعی OTP با ایمیل، ابتدا در تب <strong>ایمیل</strong> SMTP را پیکربندی کنید. در حالت لاگ، کد روی صفحه ورود/ثبت‌نام نمایش داده می‌شود.
                    </div>
                @endif
            </div>

            <div class="rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 text-xs leading-relaxed text-zinc-600 space-y-2">
                <p class="font-bold text-zinc-700">نکات</p>
                <ul class="list-inside list-disc space-y-1">
                    <li>ورود مدیران (<code dir="ltr">/admin/login</code>) همیشه با رمز عبور است و OTP ندارد.</li>
                    <li>کد ۶ رقمی است، ۵ دقیقه اعتبار دارد، و فقط به‌صورت هش ذخیره می‌شود.</li>
                    <li>در هر لحظه فقط یکی از کانال‌های موبایل یا ایمیل برای OTP فعال است.</li>
                </ul>
            </div>
        </div>

    </div>

    @include('admin.settings._preview')
</div>
