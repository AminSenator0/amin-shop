<?php

namespace App\Http\Requests\Admin;

use App\Rules\ValidJalaliDate;
use App\Support\UploadRules;
use Illuminate\Foundation\Http\FormRequest;

class HomepageSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'homepage_featured_coupon_id' => ['nullable', 'integer', 'exists:coupons,id'],
            'homepage_flash_sale_ends_at' => ['nullable', 'string', 'max:20', new ValidJalaliDate],
            'homepage_coupon_bar_enabled' => ['sometimes', 'boolean'],
            'homepage_free_shipping_bar_enabled' => ['sometimes', 'boolean'],
            'homepage_flash_sale_enabled' => ['sometimes', 'boolean'],
            'homepage_brands_enabled' => ['sometimes', 'boolean'],
            'homepage_bestsellers_enabled' => ['sometimes', 'boolean'],
            'homepage_discounted_enabled' => ['sometimes', 'boolean'],
            'homepage_reviews_enabled' => ['sometimes', 'boolean'],
            'homepage_faq_enabled' => ['sometimes', 'boolean'],
            'homepage_blog_enabled' => ['sometimes', 'boolean'],
            'homepage_recently_viewed_enabled' => ['sometimes', 'boolean'],
            'homepage_recommendations_enabled' => ['sometimes', 'boolean'],
            'homepage_quick_track_enabled' => ['sometimes', 'boolean'],
            'homepage_contact_section_enabled' => ['sometimes', 'boolean'],
            'homepage_map_enabled' => ['sometimes', 'boolean'],
            'homepage_video_enabled' => ['sometimes', 'boolean'],
            'homepage_video_url' => ['nullable', 'url', 'max:500'],
            'homepage_app_banner_enabled' => ['sometimes', 'boolean'],
            'homepage_app_download_url' => ['nullable', 'url', 'max:500'],
            'homepage_app_download_text' => ['nullable', 'string', 'max:255'],
            'homepage_payment_trust_enabled' => ['sometimes', 'boolean'],
            'homepage_how_it_works_enabled' => ['sometimes', 'boolean'],
            'homepage_collections_enabled' => ['sometimes', 'boolean'],
            'homepage_about_snippet_enabled' => ['sometimes', 'boolean'],
            'homepage_size_guide_enabled' => ['sometimes', 'boolean'],
            'homepage_sticky_nav_enabled' => ['sometimes', 'boolean'],
            'homepage_why_us_enabled' => ['sometimes', 'boolean'],
            'newsletter_incentive_text' => ['nullable', 'string', 'max:255'],
            'payment_gateways' => ['nullable', 'string', 'max:500'],
            'enamad_url' => ['nullable', 'url', 'max:500'],
            'enamad_image' => UploadRules::file('enamad_image'),
            'remove_enamad_image' => ['sometimes', 'boolean'],
            'social_instagram_embed' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $couponId = $this->input('homepage_featured_coupon_id');
        
        // اگه خالی یا 0 باشه → null بذار (تا nullable validation رد کنه)
        if ($couponId === null || $couponId === '' || $couponId === '0' || $couponId === 0) {
            $this->merge([
                'homepage_featured_coupon_id' => null,
            ]);
        } else {
            $this->merge([
                'homepage_featured_coupon_id' => (int) normalize_numeric_string($couponId),
            ]);
        }
        if ($this->filled('homepage_flash_sale_ends_at')) {
            $parsed = parse_jalali($this->input('homepage_flash_sale_ends_at'));
            if ($parsed) {
                $this->merge(['homepage_flash_sale_ends_at_parsed' => $parsed->endOfDay()]);
            }
        }
    }
}
