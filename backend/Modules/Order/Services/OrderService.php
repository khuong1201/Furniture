<?php

namespace Modules\Order\Services;

use Modules\Shared\Services\BaseService;
use Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Domain\Repositories\InventoryRepositoryInterface;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Address\Domain\Repositories\AddressRepositoryInterface;
use Modules\Cart\Services\CartService; 
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Order\Events\OrderCreated;
use Modules\Order\Events\OrderCancelled;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class OrderService extends BaseService
{
    public function __construct(
        OrderRepositoryInterface $repository,
        protected InventoryService $inventoryService,
        protected InventoryRepositoryInterface $inventoryRepo,
        protected ProductRepositoryInterface $productRepo,
        protected AddressRepositoryInterface $addressRepo,
        protected CartService $cartService 
    ) {
        parent::__construct($repository);
    }

    public function createFromCart(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $userId = auth()->id();
            
            $cartData = $this->cartService->getMyCart($userId);
            
            if (empty($cartData['items'])) {
                throw ValidationException::withMessages(['cart' => 'Giỏ hàng đang trống.']);
            }

            $orderItems = [];
            foreach ($cartData['items'] as $item) {
                if (!$item['is_stock_sufficient']) {
                    throw ValidationException::withMessages([
                        'stock' => "Sản phẩm {$item['product']['name']} không đủ số lượng tồn kho."
                    ]);
                }

                $orderItems[] = [
                    'product_uuid' => $item['product']['uuid'],
                    'quantity' => $item['quantity']
                ];
            }

            $orderData = array_merge($data, ['items' => $orderItems]);
            $order = $this->create($orderData);

            $this->cartService->clearCart($userId);

            return $order;
        });
    }

    public function create(array $data): Model
    {
        $order = DB::transaction(function () use ($data) {
            $address = $this->addressRepo->findById($data['address_id']);
            if (!$address) throw ValidationException::withMessages(['address_id' => 'Address not found']);

            $order = $this->repository->create([
                'uuid' => (string) Str::uuid(),
                'user_id' => auth()->id(),
                'shipping_address_snapshot' => $address->toArray(),
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
                'total_amount' => 0
            ]);


            $totalAmount = $this->processOrderItems($order, $data['items']);

            $order->update(['total_amount' => $totalAmount]);

            return $order->load('items.product');
        });

        event(new OrderCreated($order));

        return $order;
    }

    protected function processOrderItems(Model $order, array $itemsData): float
    {
        $totalAmount = 0;

        foreach ($itemsData as $item) {
            $product = $this->productRepo->findByUuid($item['product_uuid']);
            $product->load(['promotions' => fn($q) => $q->active()]);

            $qty = (int) $item['quantity'];

            $inventory = \Modules\Inventory\Models\Inventory::where('product_id', $product->id)
                ->where('stock_quantity', '>=', $qty)
                ->where('status', '!=', 'out_of_stock')
                ->lockForUpdate() 
                ->first();

            if (!$inventory) {
                throw ValidationException::withMessages(['items' => "Sản phẩm {$product->name} đã hết hàng hoặc không đủ số lượng."]);
            }

            $this->inventoryService->adjustStock($product->id, $inventory->warehouse_id, -$qty);

            $pricing = $this->calculateItemPrice($product);
            
            $lineSubtotal = $pricing['unit_price'] * $qty;
            $totalAmount += $lineSubtotal;

            $order->items()->create([
                'product_id' => $product->id,
                'warehouse_id' => $inventory->warehouse_id,
                'quantity' => $qty,
                'original_price' => $pricing['original_price'],
                'discount_amount' => $pricing['discount_amount'],
                'unit_price' => $pricing['unit_price'],
                'subtotal' => $lineSubtotal
            ]);
        }

        return $totalAmount;
    }

    protected function calculateItemPrice($product): array
    {
        $originalPrice = $product->price;
        $discountAmount = 0;

        $bestPromotion = $product->promotions->sortByDesc(function ($promo) use ($originalPrice) {
            return ($promo->type === 'percentage')
                ? $originalPrice * ($promo->value / 100)
                : $promo->value;
        })->first();

        if ($bestPromotion) {
            $discountAmount = ($bestPromotion->type === 'percentage')
                ? $originalPrice * ($bestPromotion->value / 100)
                : $bestPromotion->value;
        }

        return [
            'original_price' => $originalPrice,
            'discount_amount' => $discountAmount,
            'unit_price' => max(0, $originalPrice - $discountAmount)
        ];
    }

    public function cancel(string $uuid): Model
    {
        $order = DB::transaction(function () use ($uuid) {
            $order = $this->repository->findByUuidOrFail($uuid);

            if ($order->status === 'cancelled') return $order;
            
            if (in_array($order->status, ['shipped', 'delivered'])) {
                throw ValidationException::withMessages(['status' => 'Cannot cancel shipped/delivered order']);
            }

            foreach ($order->items as $item) {
                $this->inventoryService->adjustStock(
                    $item->product_id,
                    $item->warehouse_id,
                    $item->quantity
                );
            }

            $order->update(['status' => 'cancelled']);
            return $order;
        });

        event(new OrderCancelled($order));

        return $order;
    }
}