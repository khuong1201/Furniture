<?php

namespace Modules\Inventory\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Inventory\Domain\Models\InventoryStock;
use Modules\Inventory\Domain\Models\InventoryLog;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Warehouse\Domain\Models\Warehouse;

class InventoryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::all();
        $variants = ProductVariant::all();

        if ($warehouses->isEmpty() || $variants->isEmpty()) {
            $this->command->warn("⚠️ Missing Warehouses or Variants.");
            return;
        }

        // Reset dữ liệu cũ
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        InventoryLog::truncate();
        InventoryStock::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $initialDate = Carbon::create(2023, 1, 1);

        $stocksData = [];
        $logsData = [];

        foreach ($variants as $variant) {
            foreach ($warehouses as $warehouse) {
                
                // 1. Dùng Factory để tạo data chuẩn (nhưng chưa lưu DB)
                // Hàm make() chỉ tạo object trên RAM -> Rất nhanh
                $stockModel = InventoryStock::factory()
                    ->zero() // Gọi state zero để set quantity = 0
                    ->make([
                        'warehouse_id'       => $warehouse->id,
                        'product_variant_id' => $variant->id,
                        'created_at'         => $initialDate,
                        'updated_at'         => $initialDate,
                    ]);

                // Chuyển object thành mảng để chuẩn bị Bulk Insert
                $stocksData[] = $stockModel->getAttributes();

                // 2. Chuẩn bị Log (Log ít field nên build tay cho nhanh, hoặc làm Factory tương tự)
                $logsData[] = [
                    'warehouse_id'       => $warehouse->id,
                    'product_variant_id' => $variant->id,
                    'user_id'            => 1,
                    'previous_quantity'  => 0,
                    'new_quantity'       => 0,
                    'quantity_change'    => 0,
                    'type'               => 'adjustment',
                    'reason'             => 'System Initialization',
                    'created_at'         => $initialDate,
                    'updated_at'         => $initialDate,
                ];
            }
        }

        // Thực hiện Bulk Insert (Chia nhỏ mỗi lần 500 dòng)
        foreach (array_chunk($stocksData, 500) as $chunk) {
            InventoryStock::insert($chunk);
        }
        foreach (array_chunk($logsData, 500) as $chunk) {
            InventoryLog::insert($chunk);
        }
    }
}