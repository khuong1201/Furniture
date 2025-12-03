<?php

namespace Modules\Order\Services;

use Modules\Shared\Services\BaseService;
use Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use Modules\Product\Services\StockService; 
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Product\Domain\Models\InventoryStock;
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
        protected StockService $stockService,
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

                $cartItemModel = \Modules\Cart\Domain\Models\CartItem::where('uuid', $item['uuid'])->first();
                if (!$cartItemModel) continue;

                $orderItems[] = [
                    'variant_uuid' => $cartItemModel->variant->uuid,
                    'quantity' => $item['quantity']
                ];
            }

            $orderData = array_merge($data, ['items' => $orderItems]);
            
            $order = $this->create($orderData);

            $cart = $this->cartService->getRepository()->findByUser($userId);
            if ($cart) {
                $this->cartService->clearCart($cart);
            }

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

            return $order->load('items.variant.product');
        });

        event(new OrderCreated($order));

        return $order;
    }

    protected function processOrderItems(Model $order, array $itemsData): float
    {
        $totalAmount = 0;

        foreach ($itemsData as $item) {

            $inventoryStock = $this->stockService->allocate($item['variant_uuid'], $item['quantity']);

            $variant = $inventoryStock->variant; 
            $product = $variant->product;

            $pricing = $this->calculateItemPrice($variant, $product);
            
            $qty = (int) $item['quantity'];
            $lineSubtotal = $pricing['unit_price'] * $qty;
            $totalAmount += $lineSubtotal;

            $order->items()->create([
                'product_variant_id' => $variant->id,
                'warehouse_id' => $inventoryStock->warehouse_id,
                'quantity' => $qty,
                'original_price' => $pricing['original_price'],
                'discount_amount' => $pricing['discount_amount'],
                'unit_price' => $pricing['unit_price'],
                'subtotal' => $lineSubtotal,

                'product_snapshot' => [
                    'name' => $product->name,
                    'sku' => $variant->sku,
                    'attributes' => $variant->attributeValues->map(fn($v) => [
                        'name' => $v->attribute->name,
                        'value' => $v->value
                    ])->toArray()
                ]
            ]);
        }

        return $totalAmount;
    }

    protected function calculateItemPrice(ProductVariant $variant, $product): array
    {
        $originalPrice = $variant->price;
        $discountAmount = 0;

        $activePromotions = $product->promotions()->where('status', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();

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

                $this->stockService->restore(
                    $item->variant->uuid, 
                    $item->quantity, 
                    $item->warehouse_id
                );
            }

            $order->update(['status' => 'cancelled']);
            return $order;
        });

        event(new OrderCancelled($order));

        return $order;
    }
}