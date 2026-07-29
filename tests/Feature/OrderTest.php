<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private function seedOrderFixture(): array
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::create(['name' => 'تست', 'slug' => 'test', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول تست',
            'slug' => 'product-test',
            'price' => 100000,
            'sku' => 'SKU-TEST-1',
            'stock' => 10,
            'is_active' => true,
        ]);
        $shipping = ShippingMethod::create([
            'name' => 'پست',
            'cost' => 50000,
            'is_active' => true,
        ]);
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Pending,
            'shipping_method_id' => $shipping->id,
            'subtotal' => 100000,
            'shipping_cost' => 50000,
            'discount_amount' => 0,
            'total' => 150000,
            'shipping_address' => [
                'full_name' => 'کاربر تست',
                'phone' => '09121234567',
                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'خیابان تست',
                'postal_code' => '1234567890',
            ],
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'price' => $product->price,
            'quantity' => 1,
            'total' => $product->price,
        ]);
        $product->decrement('stock', 1);

        return compact('user', 'admin', 'product', 'shipping', 'order');
    }

    public function test_user_can_view_own_orders(): void
    {
        ['user' => $user, 'order' => $order] = $this->seedOrderFixture();

        $this->actingAs($user)
            ->get(route('user.orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_user_cannot_view_other_users_order(): void
    {
        ['order' => $order] = $this->seedOrderFixture();
        $other = User::factory()->create();

        $this->actingAs($other)
            ->get(route('user.orders.show', $order))
            ->assertForbidden();
    }

    public function test_user_can_cancel_pending_order_and_restore_stock(): void
    {
        ['user' => $user, 'product' => $product, 'order' => $order] = $this->seedOrderFixture();
        $stockBefore = $product->fresh()->stock;

        $this->actingAs($user)
            ->post(route('user.orders.cancel', $order))
            ->assertRedirect(route('user.orders.index'));

        $order->refresh();
        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertSame($stockBefore + 1, $product->fresh()->stock);
    }

    public function test_reorder_adds_products_to_cart(): void
    {
        ['user' => $user, 'order' => $order] = $this->seedOrderFixture();

        $this->actingAs($user)
            ->post(route('user.orders.reorder', $order))
            ->assertRedirect(route('user.cart.index'));

        $this->assertSame(1, app(CartService::class)->count());
    }

    public function test_public_tracking_requires_matching_phone(): void
    {
        ['order' => $order] = $this->seedOrderFixture();

        $this->get(route('orders.track', ['code' => $order->order_number]))
            ->assertOk()
            ->assertSee('شماره موبایل');

        $this->get(route('orders.track', ['code' => $order->order_number, 'phone' => '09120000000']))
            ->assertOk()
            ->assertSee('مطابقت ندارد');

        $this->get(route('orders.track', ['code' => $order->order_number, 'phone' => '09121234567']))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_admin_can_mark_all_orders_read(): void
    {
        ['admin' => $admin] = $this->seedOrderFixture();

        $this->actingAs($admin)
            ->patch(route('admin.orders.mark-all-read'))
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSame(0, app(OrderService::class)->unreadCount());
    }

    public function test_api_returns_authenticated_user_orders(): void
    {
        ['user' => $user, 'order' => $order] = $this->seedOrderFixture();

        $this->actingAs($user)
            ->getJson('/api/v1/orders')
            ->assertOk()
            ->assertJsonPath('data.0.order_number', $order->order_number);
    }

    public function test_cancel_expired_pending_orders(): void
    {
        ['product' => $product, 'order' => $order] = $this->seedOrderFixture();
        Order::whereKey($order->id)->update(['created_at' => now()->subDays(2)]);
        $stockBefore = $product->fresh()->stock;

        $count = app(OrderService::class)->cancelExpiredPendingOrders();

        $this->assertSame(1, $count);
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame($stockBefore + 1, $product->fresh()->stock);
    }
}
