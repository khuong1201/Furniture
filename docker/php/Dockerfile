# --- STAGE 1: BUILD FRONTEND ASSETS (Dùng Node để build file tĩnh) ---
FROM node:20-alpine AS frontend_builder

WORKDIR /app

# Copy file dependency frontend
COPY backend/src/package*.json ./
# Dùng npm ci thay vì npm install (nhanh hơn và chính xác hơn cho build tự động)
RUN npm ci

# Copy toàn bộ code để build
COPY backend/src/ .

# Chạy build (Tạo ra folder public/build)
RUN npm run build

# --- STAGE 2: PHP ENVIRONMENT (Chỉ chạy PHP) ---
FROM php:8.2-fpm

# 1. Cài đặt thư viện hệ thống (KHÔNG CẦN CÀI NODEJS NỮA -> Image nhẹ hơn rất nhiều)
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev \
    zip unzip libzip-dev supervisor default-mysql-client \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Cài PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd sockets zip

# 3. Cài Redis
RUN pecl install redis && docker-php-ext-enable redis

# 4. Lấy Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# 5. Cài Composer (Tối ưu cache layer)
COPY backend/src/composer*.json ./
# Thêm --optimize-autoloader để code chạy nhanh hơn
RUN composer install --no-interaction --no-plugins --no-scripts --no-autoloader

# 6. Copy Code PHP
COPY backend/src/ .

RUN composer dump-autoload --optimize

# --- BƯỚC QUAN TRỌNG NHẤT: ---
# Chỉ lấy kết quả đã build (CSS/JS) từ STAGE 1 bỏ vào đây
# Không mang theo node_modules rác
COPY --from=frontend_builder /app/public/build ./public/build


COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
# 7. Hoàn tất

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# RUN chmod +x /usr/local/bin/docker-entrypoint.sh
# Để Entrypoint có quyền chown, chúng ta dùng user root tạm thời ở đây
USER root 
ENTRYPOINT ["docker-entrypoint.sh"]


CMD ["php-fpm", "-F"]