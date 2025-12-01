<?php

namespace Modules\Order\Services;

use Modules\Shared\Services\BaseService;
use Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Domain\Repositories\InventoryRepositoryInterface;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Address\Domain\Repositories\AddressRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Order\Events\OrderCreated;
use Modules\Order\Events\OrderCancelled;
use Illuminate\Database\Eloquent\Model;

class OrderService extends BaseService
{
    public function __construct(
        OrderRepositoryInterface $repository,
        protected InventoryService $inventoryService,
        protected InventoryRepositoryInterface $inventoryRepo,
        protected ProductRepositoryInterface $productRepo,
        protected AddressRepositoryInterface $addressRepo
    ) {
        parent::__construct($repository);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
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

            $totalAmount = 0;

            foreach ($data['items'] as $item) {
                $product = \Modules\Product\Models\Product::with(['promotions' => function($q) {
                    $q->active(); 
                }])->where('uuid', $item['product_uuid'])->firstOrFail();
                
                $qty = (int) $item['quantity'];

                $inventory = \Modules\Inventory\Models\Inventory::where('product_id', $product->id)
                    ->where('stock_quantity', '>=', $qty)
                    ->where('status', '!=', 'out_of_stock')
                    ->first();

                if (!$inventory) throw ValidationException::withMessages(['items' => "{$product->name} out of stock"]);
                
                $this->inventoryService->adjustStock($product->id, $inventory->warehouse_id, -$qty);

                $originalPrice = $product->price;
                $discountAmount = 0;
                
                $bestPromotion = $product->promotions->sortByDesc(function ($promo) use ($originalPrice) {
                    if ($promo->type === 'percentage') {
                        return $originalPrice * ($promo->value / 100);
                    }
                    return $promo->value;
                })->first();

                if ($bestPromotion) {
                    if ($bestPromotion->type === 'percentage') {
                        $discountAmount = $originalPrice * ($bestPromotion->value / 100);
                    } else {
                        $discountAmount = $bestPromotion->value;
                    }
                }

                $finalUnitPrice = max(0, $originalPrice - $discountAmount);
                $lineSubtotal = $finalUnitPrice * $qty;
                
                $totalAmount += $lineSubtotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'warehouse_id' => $inventory->warehouse_id,
                    'quantity' => $qty,
                    
                    'original_price' => $originalPrice, 
                    'discount_amount' => $discountAmount, 
                    'unit_price' => $finalUnitPrice, 
                    
                    'subtotal' => $lineSubtotal
                ]);
            }

            $order->update(['total_amount' => $totalAmount]);

            return $order->load('items.product');
        });

        event(new OrderCreated($order));

        return $order;
    }

    public function cancel(string $uuid)
    {
        $order = DB::transaction(function () use ($uuid) {
            $order = $this->repository->findByUuidOrFail($uuid);

            if ($order->status === 'cancelled') return $order;
            if (in_array($order->status, ['shipped', 'delivered'])) {
                throw ValidationException::withMessages(['status' => 'Cannot cancel shipped/delivered order']);
            }
            if ($order->payment_status === 'paid') {
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