<?php

declare(strict_types=1);

namespace Modules\Cart\Services;

use Modules\Shared\Services\BaseService;
use Modules\Cart\Domain\Repositories\CartRepositoryInterface;
use Modules\Cart\Domain\Models\Cart;
use Modules\Cart\Domain\Models\CartItem;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Shared\Contracts\CartServiceInterface;
use Modules\Shared\Contracts\InventoryServiceInterface;
use Modules\Currency\Services\CurrencyService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartService extends BaseService implements CartServiceInterface
{
    public function __construct(
        CartRepositoryInterface $repository,
        protected CurrencyService $currencyService,
        protected InventoryServiceInterface $inventoryService
    ) {
        parent::__construct($repository);
    }

    // --- IMPLEMENTATION INTERFACE ---

    public function clearCart(int|Cart $userOrCart): void
    {
        $cart = is_int($userOrCart) ? $this->repository->findByUser($userOrCart) : $userOrCart;

        if ($cart) {
            $cart->items()->delete();
            $cart->update(['voucher_code' => null, 'voucher_discount' => 0]);
        }
    }

    public function getMyCart(int $userId): array
    {
        $cart = $this->repository->findByUser($userId);
        
        if (!$cart) {
            return $this->formatCartResponse(null, [], 0, 0);
        }

        $totalAmountVND = 0;
        $cartItems = [];

        foreach ($cart->items as $item) {
            $variant = $item->variant;
            
            // Xóa item lỗi nếu variant/product không tồn tại
            if (!$variant || !$variant->product) {
                $item->delete();
                continue; 
            }
            
            $product = $variant->product;
            $originalPriceVND = (float)$variant->price; 

            // TODO: Tích hợp PromotionService để tính discount thực tế
            $discountAmount = 0; 

            $finalPriceVND = max(0, $originalPriceVND - $discountAmount);
            $subtotalVND = $finalPriceVND * $item->quantity;
            $totalAmountVND += $subtotalVND;

            // Lấy tồn kho qua Interface
            $totalStock = $this->inventoryService->getTotalStock($variant->id);
            
            $cartItems[] = $this->formatCartItem(
                $item, $product, $variant, 
                $originalPriceVND, $finalPriceVND, $subtotalVND, $totalStock
            );
        }

        $finalTotalVND = max(0, $totalAmountVND - $cart->voucher_discount);

        return $this->formatCartResponse($cart, $cartItems, $totalAmountVND, $finalTotalVND);
    }

    // --- CORE LOGIC ---

    public function addToCart(int $userId, array $data)
    {
        return DB::transaction(function () use ($userId, $data) {
            $cart = $this->repository->firstOrCreateByUser($userId);
            
            $variant = ProductVariant::where('uuid', $data['variant_uuid'])->first();
            if (!$variant) {
                throw ValidationException::withMessages(['variant_uuid' => 'Variant not found']);
            }

            $totalStock = $this->inventoryService->getTotalStock($variant->id);
            
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
            $totalStock = $this->inventoryService->getTotalStock($item->product_variant_id);
            
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
    
    public function findCartItemOrFail(string $uuid): CartItem
    {
        $item = CartItem::where('uuid', $uuid)->first();
        if (!$item) {
            throw new ModelNotFoundException("Cart Item with UUID [{$uuid}] not found.");
        }
        return $item;
    }

    public function getRepository(): CartRepositoryInterface
    {
        return $this->repository;
    }

    // --- HELPER METHODS (Private) ---

    private function formatCartItem($item, $product, $variant, $originalPrice, $finalPrice, $subtotal, $stock): array
    {
        return [
            'uuid' => $item->uuid,
            'product' => [
                'name' => $product->name,
                'sku' => $variant->sku,
                'image' => $variant->image_url ?? $product->images->first()?->image_url,
                'attributes' => $variant->attributeValues->map(fn($val) => [
                    'name' => $val->attribute->name ?? 'Unknown',
                    'value' => $val->value
                ])
            ],
            'quantity' => $item->quantity,
            'stock_available' => (int)$stock,
            'is_stock_sufficient' => $stock >= $item->quantity,
            'price' => [
                'original' => $this->currencyService->convert($originalPrice),
                'original_formatted' => $this->currencyService->format($originalPrice),
                'final' => $this->currencyService->convert($finalPrice),
                'final_formatted' => $this->currencyService->format($finalPrice),
                'subtotal' => $this->currencyService->convert($subtotal),
                'subtotal_formatted' => $this->currencyService->format($subtotal),
            ]
        ];
    }

    private function formatCartResponse($cart, $items, $subtotalVND, $finalTotalVND): array
    {
        $currencyCode = $this->currencyService->getCurrentCurrency()->code;
        
        return [
            'uuid' => $cart?->uuid,
            'items' => $items,
            
            // Raw VND
            'total_raw_vnd' => $finalTotalVND,
            
            // Display Values
            'subtotal' => $this->currencyService->convert($subtotalVND),
            'subtotal_formatted' => $this->currencyService->format($subtotalVND),
            
            'voucher_discount' => $this->currencyService->convert($cart->voucher_discount ?? 0),
            'voucher_discount_formatted' => $this->currencyService->format($cart->voucher_discount ?? 0),
            'voucher_code' => $cart?->voucher_code,
            
            'total_amount' => $this->currencyService->convert($finalTotalVND),
            'total_formatted' => $this->currencyService->format($finalTotalVND),
            
            'currency' => $currencyCode,
            'item_count' => count($items)
        ];
    }
}