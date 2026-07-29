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
cp .env.example .env
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
```

سایت: [http://127.0.0.1:8000](http://127.0.0.1:8000)

## ورود به سیستم

| نقش | آدرس | ایمیل | رمز عبور |
|-----|------|-------|----------|
| **مدیر** | `/admin/login` | `admin@shop.test` | `password` |
| **مشتری** | `/login` | `customer1@shop.test` تا `customer15@shop.test` | `password` |

پس از ورود مشتری، پنل کاربری: `/account`

## دادهٔ دمو (بعد از `--seed`)

| مورد | تعداد |
|------|-------|
| محصول | ۱۸ |
| دسته‌بندی | ۱۵ |
| سفارش | ۲۰ |
| مشتری | ۱۵ |
| کوپن فعال | ۱۴ |

نمونه کدهای تخفیف: `SALE10`، `SALE20`، `WELCOME50`، `SUMMER15`، `VIP100`

## تنظیمات مهم `.env`

```env
APP_NAME="فروشگاه من"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite

ZARINPAL_MERCHANT_ID=        # اختیاری — اولویت با پنل مدیریت است
ZARINPAL_SANDBOX=true

SMS_DRIVER=log                 # اختیاری — اولویت با پنل مدیریت است
KAVENEGAR_API_KEY=
KAVENEGAR_SENDER=
MELIPAYAMAK_AUTH=credentials
MELIPAYAMAK_USERNAME=
MELIPAYAMAK_PASSWORD=
MELIPAYAMAK_API_KEY=
MELIPAYAMAK_FROM=

MAIL_MAILER=log                # اختیاری — اولویت با پنل مدیریت است
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=
```

> **توجه:** مقدار `APP_NAME` حتماً داخل کوتیشن باشد.
>
> تنظیمات درگاه زرین‌پال از **پنل مدیریت → تنظیمات → تب پرداخت** وارد می‌شود (مرچنت‌کد و حالت تست/واقعی). مقادیر `.env` فقط وقتی استفاده می‌شوند که در پنل مرچنت‌کد خالی باشد.
>
> در حالت **Sandbox** حتی بدون مرچنت‌کد، مشتری به درگاه واقعی تست زرین‌پال هدایت می‌شود (پرداخت آزمایشی داخلی وجود ندارد). در حالت واقعی، مرچنت‌کد الزامی است.
>
> تنظیمات پیامک از **پنل مدیریت → تنظیمات → تب پیامک** وارد می‌شود (کاوه‌نگار یا ملی‌پیامک).
>
> تنظیمات ایمیل از **پنل مدیریت → تنظیمات → تب ایمیل** وارد می‌شود (SMTP و آدرس فرستنده).
>
> ورود/ثبت‌نام با OTP از **پنل مدیریت → تنظیمات → تب ورود** فعال می‌شود (کانال موبایل یا ایمیل). رمز عبور همچنان در دسترس است.

## آدرس‌های پرکاربرد

| صفحه | مسیر |
|------|------|
| فروشگاه | `/` |
| محصولات | `/products` |
| سبد خرید | `/cart` |
| پنل مدیریت | `/admin` |
| پنل کاربر | `/account` |
| پیگیری سفارش | `/track-order` |

## تست

```bash
php artisan test
```
