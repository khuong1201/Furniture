# 🚀 Hướng dẫn cài đặt dự án Laravel Furniture

## 1. Cài đặt môi trường
- PHP: Laravel yêu cầu PHP ≥ 8.1 (tùy phiên bản).
- Composer: Trình quản lý package của PHP.
- MySQL/MariaDB: Nếu dự án có cơ sở dữ liệu.

## 2. Cài đặt dự án

git clone https://github.com/khuong1201/backend-php-Furniture.git
### Backend.
cd backend
composer install

#### 1. Cấu hình môi trường
- Chỉnh sửa thông tin trong file `.env` cho phù hợp (database, user, password, v.v.).

#### 2. Tạo key ứng dụng
php artisan key:

#### 3. Chạy migration và seed dữ liệu
- php artisan migrate:fresh --seed
#### 4. Khởi chạy server

- php artisan serve
- Truy cập tại: http://127.0.0.1:8000
#### 5. api documentation
- php artisan l5-swagger:generate
- Truy cập tại: http://127.0.0.1:8000/api/documentation
### Frontend
cd frontend
npm install
npm run dev
