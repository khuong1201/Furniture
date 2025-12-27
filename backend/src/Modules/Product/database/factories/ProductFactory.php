<?php

namespace Modules\Product\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Product\Domain\Models\Product;
use Modules\Category\Domain\Models\Category;
use Modules\Brand\Domain\Models\Brand;
use Modules\Product\Domain\Models\Attribute;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $adjectives = ['Modern', 'Classic', 'Luxury', 'Minimalist', 'Vintage', 'Rustic', 'Nordic', 'Industrial'];
        $nouns = ['Sofa', 'Table', 'Chair', 'Lamp', 'Cabinet', 'Bed', 'Rug', 'Mirror'];
        
        $name = $this->faker->randomElement($adjectives) . ' ' . $this->faker->randomElement($nouns) . ' ' . Str::random(3);
        $basePrice = $this->faker->numberBetween(100, 5000) * 10000;

        return [
            'uuid'         => (string) Str::uuid(),
            'name'         => $name,
            'slug'         => Str::slug($name) . '-' . Str::random(4),
            'description'  => "High quality {$name} featuring premium materials and durable construction.",
            'has_variants' => true,
            'is_active'    => true,
            'price'        => $basePrice,
            'sku'          => strtoupper(Str::slug($name) . '-' . Str::random(3)),
            'sold_count'   => 0,
            'rating_avg'   => $this->faker->randomFloat(1, 3.5, 5),
            'rating_count' => $this->faker->numberBetween(0, 100),
            // category_id và brand_id sẽ được truyền vào từ Seeder hoặc tạo mới nếu thiếu
            'category_id'  => Category::factory(), 
            'brand_id'     => Brand::factory(),
        ];
    }

    // State để cấu hình variant sau khi tạo Product
    public function withVariants(int $count = 3)
    {
        return $this->afterCreating(function (Product $product) use ($count) {
            $colorAttr = Attribute::where('slug', 'color')->first();
            if (!$colorAttr) return;

            // Lấy ngẫu nhiên các màu
            $colors = $colorAttr->values()->inRandomOrder()->take($count)->get();

            foreach ($colors as $index => $color) {
                $variantName = $product->name . " - " . $color->value;
                $variantSku = $product->sku . '-' . strtoupper(Str::slug($color->value)) . '-' . ($index + 1);

                // Tạo Variant
                $variant = $product->variants()->create([
                    'uuid'       => (string) Str::uuid(),
                    'sku'        => Str::limit($variantSku, 40, ''),
                    'name'       => $variantName,
                    'price'      => $product->price + (rand(0, 5) * 50000),
                    'sold_count' => 0,
                    'image_url'  => "https://placehold.co/600x400?text=" . urlencode($variantName),
                ]);

                // Gán Attribute cho Variant
                $variant->attributeValues()->attach($color->id);
            }

            // Tạo ảnh sản phẩm
            for ($j = 0; $j < 3; $j++) {
                $product->images()->create([
                    'uuid' => (string) Str::uuid(),
                    'image_url' => "https://placehold.co/600x400?text=" . urlencode($product->name . ' ' . $j),
                    'is_primary' => $j === 0,
                    'sort_order' => $j
                ]);
            }
        });
    }
}