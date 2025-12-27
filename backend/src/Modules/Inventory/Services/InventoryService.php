<?php

declare(strict_types=1);

namespace Modules\Inventory\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Domain\Models\InventoryStock;
use Modules\Inventory\Domain\Repositories\InventoryRepositoryInterface;
use Modules\Inventory\Events\LowStockDetected;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Shared\Contracts\InventoryServiceInterface;
use Modules\Shared\Exceptions\BusinessException;
use Modules\Shared\Services\BaseService;
use Modules\Warehouse\Domain\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InventoryService extends BaseService implements InventoryServiceInterface
{
    public function __construct(InventoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        return $this->repository->filter($filters);
    }

    public function allocate(int $variantId, int $quantity): int
    {
        return DB::transaction(function () use ($variantId, $quantity) {
            $stock = $this->repository->findStockForAllocation($variantId, $quantity);

            if (!$stock) {
                throw new BusinessException(409091, "Variant ID {$variantId} out of stock or insufficient quantity");
            }

            $oldQty = $stock->quantity;
            $newQty = $oldQty - $quantity;
            
            $this->repository->update($stock, ['quantity' => $newQty]);
            $this->checkLowStock($stock, $newQty);

            $this->logChange($stock->warehouse_id, $variantId, $oldQty, $newQty, -$quantity, 'allocation', 'Order Allocation');

            return $stock->warehouse_id;
        });
    }

    public function restore(int $variantId, int $quantity, int $warehouseId): void
    {
        DB::transaction(function () use ($variantId, $quantity, $warehouseId) {
            $stock = $this->repository->findByVariantAndWarehouse($variantId, $warehouseId, lock: true);

            if ($stock) {
                $oldQty = $stock->quantity;
                $newQty = $oldQty + $quantity;
                $this->repository->update($stock, ['quantity' => $newQty]);
                $this->logChange($warehouseId, $variantId, $oldQty, $newQty, $quantity, 'restore', 'Order Cancelled/Restored');
            } else {
                $this->repository->create([
                    'product_variant_id' => $variantId,
                    'warehouse_id'       => $warehouseId,
                    'quantity'           => $quantity,
                    'min_threshold'      => 0
                ]);
                $this->logChange($warehouseId, $variantId, 0, $quantity, $quantity, 'restore', 'Order Cancelled/Restored');
            }
        });
    }

    public function syncStock(int $variantId, array $stockData): void
    {
        foreach ($stockData as $data) {
            $warehouse = Warehouse::where('uuid', $data['warehouse_uuid'])->first();
            if (!$warehouse) continue;

            $stock = $this->repository->findByVariantAndWarehouse($variantId, $warehouse->id);
            $qty = (int) $data['quantity'];

            if ($stock) {
                $oldQty = $stock->quantity;
                if ($oldQty !== $qty) {
                    $this->repository->update($stock, ['quantity' => $qty]);
                    $this->checkLowStock($stock, $qty);
                    $this->logChange($warehouse->id, $variantId, $oldQty, $qty, $qty - $oldQty, 'sync');
                }
            } else {
                $newStock = $this->repository->create([
                    'product_variant_id' => $variantId,
                    'warehouse_id'       => $warehouse->id,
                    'quantity'           => $qty,
                    'min_threshold'      => 0
                ]);
                $this->checkLowStock($newStock, $qty);
                $this->logChange($warehouse->id, $variantId, 0, $qty, $qty, 'sync');
            }
        }
    }

    public function getTotalStock(int $variantId): int
    {
        return $this->repository->sumQuantityByVariant($variantId);
    }

    public function adjust(string $inventoryUuid, int $delta, string $reason = 'manual'): Model 
    {
        return DB::transaction(function () use ($inventoryUuid, $delta, $reason) {
            $stock = InventoryStock::where('uuid', $inventoryUuid)
                ->lockForUpdate()
                ->with([
                    'warehouse',
                    'variant.product',
                ])
                ->firstOrFail();

            $oldQty = $stock->quantity;
            $newQty = $oldQty + $delta;

            if ($newQty < 0) {
                throw new BusinessException(400092, 'Insufficient stock');
            }

            $this->repository->update($stock, ['quantity' => $newQty]);
            $this->checkLowStock($stock, $newQty);

            $this->logChange(
                $stock->warehouse_id,
                $stock->product_variant_id,
                $oldQty,
                $newQty,
                $delta,
                'adjustment',
                $reason
            );

            return $stock->load(['variant.product', 'warehouse']);
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

            $oldQty = 0;
            if ($stock) {
                $oldQty = $stock->quantity;
                $this->repository->update($stock, $payload);
            } else {
                $stock = $this->repository->create(array_merge($payload, [
                    'product_variant_id' => $variant->id,
                    'warehouse_id'       => $warehouse->id
                ]));
            }
            
            if ($oldQty !== $data['quantity']) {
                $delta = $data['quantity'] - $oldQty;
                $this->logChange($warehouse->id, $variant->id, $oldQty, $data['quantity'], $delta, 'stocktake', 'Inventory Upsert/Audit');
            }

            return $stock;
        });
    }

    // New: Dashboard Stats Logic
    public function getDashboardStats(?string $warehouseUuid): array
    {
        $warehouseId = null;
        if ($warehouseUuid) {
            $warehouse = Warehouse::where('uuid', $warehouseUuid)->first();
            $warehouseId = $warehouse ? $warehouse->id : null;
        }

        return $this->repository->getDashboardMetrics($warehouseId);
    }

    public function getMovementChartData(?string $warehouseUuid, string $period = 'week', ?int $month = null, ?int $year = null): array
    {
        $warehouseId = null;
        if ($warehouseUuid) {
            $warehouse = Warehouse::where('uuid', $warehouseUuid)->first();
            $warehouseId = $warehouse ? $warehouse->id : null;
        }

        $now = Carbon::now();
        $targetYear = $year ?? $now->year;
        $targetMonth = $month ?? $now->month;

        $rawData = $this->repository->getMovementChartData($warehouseId, $period, $targetMonth, $targetYear);

        $keyedData = [];
        foreach ($rawData as $item) {
            $keyedData[$item['time_unit']] = $item;
        }

        $result = [];
        
        if ($period === 'year') {
            $startDate = Carbon::create($targetYear, 1, 1)->startOfDay();
            $endDate = Carbon::create($targetYear, 12, 31)->endOfDay();
            $periodObj = CarbonPeriod::create($startDate, '1 month', $endDate);
            $dateFormat = 'Y-m';
            $displayFormat = 'M'; 
        } elseif ($period === 'month') {
            $startDate = Carbon::create($targetYear, $targetMonth, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();
            $periodObj = CarbonPeriod::create($startDate, '1 day', $endDate);
            $dateFormat = 'Y-m-d';
            $displayFormat = 'd/m';
        } else {
            $endDate = Carbon::now()->endOfDay();
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            $periodObj = CarbonPeriod::create($startDate, '1 day', $endDate);
            $dateFormat = 'Y-m-d';
            $displayFormat = 'd/m';
        }

        foreach ($periodObj as $date) {
            $key = $date->format($dateFormat);
            
            if (isset($keyedData[$key])) {
                $result[] = [
                    'name'     => $date->format($displayFormat),
                    'inbound'  => (int) $keyedData[$key]['import_qty'],
                    'outbound' => (int) $keyedData[$key]['export_qty']
                ];
            } else {
                $result[] = [
                    'name'     => $date->format($displayFormat),
                    'inbound'  => 0,
                    'outbound' => 0
                ];
            }
        }

        return $result;
    }

    protected function logChange($whId, $varId, $oldQty, $newQty, $delta, $type, $reason = null)
    {
        $this->repository->logChange([
            'warehouse_id'       => $whId,
            'product_variant_id' => $varId,
            'user_id'            => Auth::id(),
            'previous_quantity'  => $oldQty,
            'new_quantity'       => $newQty,
            'quantity_change'    => $delta,
            'type'               => $type,
            'reason'             => $reason
        ]);
    }

    protected function checkLowStock(InventoryStock $stock, int $newQty): void
    {
        $threshold = $stock->min_threshold ?? 5;
        if ($newQty <= $threshold) {
            $stock->loadMissing(['variant.product', 'warehouse']);
            event(new LowStockDetected($stock->variant, $stock->warehouse, $newQty));
        }
    }
}