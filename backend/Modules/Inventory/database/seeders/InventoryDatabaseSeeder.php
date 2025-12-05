<?php

namespace Modules\Inventory\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Warehouse\Domain\Models\Warehouse;
use Modules\Inventory\Domain\Models\InventoryStock;

class InventoryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Đảm bảo đã có kho
        $warehouseHN = Warehouse::firstOrCreate(
            ['name' => 'Kho Tổng Miền Bắc'],
            ['location' => 'Hà Nội', 'is_active' => true]
        );
        
        $warehouseHCM = Warehouse::firstOrCreate(
            ['name' => 'Kho Tổng Miền Nam'],
            ['location' => 'Hồ Chí Minh', 'is_active' => true]
        );

        $variants = ProductVariant::all();

        if ($variants->isEmpty()) {
            $this->command->info('No product variants found. Please run ProductDatabaseSeeder first.');
            return;
        }

        foreach ($variants as $variant) {
            // Random số lượng tồn kho
            $qtyHN = rand(0, 50);
            $qtyHCM = rand(0, 50);

            // Nếu cả 2 kho đều = 0 thì set 1 kho có hàng để tránh hết hàng toàn bộ
            if ($qtyHN == 0 && $qtyHCM == 0) $qtyHN = 10;

            // Kho HN
            if ($qtyHN > 0) {
                InventoryStock::updateOrCreate(
                    ['warehouse_id' => $warehouseHN->id, 'product_variant_id' => $variant->id],
                    ['quantity' => $qtyHN, 'min_threshold' => 5]
                );
            }

            // Kho HCM
            if ($qtyHCM > 0) {
                InventoryStock::updateOrCreate(
                    ['warehouse_id' => $warehouseHCM->id, 'product_variant_id' => $variant->id],
                    ['quantity' => $qtyHCM, 'min_threshold' => 5]
                );
            }
        }
    }
}