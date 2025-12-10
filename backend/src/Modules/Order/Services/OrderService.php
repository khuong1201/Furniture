<?php

declare(strict_types=1);

namespace Modules\Order\Services;

use Modules\Shared\Services\BaseService;
use Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use Modules\Shared\Contracts\InventoryServiceInterface;
use Modules\Shared\Contracts\CartServiceInterface;
use Modules\Address\Domain\Repositories\AddressRepositoryInterface;
use Modules\Product\Domain\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Order\Enums\OrderStatus;
use Illuminate\Support\Facades\Log;

// Events
use Modules\Order\Events\OrderCreated;
use Modules\Order\Events\OrderCancelled;
use Modules\Order\Events\OrderStatusUpdated;

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
        return DB::transaction(function () use ($data) {
            $userId = auth()->id();
            
            $cartData = $this->cartService->getMyCart($userId); 
            
            if (empty($cartData['items'])) {
                throw ValidationException::withMessages(['cart' => 'Giỏ hàng trống.']);
            }

            $selectedIds = $data['selected_item_uuids'] ?? [];
            $hasSelection = !empty($selectedIds);

            $orderItems = [];
            $purchasedItemUuids = [];

            foreach ($cartData['items'] as $item) {
                if ($hasSelection && !in_array($item['uuid'], $selectedIds)) {
                    continue;
                }

                $variantUuid = $item['variant_uuid'] ?? $item['variant']['uuid'] ?? null;
                
                if (!$variantUuid) {
                    Log::warning("OrderService: Bỏ qua item lỗi", ['item' => $item]);
                    continue; 
                }

                $orderItems[] = [
                    'variant_uuid' => $variantUuid,
                    'quantity' => (int) $item['quantity']
                ];
                
                $purchasedItemUuids[] = $item['uuid'];
            }

            if (empty($orderItems)) {
                throw ValidationException::withMessages(['cart' => 'Vui lòng chọn ít nhất 1 sản phẩm để thanh toán.']);
            }

            $orderData = array_merge($data, ['items' => $orderItems]);

            if (!empty($cartData['voucher_code'])) {
                $orderData['voucher_code'] = $cartData['voucher_code'];
                $orderData['voucher_discount'] = $cartData['discount_amount'] ?? 0;
            }
            
            $order = $this->create($orderData);

            $this->cartService->removeItemsList($userId, $purchasedItemUuids);

            return $order;
        });
    }

    public function createBuyNow(array $data): Model
    {
        $orderItems = [
            [
                'variant_uuid' => $data['variant_uuid'],
                'quantity' => (int) $data['quantity']
            ]
        ];

        $orderData = array_merge($data, ['items' => $orderItems]);

        if (!empty($data['voucher_code'])) {
            $orderData['voucher_code'] = $data['voucher_code'];
            $orderData['voucher_discount'] = 0; // TODO: Calculate via PromotionService
        }

        return $this->create($orderData);
    }

    public function create(array $data): Model
    {
        // 1. Transaction
        $order = DB::transaction(function () use ($data) {
            $address = $this->addressRepo->findById($data['address_id']);
            if (!$address) {
                throw ValidationException::withMessages(['address_id' => 'Địa chỉ không tồn tại.']);
            }

            // Tạo Order Header (Lúc này total = 0)
            $order = $this->repository->create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $data['user_id'] ?? auth()->id(),
                'shipping_address_snapshot' => $address->toArray(),
                'status' => OrderStatus::PENDING,
                'notes' => $data['notes'] ?? null,
                'voucher_code' => $data['voucher_code'] ?? null,
                'voucher_discount' => $data['voucher_discount'] ?? 0,
                'total_amount' => 0
            ]);

            // Insert Items vào DB & Trừ kho
            $itemsTotal = $this->processOrderItems($order, $data['items']);

            // Tính tổng tiền cuối cùng
            $discount = $order->voucher_discount;
            $finalTotal = max(0, $itemsTotal - $discount);

            // Update Total vào DB
            $order->update(['total_amount' => $finalTotal]);
            
            // [FIX QUAN TRỌNG NHẤT]: Refresh model để lấy lại items vừa insert và total vừa update
            // Nếu thiếu ->refresh(), object $order cũ vẫn giữ items=[] và total=0
            return $order->refresh()->load(['items.variant.product', 'user']);
        });

        // 2. Bắn Event (Ngoài Transaction)
        event(new OrderCreated($order));

        return $order;
    }

    protected function processOrderItems(Model $order, array $itemsData): float
    {
        $totalAmount = 0;
        
        $variantUuids = array_column($itemsData, 'variant_uuid');
        $variants = ProductVariant::whereIn('uuid', $variantUuids)
            ->with('product.images')
            ->get()
            ->keyBy('uuid');

        foreach ($itemsData as $item) {
            $variant = $variants[$item['variant_uuid']] ?? null;
            
            if (!$variant) {
                // Log cảnh báo hoặc throw exception tùy nhu cầu
                Log::error("Order Item SKU not found", ['uuid' => $item['variant_uuid']]);
                continue;
            }

            $quantity = (int) $item['quantity'];

            // Trừ kho
            try {
                $warehouseId = $this->inventoryService->allocate($variant->id, $quantity);
            } catch (\Exception $e) {
                throw ValidationException::withMessages(['items' => "Sản phẩm {$variant->sku} không đủ hàng tồn kho."]);
            }

            // Tính giá
            $unitPrice = $variant->price; 
            $subtotal = $unitPrice * $quantity;
            $totalAmount += $subtotal;

            // Insert vào bảng order_items
            $order->items()->create([
                'product_variant_id' => $variant->id,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'original_price' => $variant->price,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'product_snapshot' => [
                    'name' => $variant->product->name ?? 'Product',
                    'sku' => $variant->sku,
                    'image' => $variant->image_url ?? $variant->product->images->first()?->image_url
                ]
            ]);
        }

        return $totalAmount;
    }

    public function cancel(string $uuid): Model
    {
        $order = DB::transaction(function () use ($uuid) {
            $order = $this->repository->findByUuidOrFail($uuid);

            if ($order->status === OrderStatus::CANCELLED) return $order;
            
            if (in_array($order->status, [OrderStatus::SHIPPED, OrderStatus::DELIVERED])) {
                throw ValidationException::withMessages(['status' => 'Không thể hủy đơn hàng đã giao/đang giao.']);
            }

            foreach ($order->items as $item) {
                $this->inventoryService->restore(
                    $item->product_variant_id, 
                    $item->quantity, 
                    $item->warehouse_id
                );
            }

            $order->update(['status' => OrderStatus::CANCELLED]);
            return $order;
        });

        event(new OrderCancelled($order));

        return $order;
    }

    public function updateStatus(string $uuid, string $status): Model
    {
        $order = $this->findByUuidOrFail($uuid);
        
        $oldStatus = $order->status instanceof OrderStatus ? $order->status->value : $order->status;

        if ($status === OrderStatus::CANCELLED->value) {
            return $this->cancel($uuid);
        }

        $order->update(['status' => $status]);
        
        if ($oldStatus !== $status) {
            event(new OrderStatusUpdated($order, (string)$oldStatus, $status));
        }
        
        return $order;
    }
    
    public function getOrderStats(): array
    {
        return $this->repository->countByStatus();
    }
}