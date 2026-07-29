#!/usr/bin/env bash
set -euo pipefail

BASE="http://127.0.0.1:8000"
PASS=0
FAIL=0
TMP=$(mktemp -d)
trap 'rm -rf "$TMP"' EXIT

ok()   { PASS=$((PASS+1)); echo "  ✓ $1"; }
bad()  { FAIL=$((FAIL+1)); echo "  ✗ $1 — $2"; }

check() {
    local name="$1" url="$2" expect="$3" cookie="${4:-}"
    local args=(-s -o "$TMP/body" -w "%{http_code}" "$url")
    [[ -n "$cookie" ]] && args+=(-b "$cookie" -c "$cookie")
    local code
    code=$(curl "${args[@]}")
    if [[ "$code" == "$expect" ]]; then ok "$name ($code)"; else bad "$name" "expected $expect got $code"; fi
}

check_contains() {
    local name="$1" url="$2" needle="$3" cookie="${4:-}"
    local args=(-s "$url")
    [[ -n "$cookie" ]] && args+=(-b "$cookie")
    local body
    body=$(curl "${args[@]}")
    if echo "$body" | grep -q "$needle"; then ok "$name"; else bad "$name" "missing: $needle"; fi
}

csrf() {
    local cookie="$1" url="$2"
    curl -s -b "$cookie" -c "$cookie" "$url" | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="\([^"]*\)".*/\1/'
}

login() {
    local email="$1" cookie="$2"
    local token
    token=$(csrf "$cookie" "$BASE/login")
    curl -s -b "$cookie" -c "$cookie" -X POST "$BASE/login" \
        -d "_token=$token" -d "email=$email" -d "password=password" -o /dev/null -w "%{http_code}"
}

echo "=== فروشگاه (عمومی) ==="
check "صفحه اصلی" "$BASE/" 200
check "لیست محصولات" "$BASE/products" 200
check "سبد خرید" "$BASE/cart" 200
check "درباره ما" "$BASE/about" 200
check "قوانین" "$BASE/rules" 200
check "تماس" "$BASE/contact" 200
check "ورود" "$BASE/login" 200
check "ثبت‌نام" "$BASE/register" 200
check "پیگیری سفارش" "$BASE/track-order" 200
check "علاقه‌مندی‌ها" "$BASE/wishlist" 200
check "sitemap" "$BASE/sitemap.xml" 200

SLUG=$(cd /Users/aa/Desktop/my-rtl-app && php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo App\Models\Product::where('is_active', true)->value('slug');
")
check "جزئیات محصول" "$BASE/products/$SLUG" 200

echo ""
echo "=== سبد خرید (بدون لاگین) ==="
CART_COOKIE="$TMP/cart.txt"
touch "$CART_COOKIE"
PRODUCT_ID=$(cd /Users/aa/Desktop/my-rtl-app && php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo App\Models\Product::where('is_active', true)->where('stock', '>', 0)->value('id');
")
TOKEN=$(csrf "$CART_COOKIE" "$BASE/products/$SLUG")
CODE=$(curl -s -b "$CART_COOKIE" -c "$CART_COOKIE" -o /dev/null -w "%{http_code}" -X POST "$BASE/cart" \
    -d "_token=$TOKEN" -d "product_id=$PRODUCT_ID" -d "quantity=1" -H "Referer: $BASE/products/$SLUG")
[[ "$CODE" == "302" ]] && ok "افزودن به سبد ($CODE)" || bad "افزودن به سبد" "expected 302 got $CODE"
check_contains "سبد دارای محصول" "$BASE/cart" "تسویه حساب" "$CART_COOKIE"

echo ""
echo "=== احراز هویت ==="
ADMIN_COOKIE="$TMP/admin.txt"
CUSTOMER_COOKIE="$TMP/customer.txt"
touch "$ADMIN_COOKIE" "$CUSTOMER_COOKIE"

ACODE=$(login "admin@shop.test" "$ADMIN_COOKIE")
[[ "$ACODE" == "302" ]] && ok "ورود ادمین ($ACODE)" || bad "ورود ادمین" "got $ACODE"
CCODE=$(login "customer@shop.test" "$CUSTOMER_COOKIE")
[[ "$CCODE" == "302" ]] && ok "ورود مشتری ($CCODE)" || bad "ورود مشتری" "got $CCODE"

# افزودن به سبد با همان سشن مشتری
CTOKEN2=$(csrf "$CUSTOMER_COOKIE" "$BASE/products/$SLUG")
curl -s -b "$CUSTOMER_COOKIE" -c "$CUSTOMER_COOKIE" -o /dev/null -X POST "$BASE/cart" \
    -d "_token=$CTOKEN2" -d "product_id=$PRODUCT_ID" -d "quantity=1" -H "Referer: $BASE/products/$SLUG"

check "checkout بدون لاگین → redirect" "$BASE/checkout" 302
check "checkout مشتری" "$BASE/checkout" 200 "$CUSTOMER_COOKIE"
check "پنل کاربر" "$BASE/account" 200 "$CUSTOMER_COOKIE"
check "سفارشات" "$BASE/account/orders" 200 "$CUSTOMER_COOKIE"
check "آدرس‌ها" "$BASE/account/addresses" 200 "$CUSTOMER_COOKIE"
check "پروفایل" "$BASE/profile" 200 "$CUSTOMER_COOKIE"

ORDER_ID=$(cd /Users/aa/Desktop/my-rtl-app && php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\$u = App\Models\User::where('email','customer@shop.test')->first();
echo App\Models\Order::where('user_id', \$u->id)->value('id');
")
check "جزئیات سفارش" "$BASE/account/orders/$ORDER_ID" 200 "$CUSTOMER_COOKIE"

PAID_ORDER=$(cd /Users/aa/Desktop/my-rtl-app && php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\$u = App\Models\User::where('email','customer@shop.test')->first();
echo App\Models\Order::where('user_id', \$u->id)->where('payment_status','paid')->value('id') ?? '';
")
if [[ -n "$PAID_ORDER" ]]; then
    CODE=$(curl -s -b "$CUSTOMER_COOKIE" -o /dev/null -w "%{http_code}" "$BASE/account/orders/$PAID_ORDER/invoice")
    [[ "$CODE" == "200" ]] && ok "فاکتور HTML ($CODE)" || bad "فاکتور HTML" "got $CODE"
fi

echo ""
echo "=== پنل ادمین ==="
check "admin بدون لاگین" "$BASE/admin" 302
check "داشبورد ادمین" "$BASE/admin" 200 "$ADMIN_COOKIE"
check "محصولات" "$BASE/admin/products" 200 "$ADMIN_COOKIE"
check "دسته‌بندی" "$BASE/admin/categories" 200 "$ADMIN_COOKIE"
check "سفارشات" "$BASE/admin/orders" 200 "$ADMIN_COOKIE"
check "کاربران" "$BASE/admin/users" 200 "$ADMIN_COOKIE"
check "ارسال" "$BASE/admin/shipping" 200 "$ADMIN_COOKIE"
check "کوپن" "$BASE/admin/coupons" 200 "$ADMIN_COOKIE"
check "نظرات" "$BASE/admin/reviews" 200 "$ADMIN_COOKIE"
check "پیام‌ها" "$BASE/admin/messages" 200 "$ADMIN_COOKIE"
check "جزئیات سفارش ادمین" "$BASE/admin/orders/$ORDER_ID" 200 "$ADMIN_COOKIE"

check "مشتری به ادمین → 403" "$BASE/admin" 403 "$CUSTOMER_COOKIE"

echo ""
echo "=== فرم‌ها ==="
CONTACT_COOKIE="$TMP/contact.txt"
touch "$CONTACT_COOKIE"
CTOKEN=$(csrf "$CONTACT_COOKIE" "$BASE/contact")
CCODE=$(curl -s -b "$CONTACT_COOKIE" -c "$CONTACT_COOKIE" -o /dev/null -w "%{http_code}" -X POST "$BASE/contact" \
    -d "_token=$CTOKEN" -d "name=تست" -d "email=test@curl.local" -d "subject=تست curl" -d "message=پیام تست خودکار")
[[ "$CCODE" == "302" ]] && ok "فرم تماس ($CCODE)" || bad "فرم تماس" "got $CCODE"

CHTOKEN=$(csrf "$CUSTOMER_COOKIE" "$BASE/checkout")
CHCODE=$(curl -s -b "$CUSTOMER_COOKIE" -c "$CUSTOMER_COOKIE" -o /dev/null -w "%{http_code}" -X POST "$BASE/checkout/coupon" \
    -d "_token=$CHTOKEN" -d "code=SALE10" -H "Referer: $BASE/checkout")
[[ "$CHCODE" == "302" ]] && ok "اعمال کوپن ($CHCODE)" || bad "اعمال کوپن" "got $CHCODE"
check_contains "کوپن در checkout" "$BASE/checkout" "SALE10" "$CUSTOMER_COOKIE"

echo ""
echo "=== صفحات ایجاد ادمین ==="
check "ایجاد محصول" "$BASE/admin/products/create" 200 "$ADMIN_COOKIE"
check "ایجاد دسته" "$BASE/admin/categories/create" 200 "$ADMIN_COOKIE"
check "ایجاد کوپن" "$BASE/admin/coupons/create" 200 "$ADMIN_COOKIE"
check "ایجاد ارسال" "$BASE/admin/shipping/create" 200 "$ADMIN_COOKIE"
check "اسلایدرها" "$BASE/admin/sliders" 200 "$ADMIN_COOKIE"
check "بنرها" "$BASE/admin/banners" 200 "$ADMIN_COOKIE"
check "تنظیمات فروشگاه" "$BASE/admin/settings" 200 "$ADMIN_COOKIE"

ORD_NUM=$(cd /Users/aa/Desktop/my-rtl-app && php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo App\Models\Order::value('order_number');
")
check "پیگیری با شماره سفارش" "$BASE/track-order?code=$ORD_NUM" 200

NL_COOKIE="$TMP/nl.txt"
touch "$NL_COOKIE"
NLT=$(csrf "$NL_COOKIE" "$BASE/")
NLCODE=$(curl -s -b "$NL_COOKIE" -c "$NL_COOKIE" -o /dev/null -w "%{http_code}" -X POST "$BASE/newsletter" \
    -d "_token=$NLT" -d "email=curl-test@newsletter.test" -H "Referer: $BASE/")
[[ "$NLCODE" == "302" ]] && ok "عضویت خبرنامه ($NLCODE)" || bad "عضویت خبرنامه" "got $NLCODE"

echo ""
echo "=== ثبت سفارش کامل ==="
ADDR_ID=$(cd /Users/aa/Desktop/my-rtl-app && php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\$u = App\Models\User::where('email','customer@shop.test')->first();
echo \$u->addresses()->value('id');
")
SHIP_ID=$(cd /Users/aa/Desktop/my-rtl-app && php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo App\Models\ShippingMethod::where('is_active', true)->value('id');
")
BEFORE=$(cd /Users/aa/Desktop/my-rtl-app && php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo App\Models\Order::count();
")
CHKT=$(csrf "$CUSTOMER_COOKIE" "$BASE/checkout")
curl -s -b "$CUSTOMER_COOKIE" -c "$CUSTOMER_COOKIE" -o /dev/null -X POST "$BASE/checkout" \
    -d "_token=$CHKT" -d "address_id=$ADDR_ID" -d "shipping_method_id=$SHIP_ID" -H "Referer: $BASE/checkout"
AFTER=$(cd /Users/aa/Desktop/my-rtl-app && php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo App\Models\Order::count();
")
[[ "$AFTER" -gt "$BEFORE" ]] && ok "ثبت سفارش جدید" || bad "ثبت سفارش" "count $BEFORE -> $AFTER"

NEW_ORDER=$(cd /Users/aa/Desktop/my-rtl-app && php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo App\Models\Order::latest('id')->value('id');
")
PAYT=$(csrf "$CUSTOMER_COOKIE" "$BASE/checkout/payment/$NEW_ORDER")
PAYCODE=$(curl -s -b "$CUSTOMER_COOKIE" -c "$CUSTOMER_COOKIE" -o /dev/null -w "%{http_code}" -X POST "$BASE/checkout/payment/$NEW_ORDER" \
    -d "_token=$PAYT" -H "Referer: $BASE/checkout/payment/$NEW_ORDER")
[[ "$PAYCODE" == "302" ]] && ok "پرداخت آزمایشی ($PAYCODE)" || bad "پرداخت آزمایشی" "got $PAYCODE"

echo ""
echo "=== فیلترها ==="
check "فیلتر محصولات" "$BASE/products?search=test&sort=price_asc" 200
check "فیلتر سفارشات ادمین" "$BASE/admin/orders?status=delivered" 200 "$ADMIN_COOKIE"

echo ""
echo "=============================="
echo "موفق: $PASS | ناموفق: $FAIL"
echo "=============================="
[[ "$FAIL" -eq 0 ]]
