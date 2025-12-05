<?php

namespace Modules\Category\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Category\Domain\Models\Category;
use Illuminate\Support\Str;

class CategoryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa dữ liệu cũ để tránh trùng lặp khi seed lại
        Category::query()->delete();

        $rooms = [
            'Phòng Khách' => ['Sofa & Ghế thư giãn', 'Bàn trà & Bàn bên', 'Kệ Tivi', 'Tủ giày & Tủ trang trí'],
            'Phòng Ngủ' => ['Giường ngủ', 'Tủ quần áo', 'Bàn trang điểm', 'Tab đầu giường', 'Nệm'],
            'Phòng Bếp & Ăn' => ['Bàn ăn', 'Ghế ăn', 'Tủ bếp', 'Quầy Bar'],
            'Phòng Làm Việc' => ['Bàn làm việc', 'Ghế văn phòng', 'Kệ sách', 'Tủ hồ sơ'],
            'Trang Trí & Đèn' => ['Đèn chùm', 'Đèn cây', 'Thảm trải sàn', 'Gương', 'Tranh treo tường'],
            'Nội Thất Thông Minh' => ['Sofa giường', 'Bàn ăn gấp gọn', 'Giường ẩn tủ']
        ];

        foreach ($rooms as $roomName => $subCategories) {
            // Tạo danh mục cha (Phòng)
            $parent = Category::create([
                'name' => $roomName,
                'slug' => Str::slug($roomName),
                'description' => "Nội thất cao cấp cho " . mb_strtolower($roomName),
                'is_active' => true
            ]);

            // Tạo danh mục con (Loại sản phẩm)
            foreach ($subCategories as $subName) {
                Category::create([
                    'name' => $subName,
                    'slug' => Str::slug($subName),
                    'parent_id' => $parent->id,
                    'description' => "Các mẫu $subName thiết kế hiện đại, sang trọng.",
                    'is_active' => true
                ]);
            }
        }
    }
}