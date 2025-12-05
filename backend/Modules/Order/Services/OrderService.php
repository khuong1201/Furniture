<?php

declare(strict_types=1);

namespace Modules\Order\Services;

use Modules\Shared\Services\BaseService;
use Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use Modules\Shared\Contracts\InventoryServiceInterface; // Thay thế StockService
use Modules\Shared\Contracts\CartServiceInterface; // Thay thế CartService
use Modules\Address\Domain\Repositories\AddressRepositoryInterface;
use Modules\Product\Domain\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Order\Events\OrderCreated;
use Modules\Order\Events\OrderCancelled;
use Modules\Order\Events\OrderStatusUpdated;
use Modules\Order\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrderService extends BaseService
{
    public function __construct(
        OrderRepositoryInterface $repository,
        protected InventoryServiceInterface $inventoryService, // Dependency Injection
        protected AddressRepositoryInterface $addressRepo,
        protected CartServiceInterface $cartService
    ) {
        parent::__construct($repository);
    }

    public function createFromCart(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $userId = auth()->id();
            $cartData = $this->cartService->getMyCart($userId); // Lấy data giỏ hàng
            
            if (empty($cartData['items'])) {
                throw ValidationException::withMessages(['cart' => 'Giỏ hàng trống.']);
            }

            // Chuyển đổi cart items thành order items format
            $orderItems = [];
            foreach ($cartData['items'] as $item) {
                $orderItems[] = [
                    'variant_uuid' => $item['variant']['uuid'],
                    'quantity' => $item['quantity']
                ];
            }

            $orderData = array_merge($data, ['items' => $orderItems]);
            
            // Apply voucher nếu có trong cart (giả định logic)
            if (!empty($cartData['voucher_code'])) {
                $orderData['voucher_code'] = $cartData['voucher_code'];
                $orderData['voucher_discount'] = $cartData['discount_amount'] ?? 0;
            }
            
            $order = $this->create($orderData);

            // Xóa giỏ hàng
            $this->cartService->clearCart($userId);

            return $order;
        });
    }

    public function create(array $data): Model
    {
        $order = DB::transaction(function () use ($data) {
            $address = $this->addressRepo->findById($data['address_id']);
            if (!$address) {
                throw ValidationException::withMessages(['address_id' => 'Địa chỉ không tồn tại.']);
            }

            // 1. Tạo Order Header
            $order = $this->repository->create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $data['user_id'] ?? auth()->id(),
                'shipping_address_snapshot' => $address->toArray(),
                'status' => OrderStatus::PENDING,
                'notes' => $data['notes'] ?? null,
                'voucher_code' => $data['voucher_code'] ?? null,
                'voucher_discount' => $data['voucher_discount'] ?? 0,
                'total_amount' => 0 // Tính sau
            ]);

            // 2. Xử lý Items & Tồn kho
            $itemsTotal = $this->processOrderItems($order, $data['items']);

            // 3. Tính tổng tiền cuối cùng
            $discount = $order->voucher_discount;
            $finalTotal = max(0, $itemsTotal - $discount);

            $order->update(['total_amount' => $finalTotal]);

            return $order->load('items.variant.product');
        });

        event(new OrderCreated($order));

        return $order;
    }

    protected function processOrderItems(Model $order, array $itemsData): float
    {
        $totalAmount = 0;
        
        // Pre-load variants để tránh N+1 Query
        $variantUuids = array_column($itemsData, 'variant_uuid');
        $variants = ProductVariant::whereIn('uuid', $variantUuids)->with('product')->get()->keyBy('uuid');

        foreach ($itemsData as $item) {
            $variant = $variants[$item['variant_uuid']] ?? null;
            if (!$variant) continue;

            $quantity = (int) $item['quantity'];

            // Gọi Inventory Service để trừ kho và lấy ID kho
            try {
                $warehouseId = $this->inventoryService->allocate($variant->id, $quantity);
            } catch (\Exception $e) {
                throw ValidationException::withMessages(['items' => "Sản phẩm {$variant->sku} không đủ hàng tồn kho."]);
            }

            // Tính giá (Logic khuyến mãi nên tách ra PromotionService, ở đây tạm thời đơn giản hóa)
            $unitPrice = $variant->price; 
            $subtotal = $unitPrice * $quantity;
            $totalAmount += $subtotal;

            $order->items()->create([
                'product_variant_id' => $variant->id,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'original_price' => $variant->price,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'product_snapshot' => [
                    'name' => $variant->product->name,
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

            // Hoàn kho
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
        $oldStatus = $order->status;

        // Nếu chuyển sang Cancelled thì gọi hàm cancel để hoàn kho
        if ($status === OrderStatus::CANCELLED->value) {
            return $this->cancel($uuid);
        }

        $order->update(['status' => $status]);
        
        if ($oldStatus->value !== $status) {
            event(new OrderStatusUpdated($order, $oldStatus->value, $status));
        }
        
        return $order;
    }
    
    public function getOrderStats(): array
    {
        return $this->repository->countByStatus();
    }
}