<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Role\database\seeders\RolePermissionSeeder;

use Modules\User\Domain\Models\User;
use Modules\Role\Domain\Models\Role;
use Modules\Category\Domain\Models\Category;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Domain\Models\ProductVariant; 
use Modules\Product\Domain\Models\Attribute;
use Modules\Product\Domain\Models\AttributeValue;
use Modules\Product\Domain\Models\ProductImage;
use Modules\Warehouse\Domain\Models\Warehouse;
use Modules\Inventory\Domain\Models\InventoryStock; 
use Modules\Address\Domain\Models\Address;
use Modules\Collection\Domain\Models\Collection;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dọn dẹp DB
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $tables = [
            'users', 'roles', 'permissions', 'model_has_roles', 'permission_role',
            'categories', 'products', 'product_variants', 'product_images', 
            'attributes', 'attribute_values', 'variant_attribute_values',
            'warehouses', 'inventory_stocks', 
            'orders', 'order_items', 'carts', 'cart_items', 
            'addresses', 'collections', 'collection_product', 'shippings', 'payments'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "🚀 Starting System Seeding...\n";

        $this->call(RolePermissionSeeder::class);

        $admin = User::create([
            'uuid' => Str::uuid(), 'name' => 'Super Admin', 'email' => 'admin@system.com',
            'password' => bcrypt('123456'), 'is_active' => true
        ]);
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) $admin->roles()->sync([$adminRole->id]);

        $customer = User::create([
            'uuid' => Str::uuid(), 'name' => 'Test Customer', 'email' => 'customer@test.com',
            'password' => bcrypt('123456'), 'is_active' => true
        ]);
        $customerRole = Role::where('name', 'customer')->first();
        if ($customerRole) $customer->roles()->sync([$customerRole->id]);

        // Address
        if (Schema::hasTable('addresses')) {
            Address::create(['uuid' => Str::uuid(), 'user_id' => $customer->id, 'full_name' => 'Khách Hàng A', 'phone' => '0909123456', 'province' => 'Hà Nội', 'district' => 'Cầu Giấy', 'ward' => 'Dịch Vọng', 'street' => '123 Xuân Thủy', 'is_default' => true]);
        }

        // 4. Warehouse
        echo "🏭 Creating Warehouses...\n";
        $whHN = Warehouse::create(['uuid' => Str::uuid(), 'name' => 'Kho Hà Nội', 'location' => 'Hà Nội', 'manager_id' => $admin->id]);
        $whHCM = Warehouse::create(['uuid' => Str::uuid(), 'name' => 'Kho HCM', 'location' => 'Hồ Chí Minh', 'manager_id' => $admin->id]);

        // 5. Categories
        echo "📂 Creating Categories...\n";
        $catFashion = Category::create(['uuid' => Str::uuid(), 'name' => 'Thời trang', 'slug' => 'thoi-trang']);
        $catElec = Category::create(['uuid' => Str::uuid(), 'name' => 'Điện tử', 'slug' => 'dien-tu']);

        // 6. Attributes (Mới)
        echo "🎨 Creating Attributes...\n";
        $attrColor = Attribute::create(['uuid' => Str::uuid(), 'name' => 'Màu sắc', 'slug' => 'color', 'type' => 'color']);
        $valRed = $attrColor->values()->create(['uuid' => Str::uuid(), 'value' => 'Đỏ', 'code' => '#FF0000']);
        $valBlue = $attrColor->values()->create(['uuid' => Str::uuid(), 'value' => 'Xanh', 'code' => '#0000FF']);

        $attrSize = Attribute::create(['uuid' => Str::uuid(), 'name' => 'Kích thước', 'slug' => 'size', 'type' => 'select']);
        $valS = $attrSize->values()->create(['uuid' => Str::uuid(), 'value' => 'S']);
        $valM = $attrSize->values()->create(['uuid' => Str::uuid(), 'value' => 'M']);

        echo "📦 Creating Products & Inventory...\n";

        $tshirt = Product::create([
            'uuid' => Str::uuid(),
            'name' => 'Áo Thun Basic',
            'category_id' => $catFashion->id,
            'has_variants' => true, 
            'is_active' => true
        ]);

        $var1 = ProductVariant::create([
            'uuid' => Str::uuid(), 'product_id' => $tshirt->id,
            'sku' => 'TSHIRT-RED-S', 'price' => 100000, 'weight' => 0.2
        ]);
        $var1->attributeValues()->sync([$valRed->id, $valS->id]);
        InventoryStock::create(['uuid' => Str::uuid(), 'warehouse_id' => $whHN->id, 'product_variant_id' => $var1->id, 'quantity' => 50, 'min_threshold' => 5]);
        $var2 = ProductVariant::create([
            'uuid' => Str::uuid(), 'product_id' => $tshirt->id,
            'sku' => 'TSHIRT-BLUE-M', 'price' => 120000, 'weight' => 0.25
        ]);
        $var2->attributeValues()->sync([$valBlue->id, $valM->id]);
        InventoryStock::create(['uuid' => Str::uuid(), 'warehouse_id' => $whHN->id, 'product_variant_id' => $var2->id, 'quantity' => 30, 'min_threshold' => 5]);


        $iphone = Product::create([
            'uuid' => Str::uuid(),
            'name' => 'iPhone 15 Pro Max',
            'category_id' => $catElec->id,
            'has_variants' => false,
            'price' => 30000000, 
            'sku' => 'IP15PM',
            'is_active' => true
        ]);
        $iphoneVar = ProductVariant::create([
            'uuid' => Str::uuid(), 'product_id' => $iphone->id,
            'sku' => 'IP15PM', 'price' => 30000000, 'weight' => 0.5
        ]);
        
        InventoryStock::create([
            'uuid' => Str::uuid(),
            'warehouse_id' => $whHN->id,
            'product_variant_id' => $iphoneVar->id,
            'quantity' => 10,
            'min_threshold' => 2
        ]);

        if (Schema::hasTable('collections')) {
            $colFlashSale = Collection::create(['uuid' => Str::uuid(), 'name' => 'Flash Sale', 'slug' => 'flash-sale', 'is_active' => true]);
            $colFlashSale->products()->attach($tshirt->id);
        }

        echo "✅ SEEDING COMPLETE! \n";
        echo "Admin: admin@system.com / 123456 \n";
        echo "Customer: customer@test.com / 123456 \n";
    }
}