<?php

namespace Modules\Order\Services;

use Modules\Shared\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Services\StockService;

class OrderService extends BaseService
{
    protected StockService $stockService;

    public function __construct(
        \Modules\Order\Domain\Repositories\OrderRepositoryInterface $repository,
        StockService $stockService,
    ) {
        parent::__construct($repository);
        $this->stockService = $stockService;
    }

    /**
     * CREATE ORDER (PENDING)
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['uuid'] = (string) Str::uuid();
            $data['user_id'] = auth()->id();
            $data['status'] = 'pending';
            $data['payment_status'] = 'unpaid';

            $order = $this->repository->create($data);

            foreach ($data['items'] as $item) {
                \Log::info('Creating order item payload', $item);
                $product = Product::where('uuid', $item['product_uuid'])->firstOrFail();
                $qty = (int) $item['quantity'];

                $warehouse = $this->stockService->allocate($product, $qty);

                \Log::info('Allocated warehouse', [
                    'product_id'   => $product->id,
                    'product_uuid' => $product->uuid,
                    'warehouse'    => $warehouse ? $warehouse->toArray() : null,
                ]);

                $order->items()->create([
                    'uuid'         => (string) Str::uuid(),
                    'product_id'   => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity'     => $qty,
                    'unit_price'   => $product->price,
                ]);
            }

            $order->load(['items.product','items.warehouse','user']);
            $order->total_amount = $this->computeTotal($order);
            $order->save();

            return $order->refresh();
        });
    }

    /**
     * UPDATE ORDER (ONLY WHEN PENDING)
     */
    public function update(string $uuid, array $data)
    {
        return DB::transaction(function () use ($uuid, $data) {
            $order = $this->repository->findByUuidOrFail($uuid);

            if ($order->status !== 'pending') {
                throw new \Exception("Order không ở trạng thái pending, không thể cập nhật.");
            }

            // cập nhật address_id và notes
            $order->fill(collect($data)->only(['address_id','shipping_address','billing_address','notes'])->toArray());
            $order->save();

            if (!empty($data['items'])) {
                // restore stock cũ
                $order->load('items.product');
                foreach ($order->items as $oldItem) {
                    $this->stockService->restore(
                        $oldItem->product,
                        $oldItem->quantity,
                        $oldItem->warehouse_id
                    );
                }

                $order->items()->delete();

                foreach ($data['items'] as $item) {
                    $product = Product::where('uuid', $item['product_uuid'])->firstOrFail();
                    $qty = (int) $item['quantity'];

                    $warehouse = $this->stockService->allocate($product, $qty);

                    $order->items()->create([
                        'uuid'         => (string) Str::uuid(),
                        'product_id'   => $product->id,
                        'warehouse_id' => $warehouse->id,
                        'quantity'     => $qty,
                        'unit_price'   => $product->price,
                    ]);
                }
            }

            $order->load(['items.product','items.warehouse','user']);
            $order->total_amount = $this->computeTotal($order);
            $order->save();

            return $order->refresh();
        });
    }

    /**
     * CANCEL ORDER
     */
    public function cancel(string $uuid)
    {
        return DB::transaction(function () use ($uuid) {
            $order = $this->repository->findByUuidOrFail($uuid);

            if ($order->status === 'cancelled') return $order;
            if ($order->payment_status === 'paid') {
                throw new \Exception("Order đã thanh toán, không thể hủy.");
            }

            foreach ($order->items as $item) {
                $this->stockService->restore(
                    $item->product,
                    $item->quantity,
                    $item->warehouse_id
                );
            }

            $order->status = 'cancelled';
            $order->save();

            return $order->refresh();
        });
    }

    public function findByUuidOrFail(string $uuid)
    {
        return $this->repository->findByUuidOrFail($uuid);
    }

    /**
     * Helper: compute total
     */
    protected function computeTotal($order): float
    {
        return $order->items->reduce(fn($carry, $i) => $carry + ($i->quantity * $i->unit_price), 0);
    }
}