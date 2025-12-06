<?php

declare(strict_types=1);

namespace Modules\Promotion\Domain\Traits;

use Modules\Promotion\Domain\Models\Promotion;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

trait HasPromotions
{
    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'product_promotion', 'product_id', 'promotion_id')
                    ->withTimestamps();
    }

    public function getFlashSaleInfoAttribute(): ?array
    {
        $promotions = $this->relationLoaded('promotions') 
            ? $this->promotions->filter(fn($p) => $p->isValid()) 
            : $this->promotions()->active()->get();

        if ($promotions->isEmpty()) return null;

        $originalPrice = (int) $this->price;
        $bestPrice = $originalPrice;
        $bestPromotion = null;

        foreach ($promotions as $promotion) {
            $discount = 0;
            if ($promotion->type === 'percentage') {
                $discount = (int) round(($originalPrice * $promotion->value) / 100);
                if ($promotion->max_discount_amount > 0) {
                    $discount = min($discount, $promotion->max_discount_amount);
                }
            } else {
                $discount = min($originalPrice, $promotion->value);
            }

            $currentSale = $originalPrice - $discount;
            if ($currentSale < $bestPrice) {
                $bestPrice = $currentSale;
                $bestPromotion = $promotion;
            }
        }

        if (!$bestPromotion) return null;

        return [
            'campaign_name'    => $bestPromotion->name,
            'original_price'   => $originalPrice,
            'sale_price'       => $bestPrice,
            'discount_amount'  => $originalPrice - $bestPrice,
            'discount_rate'    => ($originalPrice > 0) ? round((($originalPrice - $bestPrice) / $originalPrice) * 100) : 0,
            'end_date'         => $bestPromotion->end_date,
        ];
    }
}