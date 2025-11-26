Cài đặt môi trường
  PHP: Laravel yêu cầu PHP ≥ 8.1 (tùy phiên bản).
  Composer: Trình quản lý package của PHP.
  MySQL/MariaDB: Nếu dự án có cơ sở dữ liệu.

git clone <https://github.com/khuong1201/backend-php-Furniture.git>
cd <src>
composer install
Chỉnh sửa thông tin trong .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
