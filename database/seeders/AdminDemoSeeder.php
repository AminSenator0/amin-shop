<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReturnStatus;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Banner;
use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\ContactMessageReply;
use App\Models\Coupon;
use App\Models\Faq;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\ShippingMethod;
use App\Models\SiteVisit;
use App\Models\Slider;
use App\Models\StoreSetting;
use App\Models\User;
use App\Models\Wishlist;
use Database\Seeders\Support\DemoProductCatalog;
use Database\Seeders\Support\SeedImageDownloader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminDemoSeeder extends Seeder
{
    private const MIN = 15;

    private SeedImageDownloader $images;

    public function run(): void
    {
        $this->images = new SeedImageDownloader;

        $this->call(StoreSettingsSeeder::class);

        $admin = User::updateOrCreate(
            ['email' => 'admin@shop.test'],
            [
                'name' => 'مدیر سیستم',
                'phone' => '09120000000',
                'role' => UserRole::Admin,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $customers = $this->seedCustomers();
        $categories = $this->seedCategories();
        $brands = $this->seedBrands();
        $shippingMethods = $this->seedShippingMethods();
        $this->seedAddresses($customers);
        $products = $this->seedProducts($categories, $brands);
        $this->seedProductImages($products);
        $coupons = $this->seedCoupons();
        $this->seedUserCoupons($customers, $coupons);
        $this->seedWishlists($customers, $products);
        $orders = $this->seedOrders($customers, $products, $shippingMethods, $coupons);
        $this->seedReturns($orders);
        $this->seedReviews($customers, $products, $orders);
        $this->seedContactMessages($customers, $admin);
        $this->seedSliders();
        $this->seedBanners();
        $this->seedFaqs();
        $this->seedBlogPosts();
        $this->seedNewsletterSubscribers();
        $this->seedSiteVisits();
        $this->seedStoreImages();
        $this->seedHomepageSettings($coupons);

        $this->command?->info('محتوای نمایشی با حداقل '.self::MIN.' رکورد در هر جدول ایجاد شد.');
        $this->command?->info('ورود ادمین: admin@shop.test — رمز: password');
    }

    /** @return \Illuminate\Support\Collection<int, User> */
    private function seedCustomers()
    {
        $names = [
            'علی محمدی', 'زهرا احمدی', 'رضا کریمی', 'مریم حسینی', 'امیر رضایی',
            'فاطمه نوری', 'حسین جعفری', 'سارا موسوی', 'مهدی قاسمی', 'نرگس صادقی',
            'پویا اکبری', 'لیلا باقری', 'کامران شریفی', 'ندا رحیمی', 'بهرام ملکی',
        ];

        $customers = collect();

        foreach ($names as $i => $name) {
            $customers->push(User::updateOrCreate(
                ['email' => 'customer'.($i + 1).'@shop.test'],
                [
                    'name' => $name,
                    'phone' => '0912'.str_pad((string) ($i + 1000000), 7, '0', STR_PAD_LEFT),
                    'role' => UserRole::Customer,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now()->subDays(rand(1, 90)),
                ]
            ));
        }

        return $customers;
    }

    /** @return \Illuminate\Support\Collection<int, Category> */
    private function seedCategories()
    {
        $names = [
            'لوازم خانگی', 'پوشاک', 'کتاب', 'لوازم الکترونیکی', 'آرایشی بهداشتی',
            'ورزشی', 'اسباب‌بازی', 'لوازم التحریر', 'کالای دیجیتال', 'خانه و آشپزخانه',
            'ابزار و یراق', 'زیورآلات', 'کفش', 'ساعت', 'عطر و ادکلن',
        ];

        $categories = collect();

        foreach ($names as $i => $name) {
            $categories->push(Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => "دسته‌بندی {$name} برای محصولات فروشگاه",
                    'image' => $this->images->categoryForName($name, Str::slug($name)),
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            ));
        }

        return $categories;
    }

    /** @return \Illuminate\Support\Collection<int, Brand> */
    private function seedBrands()
    {
        $brands = [
            ['name' => 'سامسونگ', 'slug' => 'samsung'],
            ['name' => 'اپل', 'slug' => 'apple'],
            ['name' => 'سونی', 'slug' => 'sony'],
            ['name' => 'ال‌جی', 'slug' => 'lg'],
            ['name' => 'شیائومی', 'slug' => 'xiaomi'],
            ['name' => 'ایسوس', 'slug' => 'asus'],
            ['name' => 'لنوو', 'slug' => 'lenovo'],
            ['name' => 'نایکی', 'slug' => 'nike'],
            ['name' => 'آدیداس', 'slug' => 'adidas'],
            ['name' => 'پوما', 'slug' => 'puma'],
            ['name' => 'مایکروسافت', 'slug' => 'microsoft'],
            ['name' => 'گوگل', 'slug' => 'google'],
            ['name' => 'نتفلیکس', 'slug' => 'netflix'],
            ['name' => 'سیسکو', 'slug' => 'cisco'],
            ['name' => 'آی‌بی‌ام', 'slug' => 'ibm'],
        ];

        $result = collect();

        foreach ($brands as $i => $brand) {
            $result->push(Brand::updateOrCreate(
                ['slug' => $brand['slug']],
                [
                    'name' => $brand['name'],
                    'logo' => $this->images->brand($i, $brand['slug']),
                    'is_active' => true,
                ]
            ));
        }

        return $result;
    }

    /** @return \Illuminate\Support\Collection<int, ShippingMethod> */
    private function seedShippingMethods()
    {
        $methods = [
            ['name' => 'پست پیشتاز', 'days' => 5, 'cost' => 50000, 'free_above' => 500000],
            ['name' => 'پیک موتوری', 'days' => 1, 'cost' => 80000, 'free_above' => null],
            ['name' => 'پست سفارشی', 'days' => 3, 'cost' => 65000, 'free_above' => 500000],
            ['name' => 'تیپاکس', 'days' => 4, 'cost' => 70000, 'free_above' => null],
            ['name' => 'ارسال اکسپرس', 'days' => 1, 'cost' => 120000, 'free_above' => null],
            ['name' => 'پست عادی', 'days' => 7, 'cost' => 35000, 'free_above' => 300000],
            ['name' => 'ارسال رایگان ویژه', 'days' => 5, 'cost' => 0, 'free_above' => 0],
            ['name' => 'ارسال فوری', 'days' => 1, 'cost' => 150000, 'free_above' => null],
            ['name' => 'باربری', 'days' => 6, 'cost' => 90000, 'free_above' => null],
            ['name' => 'پست ویژه', 'days' => 2, 'cost' => 95000, 'free_above' => 400000],
            ['name' => 'ارسال شبانه', 'days' => 1, 'cost' => 180000, 'free_above' => null],
            ['name' => 'پست بین‌شهری', 'days' => 8, 'cost' => 45000, 'free_above' => 350000],
            ['name' => 'پیک درون‌شهری', 'days' => 1, 'cost' => 60000, 'free_above' => null],
            ['name' => 'ارسال اقتصادی', 'days' => 10, 'cost' => 25000, 'free_above' => 200000],
            ['name' => 'تحویل در محل', 'days' => 3, 'cost' => 55000, 'free_above' => null],
        ];

        $result = collect();

        foreach ($methods as $i => $method) {
            $result->push(ShippingMethod::updateOrCreate(
                ['name' => $method['name']],
                [
                    'description' => "تحویل {$method['days']} روز کاری",
                    'cost' => $method['cost'],
                    'free_above' => $method['free_above'],
                    'estimated_days' => $method['days'],
                    'is_active' => $i < 13,
                ]
            ));
        }

        return $result;
    }

    /** @param  \Illuminate\Support\Collection<int, User>  $customers */
    private function seedAddresses($customers): void
    {
        $provinces = ['تهران', 'اصفهان', 'شیراز', 'مشهد', 'تبریز', 'کرج', 'اهواز', 'قم'];
        $cities = ['تهران', 'اصفهان', 'شیراز', 'مشهد', 'تبریز', 'کرج', 'اهواز', 'قم'];

        foreach ($customers as $i => $customer) {
            Address::updateOrCreate(
                ['user_id' => $customer->id, 'title' => 'خانه'],
                [
                    'full_name' => $customer->name,
                    'phone' => $customer->phone,
                    'province' => $provinces[$i % count($provinces)],
                    'city' => $cities[$i % count($cities)],
                    'address' => 'خیابان ولیعصر، کوچه '.($i + 1).'، پلاک '.($i + 10),
                    'postal_code' => str_pad((string) (1000000000 + $i), 10, '0', STR_PAD_LEFT),
                    'is_default' => true,
                ]
            );
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Category>  $categories
     * @param  \Illuminate\Support\Collection<int, Brand>  $brands
     * @return \Illuminate\Support\Collection<int, Product>
     */
    private function seedProducts($categories, $brands)
    {
        $products = collect();
        $categoryByName = $categories->keyBy('name');
        $brandBySlug = $brands->keyBy('slug');

        foreach (DemoProductCatalog::products() as $i => $item) {
            $category = $categoryByName->get($item['category']);
            $brand = $brandBySlug->get($item['brand_slug']);
            $price = rand(10, 200) * 10000;
            $stock = $i < 4 ? rand(0, 3) : rand(5, 100);
            $galleryPaths = $this->images->productGalleryForItem($item['sku'], $item['name'], force: true);
            $primaryImage = $galleryPaths[0] ?? null;

            $products->push(Product::updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'category_id' => $category?->id,
                    'brand_id' => $brand?->id,
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'short_description' => $item['short_description'],
                    'description' => $item['description'],
                    'price' => $price,
                    'compare_price' => ($i + 1) % 3 === 0 ? $price + rand(5, 20) * 10000 : null,
                    'stock' => $stock,
                    'weight' => rand(100, 5000),
                    'sizes' => $item['sizes'] ?? (($i + 1) % 3 === 0 ? ['S', 'M', 'L', 'XL'] : null),
                    'colors' => $item['colors'] ?? (($i + 1) % 4 === 0 ? ['مشکی', 'سفید', 'آبی'] : null),
                    'image' => $primaryImage,
                    'is_active' => $item['is_active'] ?? true,
                    'is_featured' => $item['is_featured'] ?? ($i < 8),
                ]
            ));
        }

        return $products;
    }

    /** @param  \Illuminate\Support\Collection<int, Product>  $products */
    private function seedProductImages($products): void
    {
        $catalogBySku = collect(DemoProductCatalog::products())->keyBy('sku');

        foreach ($products as $product) {
            $item = $catalogBySku->get($product->sku);

            if ($item === null) {
                continue;
            }

            $galleryPaths = $this->images->productGalleryForItem($item['sku'], $item['name'], force: true);
            $primary = $galleryPaths[0] ?? null;

            if ($primary) {
                $product->update(['image' => $primary]);
            }

            $product->images()->delete();

            foreach ($galleryPaths as $sortOrder => $path) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'sort_order' => $sortOrder,
                ]);
            }
        }
    }

    /** @return \Illuminate\Support\Collection<int, Coupon> */
    private function seedCoupons()
    {
        $couponData = [
            ['code' => 'SALE10', 'type' => 'percent', 'value' => 10, 'active' => true],
            ['code' => 'SALE20', 'type' => 'percent', 'value' => 20, 'active' => true],
            ['code' => 'WELCOME50', 'type' => 'fixed', 'value' => 50000, 'active' => true],
            ['code' => 'SUMMER15', 'type' => 'percent', 'value' => 15, 'active' => true],
            ['code' => 'VIP100', 'type' => 'fixed', 'value' => 100000, 'active' => true],
            ['code' => 'FRIDAY25', 'type' => 'percent', 'value' => 25, 'active' => true],
            ['code' => 'FLASH20', 'type' => 'percent', 'value' => 20, 'active' => true],
            ['code' => 'NEWUSER', 'type' => 'percent', 'value' => 12, 'active' => true],
            ['code' => 'SPRING30', 'type' => 'percent', 'value' => 30, 'active' => true],
            ['code' => 'FIXED75K', 'type' => 'fixed', 'value' => 75000, 'active' => true],
            ['code' => 'LOYAL5', 'type' => 'percent', 'value' => 5, 'active' => true],
            ['code' => 'BULK15', 'type' => 'percent', 'value' => 15, 'active' => true],
            ['code' => 'GIFT200', 'type' => 'fixed', 'value' => 200000, 'active' => true],
            ['code' => 'WEEKEND18', 'type' => 'percent', 'value' => 18, 'active' => true],
            ['code' => 'EXPIRED', 'type' => 'percent', 'value' => 50, 'active' => false],
        ];

        $coupons = collect();

        foreach ($couponData as $i => $data) {
            $coupons->push(Coupon::updateOrCreate(
                ['code' => $data['code']],
                [
                    'type' => $data['type'],
                    'value' => $data['value'],
                    'min_order' => $i % 2 === 0 ? 100000 : 0,
                    'max_uses' => $i % 3 === 0 ? 100 : null,
                    'used_count' => rand(0, 8),
                    'expires_at' => $data['active'] ? now()->addMonths(3) : now()->subDay(),
                    'is_active' => $data['active'],
                ]
            ));
        }

        return $coupons;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $customers
     * @param  \Illuminate\Support\Collection<int, Coupon>  $coupons
     */
    private function seedUserCoupons($customers, $coupons): void
    {
        $activeCoupons = $coupons->where('is_active', true)->values();
        $pairs = [];

        for ($i = 0; $i < self::MIN; $i++) {
            $pairs[] = [
                'user_id' => $customers->get($i % $customers->count())->id,
                'coupon_id' => $activeCoupons->get($i % $activeCoupons->count())->id,
            ];
        }

        foreach ($pairs as $pair) {
            DB::table('user_coupons')->updateOrInsert(
                ['user_id' => $pair['user_id'], 'coupon_id' => $pair['coupon_id']],
                ['created_at' => now()->subDays(rand(1, 30)), 'updated_at' => now()]
            );
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $customers
     * @param  \Illuminate\Support\Collection<int, Product>  $products
     */
    private function seedWishlists($customers, $products): void
    {
        for ($i = 0; $i < self::MIN; $i++) {
            Wishlist::updateOrCreate(
                [
                    'user_id' => $customers->get($i % $customers->count())->id,
                    'product_id' => $products->get($i % $products->count())->id,
                ]
            );
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $customers
     * @param  \Illuminate\Support\Collection<int, Product>  $products
     * @param  \Illuminate\Support\Collection<int, ShippingMethod>  $shippingMethods
     * @param  \Illuminate\Support\Collection<int, Coupon>  $coupons
     * @return \Illuminate\Support\Collection<int, Order>
     */
    private function seedOrders($customers, $products, $shippingMethods, $coupons)
    {
        $statuses = OrderStatus::cases();
        $paymentStatuses = [PaymentStatus::Paid, PaymentStatus::Paid, PaymentStatus::Pending, PaymentStatus::Failed, PaymentStatus::Refunded];
        $orders = collect();
        $count = max(self::MIN, 20);

        for ($i = 1; $i <= $count; $i++) {
            $customer = $customers->get(($i - 1) % $customers->count());
            $address = $customer->addresses()->where('is_default', true)->first();
            $shipping = $shippingMethods->random();
            $status = $i <= self::MIN ? OrderStatus::Delivered : $statuses[$i % count($statuses)];
            $paymentStatus = $paymentStatuses[$i % count($paymentStatuses)];
            $coupon = $i % 4 === 0 ? $coupons->where('is_active', true)->first() : null;
            $daysAgo = $i <= 3 ? 0 : rand(0, 60);

            $orderProducts = $products->random(rand(1, 3));
            $subtotal = 0;
            $itemsData = [];

            foreach ($orderProducts as $product) {
                $qty = rand(1, 3);
                $lineTotal = $product->price * $qty;
                $subtotal += $lineTotal;
                $itemsData[] = ['product' => $product, 'qty' => $qty, 'total' => $lineTotal];
            }

            $discount = $coupon ? $coupon->calculateDiscount($subtotal) : 0;
            $shippingCost = $shipping->calculateCost($subtotal - $discount);
            $createdAt = now()->subDays($daysAgo)->subHours(rand(1, 12));

            $order = Order::updateOrCreate(
                ['order_number' => 'ORD-DEMO-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT)],
                [
                    'user_id' => $customer->id,
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                    'payment_ref' => $paymentStatus === PaymentStatus::Paid ? 'REF-'.rand(100000, 999999) : null,
                    'payment_authority' => $paymentStatus === PaymentStatus::Pending ? 'AUTH-'.Str::random(10) : null,
                    'tracking_code' => in_array($status, [OrderStatus::Shipped, OrderStatus::Delivered], true) ? 'TRK-'.rand(1000000, 9999999) : null,
                    'shipping_method_id' => $shipping->id,
                    'coupon_id' => $coupon?->id,
                    'coupon_code' => $coupon?->code,
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'discount_amount' => $discount,
                    'total' => $subtotal - $discount + $shippingCost,
                    'shipping_address' => $address?->toSnapshot() ?? [],
                    'notes' => $i % 4 === 0 ? 'لطفاً قبل از ارسال تماس بگیرید.' : null,
                    'internal_notes' => $i % 5 === 0 ? 'مشتری VIP — اولویت ارسال' : null,
                    'paid_at' => $paymentStatus === PaymentStatus::Paid ? $createdAt->copy()->addHour() : null,
                    'shipped_at' => in_array($status, [OrderStatus::Shipped, OrderStatus::Delivered], true) ? $createdAt->copy()->addDays(2) : null,
                    'delivered_at' => $status === OrderStatus::Delivered ? $createdAt->copy()->addDays(4) : null,
                    'admin_read_at' => $i % 6 === 0 ? null : $createdAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]
            );

            $order->items()->delete();

            foreach ($itemsData as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'product_sku' => $item['product']->sku,
                    'price' => $item['product']->price,
                    'quantity' => $item['qty'],
                    'total' => $item['total'],
                ]);
            }

            $orders->push($order);
        }

        return $orders;
    }

    /** @param  \Illuminate\Support\Collection<int, Order>  $orders */
    private function seedReturns($orders): void
    {
        $delivered = $orders->filter(fn ($o) => $o->status === OrderStatus::Delivered)->values();
        $reasons = [
            'رنگ محصول با تصویر سایت متفاوت بود.',
            'کالا آسیب‌دیده به دستم رسید.',
            'سایز مناسب نبود.',
            'محصول اشتباه ارسال شده.',
            'کیفیت مطابق انتظار نبود.',
            'بسته‌بندی نامناسب بود.',
            'کالا معیوب بود.',
            'دوست نداشتم — مرجوعی.',
            'سایز کوچک‌تر از انتظار.',
            'رنگ متفاوت از عکس.',
            'دکمه خراب بود.',
            'گارانتی نداشت.',
            'محصول دست‌دوم به نظر می‌رسید.',
            'لوازم جانبی ناقص بود.',
            'آسیب در حمل‌ونقل.',
        ];

        for ($i = 0; $i < self::MIN; $i++) {
            $order = $delivered->get($i % max(1, $delivered->count()));

            if (! $order) {
                continue;
            }

            $statuses = [ReturnStatus::Pending, ReturnStatus::Approved, ReturnStatus::Refunded, ReturnStatus::Rejected];

            OrderReturn::updateOrCreate(
                ['order_id' => $order->id, 'reason' => $reasons[$i]],
                [
                    'refund_amount' => $order->total,
                    'status' => $statuses[$i % count($statuses)],
                    'admin_note' => $i % 2 === 0 ? 'درخواست در حال بررسی است.' : 'بررسی انجام شد.',
                    'processed_at' => $i % 3 !== 0 ? now()->subDays(rand(1, 5)) : null,
                ]
            );
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $customers
     * @param  \Illuminate\Support\Collection<int, Product>  $products
     * @param  \Illuminate\Support\Collection<int, Order>  $orders
     */
    private function seedReviews($customers, $products, $orders): void
    {
        $delivered = $orders->first(fn ($o) => $o->status === OrderStatus::Delivered);
        $comments = [
            ['rating' => 5, 'comment' => 'کیفیت عالی بود، پیشنهاد می‌کنم.', 'approved' => true],
            ['rating' => 4, 'comment' => 'ارسال سریع و بسته‌بندی خوب.', 'approved' => true],
            ['rating' => 5, 'comment' => 'محصول مطابق توضیحات بود.', 'approved' => true],
            ['rating' => 3, 'comment' => 'کیفیت متوسط ولی قابل قبول.', 'approved' => false],
            ['rating' => 2, 'comment' => 'رنگ با عکس فرق داشت.', 'approved' => false],
            ['rating' => 5, 'comment' => 'بهترین خرید این ماه.', 'approved' => true],
            ['rating' => 4, 'comment' => 'قیمت مناسب نسبت به کیفیت.', 'approved' => true],
            ['rating' => 1, 'comment' => 'از خرید راضی نبودم.', 'approved' => false],
            ['rating' => 5, 'comment' => 'عالی! ممنون از فروشگاه.', 'approved' => true],
            ['rating' => 4, 'comment' => 'توصیه می‌کنم حتماً امتحان کنید.', 'approved' => false],
            ['rating' => 5, 'comment' => 'بسته‌بندی مرتب و ارسال به‌موقع.', 'approved' => true],
            ['rating' => 3, 'comment' => 'انتظار بیشتری داشتم.', 'approved' => false],
            ['rating' => 4, 'comment' => 'برای هدیه عالی بود.', 'approved' => true],
            ['rating' => 5, 'comment' => 'دقیقاً همان چیزی که می‌خواستم.', 'approved' => true],
            ['rating' => 2, 'comment' => 'سایزبندی دقیق نبود.', 'approved' => false],
        ];

        foreach ($comments as $i => $data) {
            $customer = $customers->get($i % $customers->count());
            $product = $products->get($i % $products->count());

            Review::updateOrCreate(
                ['user_id' => $customer->id, 'product_id' => $product->id],
                [
                    'order_id' => $delivered?->id,
                    'rating' => $data['rating'],
                    'comment' => $data['comment'],
                    'is_approved' => $data['approved'],
                    'created_at' => now()->subDays(rand(1, 30)),
                ]
            );
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $customers
     */
    private function seedContactMessages($customers, User $admin): void
    {
        $messages = [
            ['name' => 'سارا موسوی', 'email' => 'sara.mousavi@gmail.com', 'subject' => 'سفارشم هنوز نرسیده', 'is_read' => false, 'days_ago' => 0],
            ['name' => 'رضا کریمی', 'email' => 'reza.karimi@yahoo.com', 'subject' => 'مشکل در پرداخت آنلاین', 'is_read' => false, 'days_ago' => 1],
            ['name' => 'مریم حسینی', 'email' => 'maryam.h@gmail.com', 'subject' => 'درخواست فاکتور رسمی', 'is_read' => false, 'days_ago' => 2],
            ['name' => 'امیر رضایی', 'email' => 'amir.rezaei@outlook.com', 'subject' => 'محصول معیوب — درخواست تعویض', 'is_read' => true, 'days_ago' => 4],
            ['name' => 'فاطمه نوری', 'email' => 'fateme.nouri@gmail.com', 'subject' => 'آیا ارسال به شهرستان رایگانه؟', 'is_read' => true, 'days_ago' => 5],
            ['name' => 'حسین جعفری', 'email' => 'hossein.j@company.ir', 'subject' => 'پیشنهاد همکاری عمده', 'is_read' => true, 'days_ago' => 7],
            ['name' => 'نرگس صادقی', 'email' => 'narges.s@gmail.com', 'subject' => 'کد تخفیف کار نمی‌کنه', 'is_read' => true, 'days_ago' => 8],
            ['name' => 'پویا اکبری', 'email' => 'pouya.akbari@gmail.com', 'subject' => 'تغییر آدرس قبل از ارسال', 'is_read' => false, 'days_ago' => 0],
            ['name' => 'لیلا باقری', 'email' => 'leila.bagheri@yahoo.com', 'subject' => 'سوال درباره گارانتی', 'is_read' => true, 'days_ago' => 10],
            ['name' => 'کامران شریفی', 'email' => 'kamran.sharifi@gmail.com', 'subject' => 'انتقاد از بسته‌بندی', 'is_read' => true, 'days_ago' => 12],
            ['name' => 'ندا رحیمی', 'email' => 'neda.rahimi@gmail.com', 'subject' => 'زمان تحویل سفارش', 'is_read' => false, 'days_ago' => 1],
            ['name' => 'بهرام ملکی', 'email' => 'bahram.maleki@yahoo.com', 'subject' => 'درخواست لغو سفارش', 'is_read' => true, 'days_ago' => 3],
            ['name' => 'زهرا احمدی', 'email' => 'zahra.ahmadi@gmail.com', 'subject' => 'سوال درباره ارسال رایگان', 'is_read' => true, 'days_ago' => 6],
            ['name' => 'علی محمدی', 'email' => 'ali.mohammadi@gmail.com', 'subject' => 'محصول اشتباه ارسال شد', 'is_read' => false, 'days_ago' => 2],
            ['name' => 'مهدی قاسمی', 'email' => 'mahdi.ghasemi@outlook.com', 'subject' => 'پیشنهاد بهبود سایت', 'is_read' => true, 'days_ago' => 14],
        ];

        foreach ($messages as $i => $data) {
            $userId = User::where('email', $data['email'])->value('id')
                ?? $customers->get($i % $customers->count())->id;

            $message = ContactMessage::updateOrCreate(
                ['subject' => $data['subject'], 'email' => $data['email']],
                [
                    'user_id' => $userId,
                    'name' => $data['name'],
                    'phone' => '0912'.str_pad((string) ($i + 1000), 7, '0', STR_PAD_LEFT),
                    'message' => "سلام وقت بخیر\n\n{$data['subject']}. لطفاً راهنمایی کنید.\n\nممنون",
                    'is_read' => $data['is_read'],
                    'created_at' => now()->subDays($data['days_ago']),
                ]
            );

            if ($message->replies()->count() === 0) {
                ContactMessageReply::create([
                    'contact_message_id' => $message->id,
                    'user_id' => $admin->id,
                    'body' => "سلام {$data['name']}\n\nپیام شما بررسی شد. در صورت نیاز با شما تماس می‌گیریم.",
                    'is_from_admin' => true,
                    'sms_sent' => $i % 4 === 0,
                    'email_sent' => $i % 3 === 0,
                    'created_at' => now()->subDays(max(0, $data['days_ago'] - 1)),
                ]);

                $message->update([
                    'last_replied_at' => now()->subDays(max(0, $data['days_ago'] - 1)),
                    'has_unread_reply_for_user' => $i % 3 === 0,
                ]);
            }
        }
    }

    private function seedSliders(): void
    {
        $titles = [
            'تخفیف ویژه بهاره', 'محصولات پرفروش', 'ارسال رایگان', 'جدیدترین‌ها', 'پیشنهاد هفته',
            'لوازم خانگی', 'کالای دیجیتال', 'پوشاک زمستانه', 'حراج تابستان', 'برندهای برتر',
            'فروش ویژه', 'محصولات جدید', 'تخفیف آخر هفته', 'کالکشن پاییز', 'پیشنهاد ویژه اعضا',
        ];

        foreach ($titles as $i => $title) {
            Slider::updateOrCreate(
                ['title' => $title],
                [
                    'subtitle' => 'خرید آسان و مطمئن از فروشگاه',
                    'image' => $this->images->sliderForTitle($title),
                    'button_text' => 'مشاهده محصولات',
                    'button_url' => '/products',
                    'sort_order' => $i,
                    'is_active' => $i < 10,
                ]
            );
        }
    }

    private function seedBanners(): void
    {
        $titles = [
            'ارسال سریع', 'گارانتی اصالت', 'پشتیبانی ۲۴ ساعته', 'بازگشت ۷ روزه',
            'تخفیف اعضا', 'پرداخت امن', 'محصولات ویژه', 'فروش فصلی',
            'ارسال رایگان', 'ضمانت بازگشت', 'پشتیبانی تلفنی', 'تحویل درب منزل',
            'تخفیف اولین خرید', 'برندهای معتبر', 'پرداخت در محل',
        ];

        foreach ($titles as $i => $title) {
            Banner::updateOrCreate(
                ['title' => $title, 'position' => 'home'],
                [
                    'description' => "بنر تبلیغاتی {$title}",
                    'image' => $this->images->bannerForTitle($title),
                    'link' => '/products',
                    'sort_order' => $i,
                    'is_active' => $i < 12,
                ]
            );
        }
    }

    private function seedFaqs(): void
    {
        $faqs = [
            ['question' => 'چگونه سفارش خود را پیگیری کنم؟', 'answer' => 'کد پیگیری در پنل کاربری و پیامک برای شما ارسال می‌شود.', 'sort_order' => 1],
            ['question' => 'شرایط ارسال رایگان چیست؟', 'answer' => 'برای سفارش‌های بالای ۵۰۰ هزار تومان ارسال رایگان است.', 'sort_order' => 2],
            ['question' => 'آیا امکان مرجوعی کالا وجود دارد؟', 'answer' => 'بله، تا ۷ روز پس از تحویل می‌توانید درخواست مرجوعی ثبت کنید.', 'sort_order' => 3],
            ['question' => 'روش‌های پرداخت چیست؟', 'answer' => 'پرداخت از طریق درگاه زرین‌پال انجام می‌شود.', 'sort_order' => 4],
            ['question' => 'چقدر طول می‌کشد تا سفارش برسد؟', 'answer' => 'معمولاً بین ۱ تا ۵ روز کاری زمان می‌برد.', 'sort_order' => 5],
            ['question' => 'آیا گارانتی محصولات معتبر است؟', 'answer' => 'تمام محصولات دارای گارانتی اصالت و سلامت فیزیکی هستند.', 'sort_order' => 6],
            ['question' => 'چطور کد تخفیف استفاده کنم؟', 'answer' => 'در صفحه تسویه حساب کد را وارد کنید.', 'sort_order' => 7],
            ['question' => 'آیا امکان پرداخت در محل وجود دارد؟', 'answer' => 'در حال حاضر فقط پرداخت آنلاین فعال است.', 'sort_order' => 8],
            ['question' => 'چگونه حساب کاربری بسازم؟', 'answer' => 'از منوی بالای سایت روی ثبت‌نام کلیک کنید.', 'sort_order' => 9],
            ['question' => 'آیا ارسال به شهرستان دارید؟', 'answer' => 'بله، به تمام شهرهای ایران ارسال داریم.', 'sort_order' => 10],
            ['question' => 'چطور با پشتیبانی تماس بگیرم؟', 'answer' => 'از فرم تماس با ما یا تلفن پشتیبانی استفاده کنید.', 'sort_order' => 11],
            ['question' => 'آیا امکان تغییر آدرس بعد از سفارش هست؟', 'answer' => 'تا قبل از ارسال می‌توانید با پشتیبانی تماس بگیرید.', 'sort_order' => 12],
            ['question' => 'فاکتور رسمی صادر می‌کنید؟', 'answer' => 'بله، درخواست فاکتور را در یادداشت سفارش بنویسید.', 'sort_order' => 13],
            ['question' => 'محصولات تخفیف‌دار چطور مشخص می‌شوند؟', 'answer' => 'با برچسب تخفیف و قیمت خط‌خورده نمایش داده می‌شوند.', 'sort_order' => 14],
            ['question' => 'خبرنامه چه مزایایی دارد؟', 'answer' => 'از تخفیف‌ها و محصولات جدید زودتر مطلع می‌شوید.', 'sort_order' => 15],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                [...$faq, 'is_active' => true]
            );
        }
    }

    private function seedBlogPosts(): void
    {
        $posts = [
            ['title' => 'راهنمای خرید آنلاین امن', 'excerpt' => 'نکات مهم برای خرید مطمئن از فروشگاه‌های اینترنتی', 'content' => "خرید آنلاین باید ساده و امن باشد.\n\nقبل از پرداخت، مشخصات محصول و قیمت نهایی را بررسی کنید."],
            ['title' => 'چطور از تخفیف‌ها بیشترین بهره را ببریم؟', 'excerpt' => 'استفاده هوشمندانه از کدهای تخفیف', 'content' => "کدهای تخفیف را در صفحه تسویه حساب وارد کنید.\n\nخبرنامه را دنبال کنید."],
            ['title' => 'نکات نگهداری از محصولات', 'excerpt' => 'راهنمای کوتاه برای افزایش عمر مفید کالا', 'content' => "دفترچه راهنمای محصول را مطالعه کنید.\n\nفاکتور خرید را نگه دارید."],
            ['title' => 'ترندهای مد پاییز', 'excerpt' => 'جدیدترین سبک‌های پاییزی', 'content' => "رنگ‌های گرم و لایه‌لباسی از ترندهای امسال هستند."],
            ['title' => 'راهنمای انتخاب کفش ورزشی', 'excerpt' => 'نکات مهم قبل از خرید', 'content' => "نوع فعالیت و سایز پا را در نظر بگیرید."],
            ['title' => '۵ اشتباه رایج در خرید آنلاین', 'excerpt' => 'از این اشتباهات دوری کنید', 'content' => "خواندن نظرات و مقایسه قیمت را فراموش نکنید."],
            ['title' => 'چگونه سایز مناسب انتخاب کنیم؟', 'excerpt' => 'راهنمای سایزبندی', 'content' => "جدول سایز هر محصول را بررسی کنید."],
            ['title' => 'مزایای خرید از فروشگاه ما', 'excerpt' => 'چرا ما را انتخاب کنید؟', 'content' => "ارسال سریع، پشتیبانی و ضمانت بازگشت."],
            ['title' => 'راهنمای هدیه دادن', 'excerpt' => 'ایده‌های هدیه برای مناسبت‌ها', 'content' => "کارت هدیه و بسته‌بندی ویژه داریم."],
            ['title' => 'نگهداری از لوازم الکترونیکی', 'excerpt' => 'افزایش عمر دستگاه‌های دیجیتال', 'content' => "از نوسان برق و رطوبت محافظت کنید."],
            ['title' => 'آشپزخانه مدرن', 'excerpt' => 'لوازم ضروری آشپزخانه', 'content' => "لیست ۱۰ قلم ضروری برای آشپزخانه."],
            ['title' => 'ورزش در خانه', 'excerpt' => 'تجهیزات ورزشی خانگی', 'content' => "با تجهیزات ساده در خانه ورزش کنید."],
            ['title' => 'مراقبت از پوست در زمستان', 'excerpt' => 'محصولات پیشنهادی', 'content' => "مرطوب‌کننده و ضدآفتاب را فراموش نکنید."],
            ['title' => 'کتاب‌های پرفروش ماه', 'excerpt' => 'پیشنهاد سردبیر', 'content' => "لیست ۵ کتاب برتر این ماه."],
            ['title' => 'راهنمای خرید لپ‌تاپ', 'excerpt' => 'انتخاب بر اساس نیاز', 'content' => "RAM، پردازنده و نوع استفاده را مقایسه کنید."],
        ];

        foreach ($posts as $i => $post) {
            BlogPost::updateOrCreate(
                ['slug' => Str::slug($post['title'])],
                [
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'content' => $post['content'],
                    'image' => $this->images->blogForTitle($post['title']),
                    'is_published' => true,
                    'published_at' => now()->subDays($i * 2),
                    'sort_order' => $i,
                ]
            );
        }
    }

    private function seedNewsletterSubscribers(): void
    {
        for ($i = 1; $i <= self::MIN; $i++) {
            NewsletterSubscriber::firstOrCreate([
                'email' => 'subscriber'.$i.'@newsletter.test',
            ]);
        }
    }

    private function seedSiteVisits(): void
    {
        for ($day = 29; $day >= 0; $day--) {
            $date = now()->subDays($day)->toDateString();
            $visitorCount = rand(self::MIN, 50);

            for ($i = 0; $i < $visitorCount; $i++) {
                SiteVisit::query()->insertOrIgnore([
                    'visit_date' => $date,
                    'visitor_key' => hash('sha256', "demo-{$date}-{$i}"),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedStoreImages(): void
    {
        $logo = $this->images->storeLogo();
        $favicon = $this->images->storeFavicon($logo);
        $enamad = $this->images->enamad();

        if ($logo) {
            StoreSetting::set('logo', $logo, 'appearance', 'image');
        }

        StoreSetting::set('favicon', $favicon ?? '', 'appearance', 'image');

        if ($enamad) {
            StoreSetting::set('enamad_image', $enamad, 'homepage', 'image');
        }
    }

    /** @param  \Illuminate\Support\Collection<int, Coupon>  $coupons */
    private function seedHomepageSettings($coupons): void
    {
        $coupon = $coupons->firstWhere('code', 'SALE20') ?? $coupons->first();

        $overrides = [
            'homepage_flash_sale_enabled' => '1',
            'homepage_flash_sale_ends_at' => now()->addDays(3)->endOfDay()->toDateTimeString(),
            'homepage_brands_enabled' => '1',
            'homepage_bestsellers_enabled' => '1',
            'homepage_reviews_enabled' => '1',
            'homepage_faq_enabled' => '1',
            'homepage_blog_enabled' => '1',
            'payment_gateways' => 'زرین‌پال',
        ];

        if ($coupon) {
            $overrides['homepage_featured_coupon_id'] = (string) $coupon->id;
        }

        foreach ($overrides as $key => $value) {
            StoreSetting::set($key, $value, 'homepage', str_ends_with($key, '_enabled') ? 'boolean' : 'string');
        }
    }
}
