<?php

namespace Modules\Product\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Product\Domain\Models\Attribute;
use Modules\Product\Domain\Models\AttributeValue; // Đảm bảo model này tồn tại
use Illuminate\Support\Str;

class AttributeDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Color (Màu sắc - Visual)
        $this->createAttribute('Color', 'color', 'color', [
            ['value' => 'Charcoal Grey', 'code' => '#36454F'],
            ['value' => 'Cognac Leather', 'code' => '#9A463D'],
            ['value' => 'Navy Blue', 'code' => '#000080'],
            ['value' => 'Beige', 'code' => '#F5F5DC'],
            ['value' => 'Natural Oak', 'code' => '#D2B48C'],
            ['value' => 'Dark Walnut', 'code' => '#5D4037'],
            ['value' => 'Matte Black', 'code' => '#1C1C1C'],
            ['value' => 'Pure White', 'code' => '#FFFFFF'],
            ['value' => 'Emerald Green', 'code' => '#50C878'],
            ['value' => 'Mustard Yellow', 'code' => '#FFDB58'],
        ]);

        // 2. Material (Chất liệu - Select)
        $this->createAttribute('Material', 'material', 'select', [
            ['value' => 'Italian Leather'], ['value' => 'Velvet'], ['value' => 'Solid Oak'],
            ['value' => 'Walnut Wood'], ['value' => 'Marble'], ['value' => 'Powder Coated Metal'],
            ['value' => 'Linen Fabric'], ['value' => 'Rattan'], ['value' => 'Tempered Glass']
        ]);

        // 3. Size (Kích thước - Select)
        $this->createAttribute('Size', 'size', 'select', [
            ['value' => 'King'], ['value' => 'Queen'], ['value' => 'Single'],
            ['value' => 'Standard'], ['value' => 'Large'], ['value' => 'Compact'],
            ['value' => '120cm x 60cm'], ['value' => '160cm x 80cm'], ['value' => '200cm x 100cm']
        ]);

        // 4. Style (Phong cách)
        $this->createAttribute('Style', 'style', 'select', [
            ['value' => 'Modern'], ['value' => 'Minimalist'], ['value' => 'Scandinavian'],
            ['value' => 'Industrial'], ['value' => 'Mid-Century Modern'], ['value' => 'Classic Luxury']
        ]);

        // 5. Origin (Xuất xứ)
        $this->createAttribute('Origin', 'origin', 'select', [
            ['value' => 'Vietnam'], ['value' => 'Italy'], ['value' => 'China'],
            ['value' => 'Malaysia'], ['value' => 'Germany'], ['value' => 'Denmark']
        ]);

        // 6. Warranty (Bảo hành)
        $this->createAttribute('Warranty', 'warranty', 'select', [
            ['value' => '1 Year'], ['value' => '2 Years'], ['value' => '5 Years'], ['value' => 'Lifetime Warranty']
        ]);

        // 7. Assembly (Lắp ráp)
        $this->createAttribute('Assembly', 'assembly', 'select', [
            ['value' => 'Yes (Tools Included)'], ['value' => 'No (Pre-assembled)'], ['value' => 'Professional Installation Recommended']
        ]);
    }

    private function createAttribute($name, $slug, $type, $values)
    {
        $attr = Attribute::firstOrCreate(['slug' => $slug], [
            'name' => $name,
            'type' => $type,
            'uuid' => Str::uuid()
        ]);

        foreach ($values as $val) {
            // Sử dụng relationship để tạo (An toàn hơn)
            // Giả sử Attribute có relationship hasMany('values')
            // Nếu không có relationship, dùng AttributeValue::firstOrCreate như cũ
            if (method_exists($attr, 'values')) {
                $attr->values()->firstOrCreate(
                    ['value' => $val['value']],
                    ['uuid' => Str::uuid(), 'code' => $val['code'] ?? null]
                );
            } else {
                // Fallback nếu chưa định nghĩa relation
                \DB::table('attribute_values')->updateOrInsert(
                    ['attribute_id' => $attr->id, 'value' => $val['value']],
                    ['uuid' => Str::uuid(), 'code' => $val['code'] ?? null]
                );
            }
        }
    }
}