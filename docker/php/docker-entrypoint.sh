#!/bin/bash
set -e

echo "APP_ENV=$APP_ENV | ROLE=$CONTAINER_ROLE"

# 1. Fix permission (non-local)
if [ "$APP_ENV" != "local" ]; then
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
fi

# ==========================
# APP CONTAINER ONLY
# ==========================
if [ "$APP_ENV" = "local" ] && [ "$CONTAINER_ROLE" = "app" ]; then
    echo "--- DEV MODE / APP ROLE ---"

    # 2. WAIT FOR MYSQL (CHUẨN)
    # In ra host để debug lỗi kết nối
    echo "Testing connection to MySQL Host: ${DB_HOST} Port: ${DB_PORT:-3306} User: ${DB_USERNAME}..."
    
    max_tries=60
    counter=0

    until mysqladmin ping \
        -h"${DB_HOST}" \
        -P"${DB_PORT:-3306}" \
        -u"${DB_USERNAME}" \
        -p"${DB_PASSWORD}" \
        --skip-ssl \
        --silent; do
        
        counter=$((counter+1))
        if [ $counter -ge $max_tries ]; then
            echo "❌ MySQL not ready after $max_tries tries. Check DB_HOST in .env!"
            exit 1
        fi
        
        echo "⏳ Waiting for MySQL (${counter}/${max_tries})..."
        sleep 2
    done

    echo "✅ MySQL READY"

    # 3. CLEAR CACHE
    php artisan optimize:clear

    FLAG_FILE="/var/www/storage/.db_initialized"

    # 4. MIGRATE & SEED LOGIC (ĐÃ FIX AN TOÀN)
    if [ "$DB_RESET_ON_BOOT" = "true" ]; then
        echo "⚠️ FORCE RESET DB (Wipe + Migrate + Seed)"
        php artisan db:wipe --force
        php artisan migrate --force
        
        # Chỉ seed ở đây
        if [ "$RUN_SEED" = "true" ]; then
            echo "🌱 Seeding database..."
            php artisan db:seed --force
        fi
        touch "$FLAG_FILE"

    elif [ ! -f "$FLAG_FILE" ]; then
        echo "⚡ First boot → Migrate"
        php artisan migrate --force

        # Hoặc seed ở đây (lần đầu tiên)
        if [ "$RUN_SEED" = "true" ]; then
            echo "🌱 First boot seeding..."
            php artisan db:seed --force
        fi
        touch "$FLAG_FILE"

    else
        echo "✅ DB ready. Checking for new migrations..."
        # Chỉ chạy migrate để update bảng mới (nếu có), KHÔNG SEED LẠI
        php artisan migrate --force
    fi

    # 5. SWAGGER (Optional)
    if [ "$L5_SWAGGER_GENERATE_ALWAYS" = "true" ]; then
        php artisan l5-swagger:generate || true
    fi

    # 6. CACHE
    # php artisan config:cache # Ở local nên hạn chế cache config để sửa .env ăn ngay
    # php artisan route:cache

else
    echo "--- NON-APP CONTAINER ---"
fi

echo "🚀 Starting PHP-FPM..."
exec "$@"