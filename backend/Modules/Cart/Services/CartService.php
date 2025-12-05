<?php

declare(strict_types=1);

namespace Modules\Cart\Services;

use Modules\Shared\Services\BaseService;
use Modules\Cart\Domain\Repositories\CartRepositoryInterface;
use Modules\Cart\Domain\Models\Cart;
use Modules\Cart\Domain\Models\CartItem;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Inventory\Domain\Models\InventoryStock;
use Modules\Shared\Contracts\CartServiceInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartService extends BaseService implements CartServiceInterface
{
    public function __construct(CartRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
    
    // --- IMPLEMENTATION INTERFACE ---
    
    // Sửa lại method này để khớp với interface: clearCart(int $userId)
    public function clearCart(int|Cart $userOrCart): void
    {
        if (is_int($userOrCart)) {
            $cart = $this->repository->findByUser($userOrCart);
        } else {
            $cart = $userOrCart;
        }

        if ($cart) {
            $cart->items()->delete();
            // Reset voucher
            $cart->update(['voucher_code' => null, 'voucher_discount' => 0]);
        }
    }

    // Interface yêu cầu trả về array, method này đã đúng logic
    public function getMyCart(int $userId): array
    {
        $cart = $this->repository->findByUser($userId);
        
        if (!$cart) return ['items' => [], 'total_amount' => 0, 'item_count' => 0];

        $totalAmount = 0;
        $cartItems = [];

        foreach ($cart->items as $item) {
            $variant = $item->variant;
            
            if (!$variant || !$variant->product) {
                $item->delete(); 
                continue; 
            }
            
            $product = $variant->product;
            $originalPrice = $variant->price; 

            $discountAmount = 0; // Logic promo tạm thời = 0
            
            $finalPrice = max(0, $originalPrice - $discountAmount);
            $subtotal = $finalPrice * $item->quantity;
            $totalAmount += $subtotal;

            // Check tồn kho
            $totalStock = InventoryStock::where('product_variant_id', $variant->id)->sum('quantity');
            
            $cartItems[] = [
                'uuid' => $item->uuid,
                'product' => [
                    'name' => $product->name,
                    'sku' => $variant->sku,
                    'image' => $variant->image_url ?? $product->images->first()?->image_url,
                    'attributes' => $variant->attributeValues->map(fn($val) => [
                        'name' => $val->attribute->name,
                        'value' => $val->value
                    ])
                ],
                'quantity' => $item->quantity,
                'stock_available' => (int)$totalStock,
                'is_stock_sufficient' => $totalStock >= $item->quantity,
                'price' => [
                    'original' => (float)$originalPrice,
                    'final' => (float)$finalPrice,
                    'subtotal' => (float)$subtotal
                ]
            ];
        }

        $finalTotal = max(0, $totalAmount - $cart->voucher_discount);

        return [
            'uuid' => $cart->uuid,
            'items' => $cartItems,
            'total_amount' => $finalTotal,
            'voucher_discount' => $cart->voucher_discount,
            'voucher_code' => $cart->voucher_code,
            'item_count' => count($cartItems)
        ];
    }

    // --- END IMPLEMENTATION ---

    public function findCartItemOrFail(string $uuid): CartItem
    {
        $item = CartItem::where('uuid', $uuid)->first();
        if (!$item) {
            throw new ModelNotFoundException("Cart Item with UUID [{$uuid}] not found.");
        }
        return $item;
    }

    public function addToCart(int $userId, array $data)
    {
        return DB::transaction(function () use ($userId, $data) {
            $cart = $this->repository->firstOrCreateByUser($userId);
            
            $variant = ProductVariant::where('uuid', $data['variant_uuid'])->first();
            if (!$variant) throw ValidationException::withMessages(['variant_uuid' => 'Variant not found']);

            $totalStock = InventoryStock::where('product_variant_id', $variant->id)->sum('quantity');
            
            if ($totalStock < $data['quantity']) {
                throw ValidationException::withMessages(['quantity' => 'Not enough stock available']);
            }

            $cartItem = $cart->items()->where('product_variant_id', $variant->id)->first();

            if ($cartItem) {
                $newQty = $cartItem->quantity + $data['quantity'];
                if ($totalStock < $newQty) {
                     throw ValidationException::withMessages(['quantity' => 'Total quantity exceeds stock limit']);
                }
                $cartItem->update(['quantity' => $newQty]);
            } else {
                $cart->items()->create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $data['quantity'],
                    'price' => $variant->price
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
            $totalStock = InventoryStock::where('product_variant_id', $item->product_variant_id)->sum('quantity');
            
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
    
    public function getRepository(): CartRepositoryInterface
    {
        return $this->repository;
    }
}