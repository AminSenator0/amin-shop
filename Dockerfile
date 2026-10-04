# ---------- مرحله ۱: بیلد فرانت‌اند ----------
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

# ---------- مرحله ۲: ایمیج نهایی ----------
FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
        libpng-dev libjpeg-dev libfreetype6-dev libzip-dev \
        libonig-dev libxml2-dev libicu-dev \
        unzip zip curl git nginx supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd pdo pdo_mysql mbstring exif pcntl bcmath zip intl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --no-dev --ignore-platform-reqs

COPY . .

COPY --from=frontend /app/public/build ./public/build

COPY docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/zz-shop.conf
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

RUN composer dump-autoload --optimize \
    && php artisan storage:link || true \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache \
    && mkdir -p /var/log/supervisor

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]