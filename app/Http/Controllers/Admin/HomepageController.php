<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomepageSettingsRequest;
use App\Models\Banner;
use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Coupon;
use App\Models\Faq;
use App\Models\NewsletterSubscriber;
use App\Models\Product;
use App\Models\Review;
use App\Models\Slider;
use App\Models\StoreSetting;
use App\Services\FileUploadService;
use App\Support\StoreSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomepageController extends Controller
{
    public function __construct(private FileUploadService $uploader) {}

    public function index(): View
    {
        $stats = [
            'sliders' => Slider::where('is_active', true)->count(),
            'banners' => Banner::where('is_active', true)->where('position', 'home')->count(),
            'faqs' => Faq::where('is_active', true)->count(),
            'blog_posts' => BlogPost::where('is_published', true)->count(),
            'brands' => Brand::where('is_active', true)->count(),
            'featured_products' => Product::where('is_active', true)->where('is_featured', true)->count(),
            'discounted_products' => Product::where('is_active', true)->whereNotNull('compare_price')->whereColumn('compare_price', '>', 'price')->count(),
            'active_coupons' => Coupon::where('is_active', true)->count(),
            'approved_reviews' => Review::where('is_approved', true)->count(),
            'newsletter_subscribers' => NewsletterSubscriber::count(),
        ];

        return view('admin.homepage.index', [
            'stats' => $stats,
            'settings' => StoreSettings::forAdmin(),
            'coupons' => Coupon::where('is_active', true)->orderBy('code')->get(['id', 'code']),
        ]);
    }

    public function update(HomepageSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated) {
            foreach ([
                'homepage_featured_coupon_id', 'homepage_flash_sale_ends_at',
                'homepage_video_url', 'homepage_app_download_url', 'homepage_app_download_text',
                'payment_gateways', 'enamad_url', 'social_instagram_embed', 'newsletter_incentive_text',
            ] as $key) {
                StoreSetting::set($key, $validated[$key] ?? '', 'homepage');
            }

            foreach ([
                'homepage_coupon_bar_enabled', 'homepage_free_shipping_bar_enabled', 'homepage_flash_sale_enabled',
                'homepage_brands_enabled', 'homepage_bestsellers_enabled', 'homepage_discounted_enabled',
                'homepage_reviews_enabled', 'homepage_faq_enabled', 'homepage_blog_enabled',
                'homepage_recently_viewed_enabled', 'homepage_recommendations_enabled', 'homepage_quick_track_enabled',
                'homepage_contact_section_enabled', 'homepage_map_enabled', 'homepage_video_enabled',
                'homepage_app_banner_enabled', 'homepage_payment_trust_enabled', 'homepage_why_us_enabled',
                'homepage_how_it_works_enabled', 'homepage_collections_enabled', 'homepage_about_snippet_enabled',
                'homepage_size_guide_enabled', 'homepage_sticky_nav_enabled',
            ] as $key) {
                StoreSetting::set($key, $request->boolean($key) ? '1' : '0', 'homepage', 'boolean');
            }

            if ($request->filled('homepage_flash_sale_ends_at_parsed')) {
                StoreSetting::set(
                    'homepage_flash_sale_ends_at',
                    $request->input('homepage_flash_sale_ends_at_parsed')->toDateTimeString(),
                    'homepage'
                );
            } elseif (! $request->filled('homepage_flash_sale_ends_at')) {
                StoreSetting::set('homepage_flash_sale_ends_at', '', 'homepage');
            }

            if ($request->hasFile('enamad_image')) {
                $old = StoreSetting::get('enamad_image');
                StoreSetting::set(
                    'enamad_image',
                    $this->uploader->replace($request->file('enamad_image'), $old ?: null, 'enamad_image'),
                    'homepage',
                    'image'
                );
            } elseif ($request->boolean('remove_enamad_image')) {
                $old = StoreSetting::get('enamad_image');
                if ($old) {
                    $this->uploader->delete($old);
                }
                StoreSetting::set('enamad_image', '', 'homepage', 'image');
            }
        });

        return redirect()
            ->route('admin.homepage.index')
            ->withFragment('settings')
            ->with('success', 'تنظیمات صفحه اصلی ذخیره شد.');
    }
}
