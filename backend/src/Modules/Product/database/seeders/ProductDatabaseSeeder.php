<?php

namespace Modules\Product\database\seeders;

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
        $this->createFurnitureAttributes();

        // Giá giờ đây là Integer (VND). Ví dụ: 12.000.000
        $this->generateMassiveProducts('sofas-armchairs', ['Sofa', 'Armchair'], 50, 5000000, 50000000);
        $this->generateMassiveProducts('coffee-tables', ['Coffee Table'], 30, 1000000, 8000000);
        $this->generateMassiveProducts('beds', ['Bed Frame'], 40, 3000000, 25000000);
        $this->generateMassiveProducts('dining-tables', ['Dining Table'], 30, 4000000, 15000000);
        $this->generateMassiveProducts('floor-lamps', ['Floor Lamp'], 30, 500000, 3000000);
    }

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
        ];
        foreach ($colors as $c) {
            AttributeValue::firstOrCreate(['attribute_id' => $color->id, 'value' => $c['value']], ['uuid' => Str::uuid(), 'code' => $c['code']]);
        }

        // Material
        $material = Attribute::firstOrCreate(['slug' => 'material'], ['uuid' => Str::uuid(), 'name' => 'Material', 'type' => 'select']);
        $materials = ['Italian Leather', 'Velvet', 'Oak Wood', 'Walnut', 'Marble', 'Metal', 'Linen'];
        foreach ($materials as $m) {
            AttributeValue::firstOrCreate(['attribute_id' => $material->id, 'value' => $m], ['uuid' => Str::uuid()]);
        }

        // Size
        $size = Attribute::firstOrCreate(['slug' => 'size'], ['uuid' => Str::uuid(), 'name' => 'Size', 'type' => 'select']);
        $sizes = ['King', 'Queen', '3-Seater', 'L-Shape', 'Standard', 'Large'];
        foreach ($sizes as $s) {
            AttributeValue::firstOrCreate(['attribute_id' => $size->id, 'value' => $s], ['uuid' => Str::uuid()]);
        }
    }

    protected function generateMassiveProducts(string $categorySlug, array $types, int $count, int $minPrice, int $maxPrice): void
    {
        $category = Category::where('slug', $categorySlug)->first();
        if (!$category) return;

        $adjectives = ['Modern', 'Luxury', 'Classic', 'Minimalist', 'Rustic', 'Vintage', 'Elegant'];

        for ($i = 0; $i < $count; $i++) {
            $name = $adjectives[array_rand($adjectives)] . ' ' . $types[array_rand($types)] . ' ' . rand(100, 999);
            $basePrice = rand($minPrice / 1000, $maxPrice / 1000) * 1000; // Random tròn nghìn

            // Chọn attribute ngẫu nhiên
            $attrs = ['color', 'material'];
            if (in_array($categorySlug, ['beds', 'dining-tables'])) $attrs[] = 'size';

            $this->createProductWithVariants($name, $category->id, $basePrice, $attrs);
        }
    }

    protected function createProductWithVariants(string $name, int $categoryId, int $basePrice, array $attributeSlugs): void
    {
        $product = Product::create([
            'uuid' => Str::uuid(),
            'name' => $name,
            'category_id' => $categoryId,
            'description' => "High quality furniture item: $name.",
            'has_variants' => true,
            'is_active' => true,
            'price' => $basePrice, // Giá gốc (Integer)
            'sku' => strtoupper(Str::slug($name)),
            'rating_avg' => rand(35, 50) / 10,
            'rating_count' => rand(5, 200),
            'sold_count' => 0
        ]);

        $this->generateProductImages($product);

        $attributes = [];
        foreach ($attributeSlugs as $slug) {
            $attr = Attribute::where('slug', $slug)->first();
            if ($attr) {
                $attributes[$slug] = $attr->values()->inRandomOrder()->limit(rand(2, 3))->get();
            }
        }

        if (empty($attributes)) return;

        $primaryValues = $attributes[$attributeSlugs[0]] ?? collect([]);
        $minPrice = null;
        $representativeSku = null;

        foreach ($primaryValues as $index => $val1) {
            // Giá biến thể: Base + chênh lệch (Integer)
            // Ví dụ: Base 5tr + rand(0, 5) * 100k => 5tr, 5tr1, 5tr5...
            $variantPrice = $basePrice + (rand(0, 10) * 100000);
            
            if (is_null($minPrice) || $variantPrice < $minPrice) {
                $minPrice = $variantPrice;
            }

            $skuCode = strtoupper(Str::slug($product->name) . '-' . substr($val1->value, 0, 3) . '-' . rand(10, 99));
            if ($index === 0) $representativeSku = $skuCode;

            $variant = ProductVariant::create([
                'uuid' => Str::uuid(),
                'product_id' => $product->id,
                'sku' => $skuCode,
                'price' => $variantPrice, // INTEGER
                'weight' => rand(5000, 50000),
                'sold_count' => rand(0, 50),
                'image_url' => "https://placehold.co/600x400?text=" . urlencode($name),
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
        for ($i = 0; $i < 3; $i++) {
            ProductImage::create([
                'uuid' => Str::uuid(),
                'product_id' => $product->id,
                'image_url' => "https://placehold.co/800x600?text=" . urlencode($product->name . ' ' . $i),
                'public_id' => null,
                'is_primary' => $i === 0,
                'sort_order' => $i
            ]);
        }
    }
}