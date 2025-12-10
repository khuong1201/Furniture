# ===========================
# 1) Builder stage
# ===========================
FROM composer:2 AS builder

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist

COPY . .
RUN composer dump-autoload --optimize


# ===========================
# 2) Runtime PHP-FPM
# ===========================
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev libzip-dev unzip git \
    && docker-php-ext-install pdo pdo_mysql zip

WORKDIR /var/www

COPY --from=builder /app .

RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache
