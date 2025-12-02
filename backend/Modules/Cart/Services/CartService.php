<?php

namespace Modules\Cart\Services;

use Modules\Shared\Services\BaseService;
use Modules\Cart\Domain\Repositories\CartRepositoryInterface;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Inventory\Domain\Models\Inventory;
use Modules\Cart\Domain\Models\Cart;
use Modules\Cart\Domain\Models\CartItem;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class CartService extends BaseService
{
    public function __construct(
        CartRepositoryInterface $repository,
        protected ProductRepositoryInterface $productRepo
    ) {
        parent::__construct($repository);
    }
    
    public function getMyCart(int $userId)
    {
        $cart = $this->repository->findByUser($userId);
        
        if (!$cart) return ['items' => [], 'total' => 0];

        $totalAmount = 0;
        $cartItems = [];

        foreach ($cart->items as $item) {
            $product = $item->product;
            if (!$product) continue;
            
            $originalPrice = $product->price;
            $discountAmount = 0;
            
            $activePromotions = $product->promotions->filter(function ($p) {
                return $p->status && $p->start_date <= now() && $p->end_date >= now();
            });

            $bestPromotion = $activePromotions->sortByDesc(function ($promo) use ($originalPrice) {
                return ($promo->type === 'percentage') 
                    ? $originalPrice * ($promo->value / 100) 
                    : $promo->value;
            })->first();

            if ($bestPromotion) {
                $discountAmount = ($bestPromotion->type === 'percentage') 
                    ? $originalPrice * ($bestPromotion->value / 100) 
                    : $bestPromotion->value;
            }

            $finalPrice = max(0, $originalPrice - $discountAmount);
            $subtotal = $finalPrice * $item->quantity;
            $totalAmount += $subtotal;

            $totalStock = Inventory::where('product_id', $product->id)->sum('stock_quantity');
            
            $cartItems[] = [
                'uuid' => $item->uuid,
                'product' => [
                    'uuid' => $product->uuid,
                    'name' => $product->name,
                    'image' => $product->images->first()?->image_url,
                    'sku' => $product->sku
                ],
                'quantity' => $item->quantity,
                'stock_available' => $totalStock,
                'is_stock_sufficient' => $totalStock >= $item->quantity,
                'price' => [
                    'original' => $originalPrice,
                    'discount' => $discountAmount,
                    'final' => $finalPrice,
                    'subtotal' => $subtotal
                ]
            ];
        }

        return [
            'uuid' => $cart->uuid,
            'items' => $cartItems,
            'total_amount' => $totalAmount,
            'item_count' => count($cartItems)
        ];
    }

    public function addToCart(int $userId, array $data)
    {
        return DB::transaction(function () use ($userId, $data) {
            $cart = $this->repository->firstOrCreateByUser($userId);
            
            $product = $this->productRepo->findByUuid($data['product_uuid']);
            if (!$product) throw ValidationException::withMessages(['product_uuid' => 'Product not found']);

            $totalStock = Inventory::where('product_id', $product->id)->sum('stock_quantity');
            if ($totalStock < $data['quantity']) {
                throw ValidationException::withMessages(['quantity' => 'Not enough stock available']);
            }

            $cartItem = $cart->items()->where('product_id', $product->id)->first();

            if ($cartItem) {
                $newQty = $cartItem->quantity + $data['quantity'];
                if ($totalStock < $newQty) {
                     throw ValidationException::withMessages(['quantity' => 'Total quantity exceeds stock limit']);
                }
                $cartItem->update(['quantity' => $newQty]);
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $data['quantity']
                ]);
            }

            return $this->getMyCart($userId);
        });
    }

    public function updateItem(CartItem $item, int $quantity, int $userId)
    {
        if ($quantity <= 0) {
            $item->delete();
        } else {
            $totalStock = Inventory::where('product_id', $item->product_id)->sum('stock_quantity');
            if ($totalStock < $quantity) {
                throw ValidationException::withMessages(['quantity' => 'Not enough stock']);
            }
            $item->update(['quantity' => $quantity]);
        }

        return $this->getMyCart($userId);
    }

    public function removeItem(CartItem $item, int $userId)
    {
        $item->delete();
        return $this->getMyCart($userId);
    }

    public function clearCart(Cart $cart)
    {
        $cart->items()->delete();
        return true;
    }
}