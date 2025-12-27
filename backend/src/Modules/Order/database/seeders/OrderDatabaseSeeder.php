<?php

namespace Modules\Order\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\User\Domain\Models\User;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Order\Domain\Models\Order;
use Modules\Order\Domain\Models\OrderItem;
use Modules\Warehouse\Domain\Models\Warehouse;
use Modules\Inventory\Domain\Models\InventoryStock;
use Modules\Inventory\Domain\Models\InventoryLog;
use Modules\Payment\Domain\Models\Payment;
use Modules\Shipping\Domain\Models\Shipping;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Enums\PaymentStatus;

class OrderDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::with('addresses')
            ->whereHas('roles', fn($q) => $q->where('name', 'customer'))
            ->whereHas('addresses')
            ->get();

        $warehouses = Warehouse::all();
        $variants = ProductVariant::with('product')->get();

        if ($users->isEmpty() || $warehouses->isEmpty() || $variants->isEmpty()) {
            $this->command->warn("⚠️  Skipping Orders: Missing Users, Warehouses, or Product Variants.");
            return;
        }

        $this->command->info("🚀 Seeding realistic orders from 2023 to now...");

        $startDate = Carbon::create(2023, 1, 1);
        $endDate = Carbon::now();
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $yearFactor = ($currentDate->year - 2023) + 1;
            $seasonFactor = in_array($currentDate->month, [11, 12, 1]) ? 1.5 : 1.0;
            
            $dailyOrders = (int) ceil(rand(1, 3) * $yearFactor * $seasonFactor);
            if (rand(1, 100) <= 10) $dailyOrders = 0;

            for ($k = 0; $k < $dailyOrders; $k++) {
                $this->createSingleOrder($currentDate, $users, $variants, $warehouses);
            }

            $currentDate->addDay();
        }
    }

    protected function createSingleOrder($date, $users, $variants, $warehouses)
    {
        $orderTime = $date->copy()->setTime(rand(8, 22), rand(0, 59));
        
        $user = $users->random();
        $address = $user->addresses->where('is_default', true)->first() ?? $user->addresses->first();
        
        $addressSnapshot = [
            'full_name'    => $address->full_name ?? $user->name,
            'phone'        => $address->phone,
            'address_line' => $address->street ?? $address->address,
            'ward'         => $address->ward,
            'district'     => $address->district,
            'province'     => $address->province,
            'country'      => 'Vietnam'
        ];

        $daysDiff = $orderTime->diffInDays(now());
        if ($daysDiff > 30) {
            $status = OrderStatus::DELIVERED;
        } elseif ($daysDiff > 7) {
            $status = fake()->randomElement([OrderStatus::SHIPPING, OrderStatus::DELIVERED]);
        } else {
            $status = fake()->randomElement([OrderStatus::PENDING, OrderStatus::PROCESSING, OrderStatus::SHIPPING]);
        }

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'code' => 'ORD-' . $orderTime->format('ymd') . '-' . strtoupper(Str::random(6)),
            'shipping_name' => $addressSnapshot['full_name'],
            'shipping_phone' => $addressSnapshot['phone'],
            'shipping_address_snapshot' => $addressSnapshot,
            'status' => $status,
            'ordered_at' => $orderTime,
            'created_at' => $orderTime,
            'updated_at' => $orderTime,
        ]);

        $this->processOrderFulfillment($order, $variants, $warehouses, $orderTime, $addressSnapshot);
    }

    protected function processOrderFulfillment($order, $variants, $warehouses, Carbon $time, array $addressSnapshot)
    {
        DB::transaction(function () use ($order, $variants, $warehouses, $time, $addressSnapshot) {
            $warehouse = $warehouses->random();
            $orderVariants = $variants->random(rand(1, 3)); 
            $subTotal = 0;

            foreach ($orderVariants as $variant) {
                $qty = rand(1, 3);
                $price = $variant->price;
                $lineTotal = $price * $qty;
                $subTotal += $lineTotal;

                if ($order->status !== OrderStatus::CANCELLED) {
                    $this->handleInventoryAndSoldCount($warehouse, $variant, $qty, $time, $order->code);
                }

                OrderItem::create([
                    'uuid' => Str::uuid(),
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'original_price' => $price,
                    'subtotal' => $lineTotal,
                    'product_snapshot' => [
                        'name' => $variant->name,
                        'sku' => $variant->sku,
                        'image' => null
                    ]
                ]);
            }

            $shippingFee = rand(15, 50) * 1000;
            $grandTotal = $subTotal + $shippingFee;

            $paymentStatus = PaymentStatus::UNPAID;
            if ($order->status === OrderStatus::DELIVERED || $order->status === OrderStatus::SHIPPING) {
                $paymentStatus = PaymentStatus::PAID; 
            }

            $order->update([
                'subtotal' => $subTotal,
                'shipping_fee' => $shippingFee,
                'grand_total' => $grandTotal,
                'payment_status' => $paymentStatus
            ]);

            $this->createPayment($order, $paymentStatus, $time);
            $this->createShipping($order, $addressSnapshot, $shippingFee, $time);
        });
    }

    protected function handleInventoryAndSoldCount($warehouse, $variant, $reqQty, $time, $orderCode)
    {
        $stock = InventoryStock::firstOrCreate(
            ['warehouse_id' => $warehouse->id, 'product_variant_id' => $variant->id],
            ['quantity' => 0, 'min_threshold' => 10]
        );

        if ($stock->quantity < $reqQty) {
            $importQty = $reqQty + rand(20, 50); 
            $restockTime = $time->copy()->subHours(rand(1, 5));
            
            InventoryLog::create([
                'warehouse_id' => $warehouse->id,
                'product_variant_id' => $variant->id,
                'user_id' => 1,
                'previous_quantity' => $stock->quantity,
                'new_quantity' => $stock->quantity + $importQty,
                'quantity_change' => $importQty,
                'type' => 'import',
                'reason' => "System Auto Restock",
                'created_at' => $restockTime,
                'updated_at' => $restockTime
            ]);
            $stock->increment('quantity', $importQty);
            $stock->refresh();
        }

        $oldQty = $stock->quantity;
        $stock->decrement('quantity', $reqQty);

        InventoryLog::create([
            'warehouse_id' => $warehouse->id,
            'product_variant_id' => $variant->id,
            'user_id' => 1,
            'previous_quantity' => $oldQty,
            'new_quantity' => $oldQty - $reqQty,
            'quantity_change' => -$reqQty,
            'type' => 'export',
            'reason' => "Order $orderCode",
            'created_at' => $time,
            'updated_at' => $time
        ]);

        $variant->increment('sold_count', $reqQty);
        if ($variant->product_id) {
            DB::table('products')->where('id', $variant->product_id)->increment('sold_count', $reqQty);
        }
    }

    protected function createPayment($order, $paymentStatus, $time)
    {
        $isPaid = ($paymentStatus === PaymentStatus::PAID);
        $method = $isPaid ? fake()->randomElement(['momo', 'vnpay', 'bank_transfer']) : 'cod';
        $paidAt = $isPaid ? $time->copy()->addMinutes(rand(5, 30)) : null;

        Payment::create([
            'uuid' => (string) Str::uuid(),
            'order_id' => $order->id,
            'amount' => $order->grand_total,
            'currency' => 'VND',
            'status' => $isPaid ? 'paid' : 'pending',
            'method' => $method,
            'transaction_id' => $isPaid ? strtoupper(substr($method, 0, 3)) . $time->format('ymd') . rand(1000,9999) : null,
            'paid_at' => $paidAt,
            'created_at' => $time,
            'updated_at' => $isPaid ? $paidAt : $time
        ]);
    }

    protected function createShipping($order, $addressSnapshot, $fee, $time)
    {
        // Enum Fix: Dùng match để map status
        $shipStatus = match($order->status) {
            OrderStatus::DELIVERED => 'delivered',
            OrderStatus::SHIPPING  => 'shipped',
            OrderStatus::CANCELLED => 'cancelled',
            default                => 'pending',
        };
        
        $hasShipped = in_array($shipStatus, ['shipped', 'delivered']);
        $shippedAt = $hasShipped ? $time->copy()->addHours(rand(12, 24)) : null;
        $deliveredAt = ($shipStatus === 'delivered') ? $shippedAt->copy()->addDays(rand(2, 5)) : null;

        if ($deliveredAt) {
            $order->update(['updated_at' => $deliveredAt]);
        }

        $fullAddress = implode(', ', array_filter([
            $addressSnapshot['address_line'], 
            $addressSnapshot['ward'], 
            $addressSnapshot['district'], 
            $addressSnapshot['province']
        ]));

        Shipping::create([
            'uuid' => (string) Str::uuid(),
            'order_id' => $order->id,
            
            // Map đúng với Migration Shippings
            'consignee_name' => $addressSnapshot['full_name'],
            'consignee_phone' => $addressSnapshot['phone'],
            'address_full' => $fullAddress,
            
            'provider' => $hasShipped ? fake()->randomElement(['GHTK', 'GHN', 'ViettelPost']) : null,
            'tracking_number' => $hasShipped ? strtoupper(fake()->bothify('VN????#####')) : null,
            
            'status' => $shipStatus,
            'fee' => $fee,
            
            // --- ĐÃ BỎ cod_amount Ở ĐÂY ---
            
            'created_at' => $time,
            'shipped_at' => $shippedAt,
            'delivered_at' => $deliveredAt,
            'updated_at' => $deliveredAt ?? $shippedAt ?? $time
        ]);
    }
}