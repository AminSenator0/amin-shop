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

<p class="text-sm text-zinc-500">فعال/غیرفعال کردن بخش‌های صفحه اصلی و تنظیم محتوای تکمیلی</p>
<div class="grid gap-3 sm:grid-cols-2">
    @foreach([
        'homepage_coupon_bar_enabled' => 'بنر کد تخفیف بالای صفحه',
        'homepage_free_shipping_bar_enabled' => 'بنر ارسال رایگان',
        'homepage_quick_track_enabled' => 'پیگیری سریع سفارش',
        'homepage_flash_sale_enabled' => 'پیشنهاد زمان‌دار (شمارش معکوس)',
        'homepage_brands_enabled' => 'نمایش برندها',
        'homepage_bestsellers_enabled' => 'پرفروش‌ترین‌ها',
        'homepage_discounted_enabled' => 'محصولات تخفیف‌دار',
        'homepage_reviews_enabled' => 'نظرات مشتریان',
        'homepage_faq_enabled' => 'سوالات متداول',
        'homepage_blog_enabled' => 'بخش بلاگ',
        'homepage_recently_viewed_enabled' => 'اخیراً بازدیدشده',
        'homepage_recommendations_enabled' => 'پیشنهاد برای شما',
        'homepage_why_us_enabled' => 'آمار واقعی (چرا ما؟)',
        'homepage_contact_section_enabled' => 'بخش تماس سریع',
        'homepage_map_enabled' => 'نقشه فروشگاه',
        'homepage_video_enabled' => 'ویدیوی معرفی',
        'homepage_app_banner_enabled' => 'بنر دانلود اپ',
        'homepage_payment_trust_enabled' => 'درگاه پرداخت و نماد اعتماد',
        'homepage_how_it_works_enabled' => 'خرید در ۳ قدم',
        'homepage_collections_enabled' => 'کالکشن‌های موضوعی',
        'homepage_about_snippet_enabled' => 'داستان برند (درباره ما)',
        'homepage_size_guide_enabled' => 'راهنمای سایز',
        'homepage_sticky_nav_enabled' => 'ناوبری سریع چسبان',
    ] as $key => $label)
        <label class="admin-slider-active">
            <input type="checkbox" name="{{ $key }}" value="1" @checked(old($key, $settings[$key] ?? '1') == '1')>
            <div><p class="admin-slider-active-title">{{ $label }}</p></div>
        </label>
    @endforeach
</div>
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="homepage_featured_coupon_id" class="admin-field-label">کد تخفیف نمایشی</label>
        <select name="homepage_featured_coupon_id" id="homepage_featured_coupon_id" class="admin-select w-full">
            <option value="0">خودکار (آخرین کد فعال)</option>
            @foreach($coupons ?? [] as $coupon)
                <option value="{{ $coupon->id }}" @selected(old('homepage_featured_coupon_id', $settings['homepage_featured_coupon_id'] ?? '') == $coupon->id)>{{ $coupon->code }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="homepage_flash_sale_ends_at" class="admin-field-label">پایان پیشنهاد زمان‌دار</label>
        <input type="text" name="homepage_flash_sale_ends_at" id="homepage_flash_sale_ends_at" data-jalali-date value="{{ old('homepage_flash_sale_ends_at', !empty($settings['homepage_flash_sale_ends_at']) ? format_jalali($settings['homepage_flash_sale_ends_at']) : '') }}" class="admin-input w-full" placeholder="۱۴۰۳/۱۲/۲۹">
    </div>
</div>
<div>
    <label for="homepage_video_url" class="admin-field-label">لینک embed ویدیو (یوتیوب/آپارات)</label>
    <input type="url" name="homepage_video_url" id="homepage_video_url" value="{{ old('homepage_video_url', $settings['homepage_video_url'] ?? '') }}" class="admin-input w-full text-left" dir="ltr">
</div>
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="homepage_app_download_url" class="admin-field-label">لینک دانلود اپ</label>
        <input type="url" name="homepage_app_download_url" id="homepage_app_download_url" value="{{ old('homepage_app_download_url', $settings['homepage_app_download_url'] ?? '') }}" class="admin-input w-full text-left" dir="ltr">
    </div>
    <div>
        <label for="homepage_app_download_text" class="admin-field-label">متن بنر اپ</label>
        <input type="text" name="homepage_app_download_text" id="homepage_app_download_text" value="{{ old('homepage_app_download_text', $settings['homepage_app_download_text'] ?? '') }}" class="admin-input w-full">
    </div>
</div>
<div>
    <label for="payment_gateways" class="admin-field-label">درگاه پرداخت</label>
    <input type="text" name="payment_gateways" id="payment_gateways" value="{{ old('payment_gateways', $settings['payment_gateways'] ?? 'زرین‌پال') }}" class="admin-input w-full" placeholder="زرین‌پال">
    <p class="admin-field-hint">نام درگاه نمایش‌داده‌شده در فروشگاه (فعلاً فقط زرین‌پال)</p>
</div>
<div>
    <label for="enamad_url" class="admin-field-label">لینک نماد اعتماد</label>
    <input type="url" name="enamad_url" id="enamad_url" value="{{ old('enamad_url', $settings['enamad_url'] ?? '') }}" class="admin-input w-full text-left" dir="ltr">
</div>
@php $existingEnamad = !empty($settings['enamad_image']) ? asset('storage/'.$settings['enamad_image']) : null; @endphp
<x-admin.single-image-upload name="enamad_image" remove-name="remove_enamad_image" :existing-url="$existingEnamad" label="تصویر نماد اعتماد" button="انتخاب تصویر" />
<div>
    <label for="newsletter_incentive_text" class="admin-field-label">متن تشویقی خبرنامه</label>
    <input type="text" name="newsletter_incentive_text" id="newsletter_incentive_text" value="{{ old('newsletter_incentive_text', $settings['newsletter_incentive_text'] ?? '') }}" class="admin-input w-full" placeholder="۱۰٪ تخفیف اولین خرید با عضویت در خبرنامه">
</div>
<div>
    <label for="social_instagram_embed" class="admin-field-label">کد embed اینستاگرام</label>
    <textarea name="social_instagram_embed" id="social_instagram_embed" rows="3" class="admin-input w-full font-mono text-xs" dir="ltr">{{ old('social_instagram_embed', $settings['social_instagram_embed'] ?? '') }}</textarea>
</div>
