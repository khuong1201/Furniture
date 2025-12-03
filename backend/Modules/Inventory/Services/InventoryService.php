<?php

namespace Modules\Inventory\Services;

use Modules\Shared\Services\BaseService;
use Modules\Inventory\Domain\Repositories\InventoryRepositoryInterface;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Warehouse\Domain\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;

class InventoryService extends BaseService
{
    public function __construct(InventoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function adjust(string $variantUuid, string $warehouseUuid, int $delta, string $reason = 'manual'): Model
    {
        return DB::transaction(function () use ($variantUuid, $warehouseUuid, $delta) {
            $variant = ProductVariant::where('uuid', $variantUuid)->firstOrFail();
            $warehouse = Warehouse::where('uuid', $warehouseUuid)->firstOrFail();

            $stock = $this->repository->findByVariantAndWarehouse($variant->id, $warehouse->id, lock: true);

            if (!$stock) {
                if ($delta < 0) {
                    throw ValidationException::withMessages(['quantity' => 'Kho chưa có sản phẩm này để xuất.']);
                }
                $stock = $this->repository->create([
                    'product_variant_id' => $variant->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => 0
                ]);
            }

            $newQty = $stock->quantity + $delta;

            if ($newQty < 0) {
                throw ValidationException::withMessages([
                    'quantity' => "Tồn kho không đủ để xuất. Hiện tại: {$stock->quantity}, Yêu cầu trừ: " . abs($delta)
                ]);
            }

            $this->repository->update($stock->id, ['quantity' => $newQty]);

            return $stock->load(['variant.product', 'warehouse']);
        });
    }

    public function upsert(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $variant = ProductVariant::where('uuid', $data['variant_uuid'])->firstOrFail();
            $warehouse = Warehouse::where('uuid', $data['warehouse_uuid'])->firstOrFail();

            $stock = $this->repository->findByVariantAndWarehouse($variant->id, $warehouse->id);

            $payload = [
                'quantity' => $data['quantity'],
                'min_threshold' => $data['min_threshold'] ?? ($stock->min_threshold ?? 0),
            ];

            if ($stock) {
                $this->repository->update($stock->id, $payload);
            } else {
                $payload['product_variant_id'] = $variant->id;
                $payload['warehouse_id'] = $warehouse->id;
                $stock = $this->repository->create($payload);
            }

            return $stock->load(['variant', 'warehouse']);
        });
    }
}