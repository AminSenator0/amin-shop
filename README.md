# فروشگاه آنلاین — Laravel

فروشگاه اینترنتی فارسی با پنل مدیریت، سبد خرید، پرداخت زرین‌پال (سندباکس)، فاکتور PDF، وبلاگ، کوپن تخفیف و دادهٔ دمو آماده.
## نیازمندی‌ها

- PHP 8.4 یا بالاتر
- افزونه‌های PHP: `sqlite3`, `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `gd`
- [Composer](https://getcomposer.org)
- [Node.js](https://nodejs.org) 18+ و npm (فقط برای بیلد فرانت‌اند)

## نصب و اجرا

```bash
# ۱. وابستگی‌ها
composer install
php artisan key:generate

# ۲. دیتابیس و دادهٔ دمو
touch database/database.sqlite
php artisan migrate --seed

# ۳. لینک تصاویر آپلود
php artisan storage:link

# ۴. فرانت‌اند (اختیاری — فایل‌های build از قبل موجودند)
npm install
npm run build

# ۵. اجرا
php artisan serve
php artisan schedule:work
php artisan queue:work
C:\Users\TS\Music\shop\public\robots.txt
$discountPercent = (float) (\App\Support\StoreSettings::get('c2c_discount_percent') ?? 1); // تخفیف کارت

 http://127.0.0.1:8000/api/v3/torob/products
 http://127.0.0.1:8000/api/v1/emalls/products
 

```
