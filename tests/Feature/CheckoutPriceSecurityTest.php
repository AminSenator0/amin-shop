<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * تست‌های سخت‌گیرانهٔ دستکاری قیمت در سبد / تسویه / کوپن / ارسال.
 * سرور باید فقط به قیمت دیتابیس و محاسبات خودش اعتماد کند.
 */
class CheckoutPriceSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Product $product;

    private Address $address;

    private ShippingMethod $shippingPaid;

    private ShippingMethod $shippingFree;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $category = Category::create(['name' => 'امنیت', 'slug' => 'sec-price', 'is_active' => true]);
        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول گران',
            'slug' => 'expensive-sec',
            'price' => 250000,
            'sku' => 'SEC-PRICE-1',
            'stock' => 50,
            'is_active' => true,
        ]);
        $this->address = Address::create([
            'user_id' => $this->user->id,
            'title' => 'منزل',
            'full_name' => 'کاربر تست',
            'phone' => '09121234567',
            'province' => 'تهران',
            'city' => 'تهران',
            'address' => 'خیابان تست',
            'postal_code' => '1234567890',
            'is_default' => true,
        ]);
        $this->shippingPaid = ShippingMethod::create([
            'name' => 'پست پیشتاز',
            'cost' => 75000,
            'is_active' => true,
        ]);
        $this->shippingFree = ShippingMethod::create([
            'name' => 'ارسال رایگان مخفی',
            'cost' => 0,
            'is_active' => false,
        ]);
    }

    private function fillCart(int $quantity = 1): void
    {
        $this->actingAs($this->user);
        app(CartService::class)->clear();
        app(CartService::class)->add($this->product->id, $quantity);
    }

    public function test_forged_total_subtotal_and_shipping_cost_fields_are_rejected(): void
    {
        $this->fillCart();

        $this->post(route('checkout.store'), [
            'address_id' => $this->address->id,
            'shipping_method_id' => $this->shippingPaid->id,
            'total' => 1,
            'subtotal' => 1,
            'shipping_cost' => 0,
            'discount_amount' => 999999,
            'price' => 1,
        ])->assertSessionHasErrors([
            'total',
            'subtotal',
            'shipping_cost',
            'discount_amount',
            'price',
        ]);

        $this->assertSame(0, Order::count());
    }

    public function test_order_total_is_calculated_only_from_server_prices(): void
    {
        $this->fillCart(2);

        $this->post(route('checkout.store'), [
            'address_id' => $this->address->id,
            'shipping_method_id' => $this->shippingPaid->id,
            'notes' => 'ok',
        ])->assertRedirect();

        $order = Order::first();
        $this->assertNotNull($order);

        $expectedSubtotal = 250000 * 2;
        $expectedShipping = 75000;
        $expectedTotal = $expectedSubtotal + $expectedShipping;

        $this->assertSame($expectedSubtotal, (int) $order->subtotal);
        $this->assertSame($expectedShipping, (int) $order->shipping_cost);
        $this->assertSame(0, (int) $order->discount_amount);
        $this->assertSame($expectedTotal, (int) $order->total);
        $this->assertSame($expectedSubtotal, (int) $order->items()->sum('total'));
        $this->assertSame(250000, (int) $order->items()->first()->price);
    }

    public function test_inactive_free_shipping_method_cannot_be_selected(): void
    {
        $this->fillCart();

        $this->post(route('checkout.store'), [
            'address_id' => $this->address->id,
            'shipping_method_id' => $this->shippingFree->id,
        ])->assertSessionHasErrors('shipping_method_id');

        $this->assertSame(0, Order::count());
    }

    public function test_foreign_address_cannot_be_used(): void
    {
        $this->fillCart();
        $other = User::factory()->create();
        $foreignAddress = Address::create([
            'user_id' => $other->id,
            'title' => 'دزدی',
            'full_name' => 'هکر',
            'phone' => '09120000000',
            'province' => 'تهران',
            'city' => 'تهران',
            'address' => 'آدرس دیگران',
            'postal_code' => '1111111111',
            'is_default' => true,
        ]);

        $this->post(route('checkout.store'), [
            'address_id' => $foreignAddress->id,
            'shipping_method_id' => $this->shippingPaid->id,
        ])->assertSessionHasErrors('address_id');

        $this->assertSame(0, Order::count());
    }

    public function test_session_cart_price_key_is_ignored(): void
    {
        $this->actingAs($this->user);
        $cart = app(CartService::class);
        $cart->clear();
        $cart->add($this->product->id, 1);

        // شبیه‌سازی دستکاری سشن: تزریق قیمت جعلی
        $sessionCart = session('cart');
        $key = array_key_first($sessionCart);
        $sessionCart[$key]['price'] = 1;
        $sessionCart[$key]['subtotal'] = 1;
        session(['cart' => $sessionCart]);

        $this->assertSame(250000, $cart->subtotal());

        $this->post(route('checkout.store'), [
            'address_id' => $this->address->id,
            'shipping_method_id' => $this->shippingPaid->id,
        ])->assertRedirect();

        $order = Order::first();
        $this->assertSame(250000, (int) $order->subtotal);
        $this->assertSame(325000, (int) $order->total);
    }

    public function test_price_change_before_checkout_uses_locked_db_price(): void
    {
        $this->fillCart(1);

        // قیمت بعد از افزودن به سبد تغییر می‌کند
        $this->product->update(['price' => 180000]);

        $this->post(route('checkout.store'), [
            'address_id' => $this->address->id,
            'shipping_method_id' => $this->shippingPaid->id,
        ])->assertRedirect();

        $order = Order::first();
        $this->assertSame(180000, (int) $order->subtotal);
        $this->assertSame(180000, (int) $order->items()->first()->price);
        $this->assertSame(255000, (int) $order->total);
    }

    public function test_expired_or_inactive_coupon_does_not_reduce_total(): void
    {
        $this->fillCart();
        $coupon = Coupon::create([
            'code' => 'DEAD50',
            'type' => 'percent',
            'value' => 50,
            'min_order' => 0,
            'max_uses' => 10,
            'used_count' => 0,
            'is_active' => false,
            'expires_at' => null,
        ]);

        session(['coupon_code' => $coupon->code]);

        $this->post(route('checkout.store'), [
            'address_id' => $this->address->id,
            'shipping_method_id' => $this->shippingPaid->id,
        ])->assertRedirect();

        $order = Order::first();
        $this->assertSame(0, (int) $order->discount_amount);
        $this->assertNull($order->coupon_id);
        $this->assertSame(325000, (int) $order->total);
        $this->assertSame(0, $coupon->fresh()->used_count);
    }

    public function test_percent_coupon_cannot_create_negative_total_even_if_value_over_100(): void
    {
        $this->fillCart();
        // دادهٔ آلوده در DB (مثل سید قدیمی یا دستکاری مستقیم)
        $coupon = Coupon::create([
            'code' => 'HACK500',
            'type' => 'percent',
            'value' => 500,
            'min_order' => 0,
            'max_uses' => 10,
            'used_count' => 0,
            'is_active' => true,
        ]);
        app(CouponService::class)->apply($coupon->code, 250000);

        $this->assertSame(250000, $coupon->calculateDiscount(250000));

        $this->post(route('checkout.store'), [
            'address_id' => $this->address->id,
            'shipping_method_id' => $this->shippingPaid->id,
        ])->assertRedirect();

        $order = Order::first();
        $this->assertSame(250000, (int) $order->discount_amount);
        $this->assertSame(75000, (int) $order->total); // فقط هزینه ارسال
        $this->assertGreaterThanOrEqual(0, (int) $order->total);
    }

    public function test_valid_coupon_discount_matches_server_math(): void
    {
        $this->fillCart();
        $coupon = Coupon::create([
            'code' => 'SAVE10',
            'type' => 'percent',
            'value' => 10,
            'min_order' => 0,
            'max_uses' => 10,
            'used_count' => 0,
            'is_active' => true,
        ]);
        app(CouponService::class)->apply('SAVE10', 250000);

        $this->post(route('checkout.store'), [
            'address_id' => $this->address->id,
            'shipping_method_id' => $this->shippingPaid->id,
        ])->assertRedirect();

        $order = Order::first();
        $this->assertSame(25000, (int) $order->discount_amount);
        $this->assertSame(250000 - 25000 + 75000, (int) $order->total);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_free_above_shipping_uses_server_subtotal_not_client_claim(): void
    {
        $shipping = ShippingMethod::create([
            'name' => 'ارسال شرطی',
            'cost' => 90000,
            'free_above' => 500000,
            'is_active' => true,
        ]);

        $this->fillCart(1); // 250000 < 500000 → باید هزینه بگیرد

        $this->post(route('checkout.store'), [
            'address_id' => $this->address->id,
            'shipping_method_id' => $shipping->id,
            'shipping_cost' => 0,
        ])->assertSessionHasErrors('shipping_cost');

        $this->post(route('checkout.store'), [
            'address_id' => $this->address->id,
            'shipping_method_id' => $shipping->id,
        ])->assertRedirect();

        $order = Order::first();
        $this->assertSame(90000, (int) $order->shipping_cost);
        $this->assertSame(340000, (int) $order->total);
    }

    public function test_free_above_threshold_waives_shipping_when_subtotal_qualifies(): void
    {
        $shipping = ShippingMethod::create([
            'name' => 'ارسال رایگان بالای سقف',
            'cost' => 90000,
            'free_above' => 400000,
            'is_active' => true,
        ]);

        $this->fillCart(2); // 500000 >= 400000

        $this->post(route('checkout.store'), [
            'address_id' => $this->address->id,
            'shipping_method_id' => $shipping->id,
        ])->assertRedirect();

        $order = Order::first();
        $this->assertSame(0, (int) $order->shipping_cost);
        $this->assertSame(500000, (int) $order->total);
    }

    public function test_inactive_product_in_session_is_not_charged(): void
    {
        $this->fillCart();
        $this->product->update(['is_active' => false]);

        $this->post(route('checkout.store'), [
            'address_id' => $this->address->id,
            'shipping_method_id' => $this->shippingPaid->id,
        ])->assertRedirect(route('user.cart.index'));

        $this->assertSame(0, Order::count());
    }

    public function test_payment_request_uses_order_total_from_database_not_query(): void
    {
        $this->fillCart();

        $this->post(route('checkout.store'), [
            'address_id' => $this->address->id,
            'shipping_method_id' => $this->shippingPaid->id,
        ])->assertRedirect();

        $order = Order::first();
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame(325000, (int) $order->total);

        // تلاش برای تغییر مبلغ ذخیره‌شده سفارش توسط کاربر عادی ممکن نیست
        $this->actingAs($this->user)
            ->put('/orders/'.$order->id, ['total' => 1])
            ->assertNotFound();

        $this->assertSame(325000, (int) $order->fresh()->total);
    }

    public function test_guest_cannot_checkout(): void
    {
        app(CartService::class)->add($this->product->id, 1);

        $this->post(route('checkout.store'), [
            'address_id' => $this->address->id,
            'shipping_method_id' => $this->shippingPaid->id,
        ])->assertRedirect(route('login'));

        $this->assertSame(0, Order::count());
    }
}
