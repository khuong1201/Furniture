<?php

namespace Modules\Promotion\Tests\Unit;

use Tests\TestCase;
use Modules\Promotion\Services\PromotionService;
use Modules\Promotion\Domain\Models\Promotion;
use Mockery;

class PromotionLogicTest extends TestCase
{
    public function test_calculate_percentage_discount()
    {
        $service = new PromotionService(Mockery::mock(\Modules\Promotion\Repositories\PromotionRepositoryInterface::class));
        
        $promotion = new Promotion(['type' => 'percentage', 'value' => 10]); 
        $price = 100000;

        $discount = $service->calculateDiscount($price, $promotion);

        $this->assertEquals(10000, $discount);
    }

    public function test_calculate_fixed_discount()
    {
        $service = new PromotionService(Mockery::mock(\Modules\Promotion\Repositories\PromotionRepositoryInterface::class));
        
        $promotion = new Promotion(['type' => 'fixed', 'value' => 15000]); 
        $price = 100000;

        $discount = $service->calculateDiscount($price, $promotion);

        $this->assertEquals(15000, $discount);
    }
    
    public function test_fixed_discount_cannot_exceed_price()
    {
        $service = new PromotionService(Mockery::mock(\Modules\Promotion\Repositories\PromotionRepositoryInterface::class));
        
        $promotion = new Promotion(['type' => 'fixed', 'value' => 200000]); 
        $price = 100000; // Giá gốc 100k

        $discount = $service->calculateDiscount($price, $promotion);

        $this->assertEquals(100000, $discount); 
    }
}