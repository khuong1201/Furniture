<?php

namespace Modules\Promotion\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Promotion\Domain\Models\PromotionProduct;
use Illuminate\Support\Str;

class PromotionProductFactory extends Factory
{
    protected $model = PromotionProduct::class;

    public function definition()
    {
        return [
            'uuid' => Str::uuid(),
            'promotion_id' => 1,
            'product_id' => 1,
        ];
    }
}
