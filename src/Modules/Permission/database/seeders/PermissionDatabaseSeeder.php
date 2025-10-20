<?php

namespace Modules\Permission\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Permission\Models\Permission;

class PermissionDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'user.view', 'label' => 'Xem người dùng'],
            ['name' => 'user.create', 'label' => 'Tạo người dùng'],
            ['name' => 'user.edit', 'label' => 'Sửa người dùng'],
            ['name' => 'user.delete', 'label' => 'Xóa người dùng'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], ['label' => $perm['label']]);
        }
    }
}
