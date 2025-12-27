<?php

declare(strict_types=1);

namespace Modules\Order\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Address\Domain\Repositories\AddressRepositoryInterface;
use Modules\Cart\Services\CartService; 
use Modules\Order\Domain\Models\Order;
use Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Enums\PaymentStatus;
use Modules\Order\Events\OrderCancelled;
use Modules\Order\Events\OrderCreated;
use Modules\Order\Events\OrderStatusUpdated;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Shared\Contracts\CartServiceInterface;
use Modules\Shared\Contracts\InventoryServiceInterface;
use Modules\Shared\Exceptions\BusinessException;
use Modules\Shared\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderService extends BaseService
{
    public function __construct(
        OrderRepositoryInterface $repository,
        protected InventoryServiceInterface $inventoryService,
        protected AddressRepositoryInterface $addressRepo,
        protected CartServiceInterface $cartService
    ) {
        parent::__construct($repository);
    }

    public function createFromCart(array $data): Model
    {
        $userId = auth()->id();
        $cartData = $this->cartService->getMyCart($userId); 
        
        if (empty($cartData['items'])) {
            throw new BusinessException(400044, 'Your cart is empty.');
        }

        $selectedIds = $data['selected_item_uuids'] ?? [];
        $hasSelection = !empty($selectedIds);
        $checkoutItems = [];
        $purchasedItemUuids = [];

        foreach ($cartData['items'] as $item) {
            if ($hasSelection && !in_array($item['uuid'], $selectedIds)) {
                continue;
            }
            
            $checkoutItems[] = [
                'variant_uuid' => $item['variant_uuid'],
                'quantity'     => (int) $item['quantity']
            ];
            
            $purchasedItemUuids[] = $item['uuid'];
        }

        if (empty($checkoutItems)) {
            throw new BusinessException(422131, 'Please select at least one item to checkout.');
        }

        $orderData = array_merge($data, ['items' => $checkoutItems]);

        // Áp dụng voucher từ Cart nếu có
        if (!empty($cartData['summary']['discount']['code'])) {
            $orderData['voucher_code'] = $cartData['summary']['discount']['code'];
            $orderData['voucher_discount'] = $cartData['summary']['discount']['amount'] ?? 0;
        }

        $order = $this->create($orderData);

        // Xóa cart sau khi order thành công
        if ($this->cartService instanceof CartService) {
            $this->cartService->removeItemsList($userId, $purchasedItemUuids);
        } else {
            $this->cartService->clearCart($userId);
        }

        return $order;
    }

    public function createBuyNow(array $data): Model
    {
        $orderItems = [
            [
                'variant_uuid' => $data['variant_uuid'],
                'quantity'     => (int) $data['quantity']
            ]
        ];

        $orderData = array_merge($data, ['items' => $orderItems]);

        return $this->create($orderData);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // 1. Validate Address
            $address = $this->addressRepo->findById($data['address_id']);
            if (!$address) {
                throw new BusinessException(404030, 'Delivery address not found or invalid.');
            }

            // 2. Prepare Snapshot Data
            // Fix: Đảm bảo format snapshot khớp với mong đợi của ShippingModule
            $addressSnapshot = $address->toArray();
            $consigneeName   = $data['consignee_name'] ?? $address->name ?? auth()->user()->name;
            $consigneePhone  = $data['consignee_phone'] ?? $address->phone ?? auth()->user()->phone;

            // Optional: Pre-check stock (Mặc dù hàm allocate sẽ check lại)
            /*
            foreach ($data['items'] as $item) {
                $variant = ProductVariant::where('uuid', $item['variant_uuid'])->first();
                // Giả sử có hàm check tồn kho thực tế
                // if (!$this->inventoryService->hasStock($variant->id, $item['quantity'])) {
                //     throw new BusinessException(422001, "Product {$variant->sku} is out of stock.");
                // }
            }
            */

            // 3. Create Order
            $order = $this->repository->create([
                'uuid' => (string) Str::uuid(),
                'code' => 'ATL-' . strtoupper(Str::random(8)),
                'user_id' => $data['user_id'] ?? auth()->id(),
                
                // Lưu snapshot địa chỉ
                'shipping_address_snapshot' => $addressSnapshot,
                
                // Fix: Lưu thông tin người nhận rõ ràng để module Shipping lấy dùng
                'shipping_name'  => $consigneeName,
                'shipping_phone' => $consigneePhone,
                
                'status' => OrderStatus::PENDING,
                'payment_status' => PaymentStatus::UNPAID,
                'shipping_status' => 'not_shipped', // Đồng bộ enum string
                'notes' => $data['notes'] ?? null,
                'voucher_code' => $data['voucher_code'] ?? null,
                'voucher_discount' => $data['voucher_discount'] ?? 0,
                'total_amount' => 0 // Sẽ update sau
            ]);

            // 4. Process Items & Inventory
            $itemsResult = $this->processOrderItems($order, $data['items']);
            $itemsTotal  = $itemsResult['total_amount'];
            $totalWeight = $itemsResult['total_weight']; // Lấy tổng khối lượng để tính ship

            // 5. Calculate Fee
            // Fix: Đồng bộ logic tính phí theo Cân nặng giống ShippingModule
            $shippingFee = $this->calculateShippingFee($totalWeight, $addressSnapshot);
            $discount    = (float) $order->voucher_discount;
            $grandTotal  = max(0, $itemsTotal - $discount + $shippingFee);

            // 6. Update Totals
            $order->update([
                'subtotal'     => $itemsTotal,
                'shipping_fee' => $shippingFee,
                'grand_total'  => $grandTotal,
                'total_amount' => $grandTotal, // Cập nhật lại total_amount nếu dùng cột này
            ]);

            event(new OrderCreated($order));

            return $order->refresh()->load(['items.variant.product', 'user']);
        });
    }

    /**
     * Logic này phải KHỚP 100% với ShippingService::calculateShippingFee
     */
    private function calculateShippingFee(float $totalWeight, array $addressSnapshot): int
    {
        // Logic mẫu: Dưới 2kg = 30k, mỗi kg thêm + 5k
        // Bạn có thể thêm logic check Vùng miền ở đây nếu muốn kết hợp cả 2
        
        $baseFee = 30000;
        
        if ($totalWeight <= 2000) {
            return $baseFee;
        }

        $extraKg = ceil(($totalWeight - 2000) / 1000);
        return (int) ($baseFee + ($extraKg * 5000));
    }

    public function cancel(string $uuid): Model
    {
        $order = $this->findByUuidOrFail($uuid); 

        if ($order->status === OrderStatus::CANCELLED) {
            return $order;
        }

        if (in_array($order->status, [OrderStatus::SHIPPED, OrderStatus::DELIVERED])) {
            throw new BusinessException(409132, 'Cannot cancel order that is already shipped or delivered.');
        }

        return DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $this->inventoryService->restore(
                    $item->product_variant_id,
                    $item->quantity,
                    $item->warehouse_id
                );
            }

            $order->update([
                'status' => OrderStatus::CANCELLED,
                'shipping_status' => 'not_shipped' // Reset shipping status
            ]);
            
            event(new OrderCancelled($order));

            return $order;
        });
    }

    public function updateStatus(string $uuid, string $status): Model
    {
        return DB::transaction(function () use ($uuid, $status) {
            $order = $this->findByUuidOrFail($uuid);
            $newStatusEnum = OrderStatus::tryFrom($status);
            
            if (!$newStatusEnum) {
                throw new BusinessException(400133, 'Invalid status value provided.');
            }

            $this->validateStatusTransition($order, $newStatusEnum);

            if ($newStatusEnum === OrderStatus::CANCELLED) {
                return $this->cancel($uuid);
            }

            $oldStatus = $order->status;

            $isCod = $order->payments()->where('method', 'cod')->exists(); 

            if ($newStatusEnum === OrderStatus::DELIVERED && $isCod) {
                $order->update(['payment_status' => PaymentStatus::PAID]);

                $order->payments()->where('method', 'cod')->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'transaction_id' => 'COD-' . $order->code 
                ]);
            }

            $order->update(['status' => $newStatusEnum]);

            if ($oldStatus !== $newStatusEnum) {
                event(new OrderStatusUpdated($order, $oldStatus->value, $status));
            }

            return $order->refresh();
        });
    }

    private function validateStatusTransition(Order $order, OrderStatus $new): void
    {
        if ($order->status === OrderStatus::DELIVERED) {
            throw new BusinessException(409137, 'Order has been delivered and cannot change status.');
        }
        
        if ($order->status === OrderStatus::CANCELLED) {
            throw new BusinessException(409138, 'Order is cancelled and cannot be restored.');
        }

        $isCod = $order->payments()->where('method', 'cod')->exists();
        if ($new === OrderStatus::DELIVERED && !$isCod && $order->payment_status !== PaymentStatus::PAID) {
            // throw new BusinessException(402134, 'Order must be paid before delivery unless COD.');
        }
    }

    /**
     * Return array: ['total_amount' => float, 'total_weight' => float]
     */
    protected function processOrderItems(Model $order, array $itemsData): array
    {
        $totalAmount = 0;
        $totalWeight = 0; // Thêm biến tính cân nặng
        
        $variantUuids = array_column($itemsData, 'variant_uuid');
        
        $variants = ProductVariant::with([
                'product.images', 
                'attributeValues.attribute'
            ])
            ->whereIn('uuid', $variantUuids)
            ->get()
            ->keyBy('uuid');

        foreach ($itemsData as $item) {
            $variant = $variants[$item['variant_uuid']] ?? null;

            if (!$variant) {
                throw new BusinessException(422043, "Product variant {$item['variant_uuid']} is unavailable.");
            }

            $attributesList = $variant->attributeValues->map(function($av) {
                return [
                    'name'  => $av->attribute->name, 
                    'value' => $av->value,        
                    'code'  => $av->code            
                ];
            })->values()->toArray();

            $productSnapshot = [
                'id'         => $variant->product->id,
                'name'       => $variant->product->name,
                'sku'        => $variant->sku,
                'image'      => $variant->image_url ?? $variant->product->images->first()?->image_url,
                'price_at_purchase' => $variant->price,
                'attributes' => $attributesList
            ];

            $quantity = (int) $item['quantity'];
            
            // Allocate Inventory
            $warehouseId = $this->inventoryService->allocate($variant->id, $quantity);

            $unitPrice = $variant->price; 
            $subtotal = $unitPrice * $quantity;
            $totalAmount += $subtotal;

            // Tính cân nặng (Giả sử 500g nếu ko có data)
            $weightPerItem = $variant->weight > 0 ? $variant->weight : 500;
            $totalWeight += ($weightPerItem * $quantity);

            $order->items()->create([
                'product_variant_id' => $variant->id,
                'warehouse_id'       => $warehouseId,
                'quantity'           => $quantity,
                'original_price'     => $variant->price, 
                'unit_price'         => $unitPrice,     
                'subtotal'           => $subtotal,
                'product_snapshot'   => $productSnapshot 
            ]);
        }

        return [
            'total_amount' => (float) $totalAmount,
            'total_weight' => (float) $totalWeight
        ];
    }

    public function filter(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $filters['per_page'] = $perPage;
        return $this->repository->filter($filters);
    }
}