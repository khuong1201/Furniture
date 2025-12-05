<?php

namespace Modules\Product\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Domain\Models\Attribute;
use Modules\Product\Domain\Models\AttributeValue;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Category\Domain\Models\Category;
use Illuminate\Support\Str;

class ProductDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Setup Attributes (Đặc thù nội thất)
        $this->createAttributes();

        // 2. Generate Products (Số lượng lớn)
        $this->generateLivingRoomProducts();
        $this->generateBedroomProducts();
        $this->generateDiningProducts();
        $this->generateDecorProducts();
    }

    protected function createAttributes(): void
    {
        // Màu sắc (Color)
        $color = Attribute::firstOrCreate(['slug' => 'color'], ['name' => 'Màu sắc', 'type' => 'color']);
        $colors = [
            ['value' => 'Xám ghi', 'code' => '#808080'],
            ['value' => 'Nâu da bò', 'code' => '#8B4513'],
            ['value' => 'Xanh Navy', 'code' => '#000080'],
            ['value' => 'Be (Beige)', 'code' => '#F5F5DC'],
            ['value' => 'Gỗ sồi tự nhiên', 'code' => '#D2B48C'],
            ['value' => 'Gỗ óc chó (Walnut)', 'code' => '#5D4037'],
            ['value' => 'Đen mờ', 'code' => '#1C1C1C'],
            ['value' => 'Trắng sứ', 'code' => '#FFFFFF'],
        ];
        foreach ($colors as $c) {
            AttributeValue::firstOrCreate(['attribute_id' => $color->id, 'value' => $c['value']], ['code' => $c['code']]);
        }

        // Chất liệu (Material)
        $material = Attribute::firstOrCreate(['slug' => 'material'], ['name' => 'Chất liệu', 'type' => 'select']);
        $materials = ['Da thật Italia', 'Da Microfiber', 'Vải nỉ cao cấp', 'Vải nhung', 'Gỗ Sồi Mỹ', 'Gỗ Óc Chó', 'Gỗ Công Nghiệp MDF', 'Kim loại sơn tĩnh điện', 'Đá Marble'];
        foreach ($materials as $m) {
            AttributeValue::firstOrCreate(['attribute_id' => $material->id, 'value' => $m]);
        }

        // Kích thước (Dimensions - Rất quan trọng với nội thất)
        $size = Attribute::firstOrCreate(['slug' => 'size'], ['name' => 'Kích thước', 'type' => 'select']);
        $sizes = ['1m2 x 2m', '1m6 x 2m', '1m8 x 2m', 'Dài 2m2', 'Dài 2m8', 'Dài 1m6', 'Rộng 80cm', 'Size Tiêu Chuẩn'];
        foreach ($sizes as $s) {
            AttributeValue::firstOrCreate(['attribute_id' => $size->id, 'value' => $s]);
        }
    }

    protected function generateLivingRoomProducts(): void
    {
        $sofaCat = Category::where('slug', 'sofa-ghe-thu-gian')->first();
        if (!$sofaCat) return;

        $products = [
            ['name' => 'Sofa Văng Da Bò Ý Napoli', 'base_price' => 15000000],
            ['name' => 'Sofa Góc L Nỉ Hàn Quốc', 'base_price' => 8500000],
            ['name' => 'Sofa Thông Minh Giường Kéo', 'base_price' => 6500000],
            ['name' => 'Ghế Thư Giãn Armchair Retro', 'base_price' => 3200000],
            ['name' => 'Sofa Băng Gỗ Sồi Đệm Nỉ', 'base_price' => 9000000],
        ];

        foreach ($products as $p) {
            $this->createProductWithVariants($p['name'], $sofaCat->id, $p['base_price'], ['color', 'material']);
        }

        $tableCat = Category::where('slug', 'ban-tra-ban-ben')->first();
        if ($tableCat) {
            $this->createProductWithVariants('Bàn Trà Đôi Mặt Đá Ceramic', $tableCat->id, 2500000, ['color']);
            $this->createProductWithVariants('Bàn Trà Gỗ Óc Chó Nguyên Khối', $tableCat->id, 5500000, ['size']);
        }
    }

    protected function generateBedroomProducts(): void
    {
        $bedCat = Category::where('slug', 'giuong-ngu')->first();
        if (!$bedCat) return;

        $products = [
            ['name' => 'Giường Ngủ Gỗ Sồi Nhật Bản', 'base_price' => 7000000],
            ['name' => 'Giường Bọc Nỉ Đầu Giường Cao', 'base_price' => 8500000],
            ['name' => 'Giường Có Ngăn Kéo Thông Minh', 'base_price' => 5500000],
        ];

        foreach ($products as $p) {
            $this->createProductWithVariants($p['name'], $bedCat->id, $p['base_price'], ['size', 'color']);
        }
    }

    protected function generateDiningProducts(): void
    {
        $diningCat = Category::where('slug', 'ban-an')->first();
        if (!$diningCat) return;

        $this->createProductWithVariants('Bộ Bàn Ăn 6 Ghế Howard', $diningCat->id, 12000000, ['material', 'color']);
        $this->createProductWithVariants('Bàn Ăn Thông Minh Kéo Dài', $diningCat->id, 8900000, ['color']);
    }

    protected function generateDecorProducts(): void
    {
        $decorCat = Category::where('slug', 'den-cay')->first();
        if (!$decorCat) return;
        
        $this->createProductWithVariants('Đèn Cây Đọc Sách Bắc Âu', $decorCat->id, 1200000, ['color']);
    }

    /**
     * Helper function để tạo sản phẩm và tự động sinh variants ngẫu nhiên
     */
    protected function createProductWithVariants(string $name, int $categoryId, float $basePrice, array $attributeSlugs): void
    {
        $product = Product::create([
            'name' => $name,
            'category_id' => $categoryId,
            'description' => "Mô tả chi tiết cho sản phẩm $name. Được chế tác từ nguyên vật liệu cao cấp, phù hợp với mọi không gian nội thất hiện đại.",
            'has_variants' => true,
            'is_active' => true,
            'price' => null, 
            'rating_avg' => rand(40, 50) / 10, // 4.0 - 5.0
            'rating_count' => rand(10, 500),
            'sold_count' => rand(0, 1000)
        ]);

        // Lấy các Attribute Values tương ứng
        $attributes = [];
        foreach ($attributeSlugs as $slug) {
            $attr = Attribute::where('slug', $slug)->first();
            if ($attr) {
                // Lấy random 2-3 giá trị cho mỗi thuộc tính để tạo variant
                $attributes[$slug] = $attr->values()->inRandomOrder()->limit(rand(2, 3))->get();
            }
        }

        // Generate Variants (Cartesian Product đơn giản hóa)
        // Để đơn giản, ta loop qua attribute đầu tiên và ghép ngẫu nhiên với attribute thứ 2 (nếu có)
        
        $primaryAttrSlug = $attributeSlugs[0];
        $primaryValues = $attributes[$primaryAttrSlug] ?? collect([]);

        foreach ($primaryValues as $val1) {
            $variantPrice = $basePrice + rand(100000, 2000000);
            $skuCode = Str::slug($product->name) . '-' . $val1->id . '-' . Str::random(4);
            
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => strtoupper($skuCode),
                'price' => $variantPrice,
                'weight' => rand(5000, 50000), // 5kg - 50kg
                'sold_count' => rand(0, 100),
                'image_url' => "https://placehold.co/600x400?text=" . urlencode($name . ' - ' . $val1->value)
            ]);

            // Attach Attributes
            $variant->attributeValues()->attach($val1->id);

            // Nếu có attribute thứ 2, lấy 1 giá trị random attach vào
            if (isset($attributeSlugs[1])) {
                $val2 = $attributes[$attributeSlugs[1]]->random();
                $variant->attributeValues()->attach($val2->id);
            }
        }
        
        // Cập nhật lại tổng sold count
        $product->update(['sold_count' => $product->variants()->sum('sold_count')]);
    }
}