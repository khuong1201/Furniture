<?php

declare(strict_types=1);

namespace Modules\Cart\Services;

use Illuminate\Support\Facades\DB;
use Modules\Cart\Domain\Models\Cart;
use Modules\Cart\Domain\Models\CartItem;
use Modules\Cart\Domain\Repositories\CartRepositoryInterface;
use Modules\Currency\Services\CurrencyService;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Shared\Contracts\CartServiceInterface;
use Modules\Shared\Contracts\InventoryServiceInterface;
use Modules\Shared\Contracts\VoucherServiceInterface;
use Modules\Shared\Exceptions\BusinessException;
use Modules\Shared\Services\BaseService;

class CartService extends BaseService implements CartServiceInterface
{
    public function __construct(
        CartRepositoryInterface $repository,
        protected CurrencyService $currencyService,
        protected InventoryServiceInterface $inventoryService,
        protected VoucherServiceInterface $voucherService
    ) {
        parent::__construct($repository);
    }

    public function getMyCart(string|int $userId): array
    {
        $cart = $this->repository->findByUser($userId);

        if (!$cart) {
            return $this->formatCartResponse(null, [], 0);
        }
        $cart->load([
            'items.variant.product.images',
            'items.variant.attributeValues.attribute'
        ]);

        $totalAmount = 0;
        $formattedItems = [];

        foreach ($cart->items as $item) {
            $variant = $item->variant;

            if (!$variant || !$variant->product || !$variant->product->is_active) {
                $item->delete();
                continue;
            }

            $stockAvailable = $this->inventoryService->getTotalStock($variant->id);

            $price = (int) $variant->price;
            $subtotal = $price * $item->quantity;
            $totalAmount += $subtotal;

            $formattedItems[] = $this->formatCartItem($item, $variant, $price, $subtotal, $stockAvailable);
        }

        $discount = 0;
        if ($cart->voucher_code) {
            try {
                $voucherResult = $this->voucherService->check($cart->voucher_code, $userId, (float)$totalAmount);
                $discount = $voucherResult['discount_amount'];

                if ($discount != $cart->voucher_discount) {
                    $cart->update(['voucher_discount' => $discount]);
                }
            } catch (\Exception $e) {
                $cart->update(['voucher_code' => null, 'voucher_discount' => 0]);
                $discount = 0;
            }
        }

        $finalTotal = max(0, $totalAmount - $discount);

        return $this->formatCartResponse($cart, $formattedItems, $finalTotal);
    }

    public function addToCart(string|int $userId, array $data): array
    {
        return DB::transaction(function () use ($userId, $data) {
            $cart = $this->repository->firstOrCreateByUser($userId);

            $variant = ProductVariant::where('uuid', $data['variant_uuid'])->first();

            if (!$variant) {
                throw new BusinessException(422043);
            }

            $currentStock = $this->inventoryService->getTotalStock($variant->id);
            $requestedQty = $data['quantity'];

            $cartItem = $cart->items()->where('product_variant_id', $variant->id)->first();
            
            if ($cartItem) {
                $requestedQty += $cartItem->quantity;
            }

            if ($requestedQty > $currentStock) {
                throw new BusinessException(409042, "Sản phẩm chỉ còn {$currentStock} items.");
            }

            if ($cartItem) {
                $cartItem->update([
                    'quantity' => $requestedQty,
                    'price'    => $variant->price
                ]);
            } else {
                $cart->items()->create([
                    'product_variant_id' => $variant->id,
                    'quantity'           => $data['quantity'],
                    'price'              => $variant->price
                ]);
            }

            if ($cart->voucher_code) {
                try {
                    $this->applyVoucher($userId, $cart->voucher_code);
                } catch (\Exception $e) {
                    // Ignore voucher error on add
                }
            }

            return $this->getMyCart($userId);
        });
    }

    public function updateItem(string $itemUuid, int $quantity, string|int $userId): array
    {
        $cartItem = $this->findCartItemOrFail($itemUuid);

        if ((string)$cartItem->cart->user_id !== (string)$userId) {
            throw new BusinessException(404041);
        }

        if ($quantity <= 0) {
            $cartItem->delete();
        } else {
            $totalStock = $this->inventoryService->getTotalStock($cartItem->product_variant_id);
            
            if ($quantity > $totalStock) {
                throw new BusinessException(409042, "Số lượng yêu cầu vượt quá tồn kho ({$totalStock}).");
            }
            
            $cartItem->update(['quantity' => $quantity]);
        }

        $cart = $cartItem->cart;
        if ($cart->voucher_code) {
            try {
                $this->applyVoucher($userId, $cart->voucher_code);
            } catch (BusinessException $e) {
                $this->removeVoucher($userId);
            }
        }

        return $this->getMyCart($userId);
    }

    public function removeItem(string $itemUuid, string|int $userId): array
    {
        $cartItem = $this->findCartItemOrFail($itemUuid);
        
        if ((string)$cartItem->cart->user_id === (string)$userId) {
            $cart = $cartItem->cart;
            $cartItem->delete();

            if ($cart->items()->count() === 0) {
                $this->clearCart($userId);
            } else {
                if ($cart->voucher_code) {
                    try {
                        $this->applyVoucher($userId, $cart->voucher_code);
                    } catch (BusinessException $e) {
                        $this->removeVoucher($userId);
                    }
                }
            }
        }

        return $this->getMyCart($userId);
    }

    public function removeItemsList(string|int $userId, array $itemUuids): void
    {
        $cart = $this->repository->findByUser($userId);
        if ($cart) {
            $cart->items()->whereIn('uuid', $itemUuids)->delete();
            
            if ($cart->items()->count() === 0) {
                $this->clearCart($userId);
            } else {
                 if ($cart->voucher_code) {
                    try {
                        $this->applyVoucher($userId, $cart->voucher_code);
                    } catch (BusinessException $e) {
                        $this->removeVoucher($userId);
                    }
                }
            }
        }
    }

    public function clearCart(string|int $userId): void
    {
        $cart = $this->repository->findByUser($userId);

        if ($cart) {
            $cart->items()->delete();
            $cart->update(['voucher_code' => null, 'voucher_discount' => 0]);
        }
    }

    public function applyVoucher(string|int $userId, string $code): array
    {
        $cart = $this->repository->firstOrCreateByUser($userId);

        if ($cart->items->isEmpty()) {
            throw new BusinessException(400044); 
        }

        // Eager load items để tính tổng nhanh hơn
        $cart->load('items.variant');
        
        $cartTotal = 0;
        foreach ($cart->items as $item) {
            $cartTotal += ($item->variant->price * $item->quantity);
        }

        $result = $this->voucherService->check($code, $userId, (float)$cartTotal);

        $cart->update([
            'voucher_code' => $result['code'],
            'voucher_discount' => $result['discount_amount']
        ]);

        return $this->getMyCart($userId);
    }

    public function removeVoucher(string|int $userId): array
    {
        $cart = $this->repository->findByUser($userId);
        if ($cart) {
            $cart->update([
                'voucher_code' => null, 
                'voucher_discount' => 0
            ]);
        }
        return $this->getMyCart($userId);
    }

    public function findCartItemOrFail(string $uuid): CartItem
    {
        $item = CartItem::with('cart')->where('uuid', $uuid)->first();
        if (!$item) {
            throw new BusinessException(404041);
        }
        return $item;
    }

    private function formatCartItem(CartItem $item, ProductVariant $variant, int $price, int $subtotal, int $stock): array
    {
        $currency = $this->currencyService;
        $product = $variant->product;

        $image = $variant->image_url ?? $product->images->where('is_primary', true)->first()?->image_url;

        $options = $variant->attributeValues->map(function($val) {
            return ($val->attribute->name ?? '') . ': ' . $val->value;
        })->implode(', ');

        return [
            'uuid'         => $item->uuid,
            'variant_uuid' => $variant->uuid,
            'product_name' => $product->name,
            'sku'          => $variant->sku,
            'image'        => $image,
            'options'      => $options,
            'quantity'     => $item->quantity,
            'stock_available' => $stock,
            'is_stock_sufficient' => $stock >= $item->quantity,
            'price' => [
                'raw'       => $currency->convert($price),
                'formatted' => $currency->format($price),
            ],
            'subtotal' => [
                'raw'       => $currency->convert($subtotal),
                'formatted' => $currency->format($subtotal),
            ]
        ];
    }

    private function formatCartResponse(?Cart $cart, array $items, int $totalAmount): array
    {
        $currency = $this->currencyService;
        
        $discount = $cart ? (int)$cart->voucher_discount : 0;

        return [
            'cart_uuid' => $cart?->uuid,
            'items'     => $items,
            'summary'   => [
                'item_count' => count($items),
                'currency'   => $currency->getCurrentCurrency()->code,
                'discount'   => [
                    'code'      => $cart?->voucher_code,
                    'amount'    => $currency->convert($discount),
                    'formatted' => $currency->format($discount),
                ],
                'total' => [
                    'raw'       => $currency->convert($totalAmount),
                    'formatted' => $currency->format($totalAmount),
                ]
            ]
        ];
    }
}