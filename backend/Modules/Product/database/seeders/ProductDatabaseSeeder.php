<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Domain\Models\Attribute;
use Modules\Product\Domain\Models\AttributeValue;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Category\Domain\Models\Category;
use Modules\Product\Domain\Models\ProductImage;
use Illuminate\Support\Str;

class ProductDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Attributes
        $this->createFurnitureAttributes();

        // 2. Generate Massive Products
        $this->generateMassiveProducts('sofas-armchairs', ['Sofa', 'Armchair', 'Couch', 'Recliner'], 50);
        $this->generateMassiveProducts('coffee-tables', ['Coffee Table', 'Side Table', 'Tea Table'], 30);
        $this->generateMassiveProducts('beds', ['Bed Frame', 'Luxury Bed', 'Platform Bed'], 40);
        $this->generateMassiveProducts('dining-tables', ['Dining Table', 'Breakfast Table'], 30);
        $this->generateMassiveProducts('floor-lamps', ['Floor Lamp', 'Standing Lamp', 'Reading Light'], 30);
    }

    // ... (Phần createFurnitureAttributes giữ nguyên như cũ) ...
    protected function createFurnitureAttributes(): void
    {
        // Color
        $color = Attribute::firstOrCreate(['slug' => 'color'], ['name' => 'Color', 'type' => 'color', 'uuid' => Str::uuid()]);
        $colors = [
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
        ];
        foreach ($colors as $c) AttributeValue::firstOrCreate(['attribute_id' => $color->id, 'value' => $c['value']], ['uuid' => Str::uuid(), 'code' => $c['code']]);

        // Material
        $material = Attribute::firstOrCreate(['slug' => 'material'], ['uuid' => Str::uuid(), 'name' => 'Material', 'type' => 'select']);
        $materials = ['Italian Leather', 'Premium Velvet', 'Solid Oak', 'Walnut Wood', 'Marble', 'Powder Coated Metal', 'Linen Fabric', 'Rattan'];
        foreach ($materials as $m) AttributeValue::firstOrCreate(['attribute_id' => $material->id, 'value' => $m], ['uuid' => Str::uuid()]);

        // Size
        $size = Attribute::firstOrCreate(['slug' => 'size'], ['uuid' => Str::uuid(), 'name' => 'Size', 'type' => 'select']);
        $sizes = ['King Size', 'Queen Size', '3-Seater (2.2m)', 'L-Shape (2.8m x 1.8m)', 'Standard (1.6m)', 'Large (2.0m)', 'Compact', 'Single'];
        foreach ($sizes as $s) AttributeValue::firstOrCreate(['attribute_id' => $size->id, 'value' => $s], ['uuid' => Str::uuid()]);
    }

    /**
     * Hàm sinh sản phẩm số lượng lớn với tên ngẫu nhiên
     */
    protected function generateMassiveProducts(string $categorySlug, array $productTypes, int $count): void
    {
        $category = Category::where('slug', $categorySlug)->first();
        if (!$category) return;

        $adjectives = ['Modern', 'Luxury', 'Vintage', 'Minimalist', 'Rustic', 'Industrial', 'Classic', 'Scandanavian', 'Boho', 'Elegant'];
        $features = ['with Storage', 'Convertible', 'Low Profile', 'Tufted', 'Modular', 'Handcrafted', 'Eco-friendly'];

        for ($i = 0; $i < $count; $i++) {
            // Tạo tên ngẫu nhiên: "Modern Luxury Sofa with Storage"
            $name = $adjectives[array_rand($adjectives)] . ' ' . 
                    $productTypes[array_rand($productTypes)] . ' ' . 
                    (rand(0, 1) ? $features[array_rand($features)] : ''); // 50% có thêm feature
            
            // Tránh trùng tên
            $name .= ' ' . rand(100, 999); 

            $basePrice = rand(100, 2000); // Giá từ $100 - $2000

            // Chọn attribute ngẫu nhiên cho từng loại sản phẩm
            $attributeSets = [
                'sofas-armchairs' => ['color', 'material'],
                'coffee-tables' => ['material', 'size'],
                'beds' => ['size', 'material'],
                'dining-tables' => ['material', 'size'],
                'floor-lamps' => ['color']
            ];
            
            $attrs = $attributeSets[$categorySlug] ?? ['color'];

            $this->createProductWithVariants($name, $category->id, $basePrice, $attrs);
        }
    }

    // ... (Hàm createProductWithVariants và generateProductImages giữ nguyên như bản trước) ...
    // ... Bạn copy lại đoạn code createProductWithVariants từ câu trả lời trước vào đây ...
    // ... (Tôi sẽ paste lại đầy đủ bên dưới để bạn tiện copy 1 lần) ...

    protected function createProductWithVariants(string $name, int $categoryId, float $basePrice, array $attributeSlugs): void
    {
        $product = Product::create([
            'uuid' => Str::uuid(),
            'name' => $name,
            'category_id' => $categoryId,
            'description' => "Experience luxury with the $name. Premium materials. High durability. Designed for modern living.",
            'has_variants' => true,
            'is_active' => true,
            'price' => $basePrice, 
            'sku' => strtoupper(Str::slug($name)), 
            'rating_avg' => rand(35, 50) / 10, // 3.5 - 5.0
            'rating_count' => rand(10, 500),
            'sold_count' => 0
        ]);

        $this->generateProductImages($product);

        $attributes = [];
        foreach ($attributeSlugs as $slug) {
            $attr = Attribute::where('slug', $slug)->first();
            if ($attr) {
                // Random 2-4 options per attribute to create variants
                $attributes[$slug] = $attr->values()->inRandomOrder()->limit(rand(2, 4))->get();
            }
        }

        if (empty($attributes)) return;

        $primaryAttrSlug = $attributeSlugs[0];
        $primaryValues = $attributes[$primaryAttrSlug] ?? collect([]);

        $minPrice = null; 
        $representativeSku = null; 

        foreach ($primaryValues as $index => $val1) {
            $variantPrice = $basePrice + (rand(0, 50) * 10); 
            
            if (is_null($minPrice) || $variantPrice < $minPrice) {
                $minPrice = $variantPrice;
            }

            $skuCode = strtoupper(Str::slug($product->name) . '-' . $val1->value . '-' . Str::random(3));
            
            if ($index === 0) {
                $representativeSku = $skuCode;
            }

            $variant = ProductVariant::create([
                'uuid' => Str::uuid(),
                'product_id' => $product->id,
                'sku' => $skuCode,
                'price' => $variantPrice,
                'weight' => rand(5000, 50000),
                'sold_count' => rand(0, 100),
                'image_url' => "https://placehold.co/600x400?text=" . urlencode($name . ' - ' . $val1->value),
            ]);

            $variant->attributeValues()->attach($val1->id);

            if (isset($attributeSlugs[1]) && isset($attributes[$attributeSlugs[1]])) {
                $val2 = $attributes[$attributeSlugs[1]]->random();
                $variant->attributeValues()->attach($val2->id);
            }
        }

        $product->update([
            'sold_count' => $product->variants()->sum('sold_count'),
            'price' => $minPrice ?? $basePrice, 
            'sku' => $representativeSku ?? $product->sku 
        ]);
    }

    protected function generateProductImages(Product $product): void
    {
        $imageCount = rand(2, 4);
        for ($i = 0; $i < $imageCount; $i++) {
            ProductImage::create([
                'uuid' => Str::uuid(),
                'product_id' => $product->id,
                'image_url' => "https://placehold.co/800x600?text=" . urlencode($product->name . ' View ' . ($i + 1)),
                'public_id' => null,
                'is_primary' => $i === 0,
                'sort_order' => $i
            ]);
        }
    }
}