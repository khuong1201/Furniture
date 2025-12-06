<?php

declare(strict_types=1);

namespace Modules\Inventory\Services;

use Modules\Shared\Services\BaseService;
use Modules\Shared\Contracts\InventoryServiceInterface;
use Modules\Inventory\Domain\Repositories\InventoryRepositoryInterface;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Warehouse\Domain\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;
use Modules\Inventory\Events\LowStockDetected;
use Exception;

class InventoryService extends BaseService implements InventoryServiceInterface
{
    public function __construct(InventoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function allocate(int $variantId, int $quantity): int
    {

        $stock = $this->repository->query()
            ->where('product_variant_id', $variantId)
            ->where('quantity', '>=', $quantity)
            ->orderByDesc('quantity') 
            ->lockForUpdate()
            ->first();

        if (!$stock) {
            throw new Exception("Sản phẩm (Variant ID: {$variantId}) không đủ hàng tồn kho.");
        }

        $newQty = $stock->quantity - $quantity;
        $this->repository->update($stock, ['quantity' => $newQty]);

        // Check low stock sau khi trừ
        if ($newQty <= ($stock->min_threshold ?? 5)) {

            $stock->load(['variant.product', 'warehouse']);
            event(new LowStockDetected($stock->variant, $stock->warehouse, $newQty));
        }

        return $stock->warehouse_id;
    }

    /**
     * Hoàn kho khi hủy đơn (Restore stock).
     */
    public function restore(int $variantId, int $quantity, int $warehouseId): void
    {
        $stock = $this->repository->findByVariantAndWarehouse($variantId, $warehouseId, lock: true);

        if ($stock) {
            $this->repository->update($stock, ['quantity' => $stock->quantity + $quantity]);
        } else {
            // Trường hợp hiếm: Kho bị xóa hoặc record stock bị xóa (dù trước đó đã allocate)
            // Ta tạo lại record stock mới
            $this->repository->create([
                'product_variant_id' => $variantId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'min_threshold' => 0
            ]);
        }
    }

    public function syncStock(int $variantId, array $stockData): void
    {
        foreach ($stockData as $data) {
            $warehouse = Warehouse::where('uuid', $data['warehouse_uuid'])->first();
            if (!$warehouse) continue;

            $stock = $this->repository->findByVariantAndWarehouse($variantId, $warehouse->id);
            $quantity = (int) $data['quantity'];
            $threshold = 5; // Hoặc lấy từ config/data

            if ($stock) {
                // Update
                $threshold = $stock->min_threshold ?? 5;
                $this->repository->update($stock, ['quantity' => $quantity]);
                
                // [THÊM MỚI] Check Low Stock
                if ($quantity <= $threshold) {
                    $stock->load(['variant.product', 'warehouse']);
                    event(new LowStockDetected($stock->variant, $stock->warehouse, $quantity));
                }
            } else {
                // Create
                $newStock = $this->repository->create([
                    'product_variant_id' => $variantId,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $quantity,
                    'min_threshold' => 0
                ]);
                
                // [THÊM MỚI] Check Low Stock (Dù mới tạo nhưng nếu quantity thấp cũng báo)
                if ($quantity <= 5) {
                    $newStock->load(['variant.product', 'warehouse']);
                    event(new LowStockDetected($newStock->variant, $newStock->warehouse, $quantity));
                }
            }
        }
    }

    public function getTotalStock(int $variantId): int
    {
        // Tính tổng quantity từ tất cả các kho
        return (int) $this->repository->query()
            ->where('product_variant_id', $variantId)
            ->sum('quantity');
    }
    
    // --- END IMPLEMENTATION ---

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
                    'quantity' => "Tồn kho không đủ. Hiện tại: {$stock->quantity}, Yêu cầu trừ: " . abs($delta)
                ]);
            }

            $updatedStock = $this->repository->update($stock, ['quantity' => $newQty]);

            if ($newQty <= ($updatedStock->min_threshold ?? 5)) { 
                $updatedStock->load(['variant.product', 'warehouse']);
                event(new LowStockDetected($updatedStock->variant, $updatedStock->warehouse, $newQty));
            }

            return $updatedStock->load(['variant.product', 'warehouse']);
        });
    }

    public function upsert(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $variant = ProductVariant::where('uuid', $data['variant_uuid'])->firstOrFail();
            $warehouse = Warehouse::where('uuid', $data['warehouse_uuid'])->firstOrFail();

            $stock = $this->repository->findByVariantAndWarehouse($variant->id, $warehouse->id);

            $payload = ['quantity' => $data['quantity']];
            
            if (isset($data['min_threshold'])) {
                $payload['min_threshold'] = $data['min_threshold'];
            }

            if ($stock) {
                return $this->repository->update($stock, $payload);
            } 
            
            return $this->repository->create(array_merge($payload, [
                'product_variant_id' => $variant->id,
                'warehouse_id' => $warehouse->id
            ]));
        });
    }
}