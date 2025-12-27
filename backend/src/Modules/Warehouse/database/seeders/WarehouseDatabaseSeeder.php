<?php

namespace Modules\Warehouse\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Warehouse\Domain\Models\Warehouse;
use Illuminate\Support\Str;

class WarehouseDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'Kho Tổng HCM (Miền Nam)',
                'location' => 'Khu Chế Xuất Tân Thuận, Quận 7, TP.HCM',
                'contact_email' => 'kho.hcm@system.com',
                'contact_phone' => '02812345678',
            ],
            [
                'name' => 'Kho Hà Nội (Miền Bắc)',
                'location' => 'Khu Công Nghiệp Nam Thăng Long, Bắc Từ Liêm, Hà Nội',
                'contact_email' => 'kho.hn@system.com',
                'contact_phone' => '02412345678',
            ],
            [
                'name' => 'Kho Đà Nẵng (Miền Trung)',
                'location' => 'Khu Công Nghiệp Hòa Khánh, Liên Chiểu, Đà Nẵng',
                'contact_email' => 'kho.dn@system.com',
                'contact_phone' => '02361234567',
            ],
            [
                'name' => 'Kho Cần Thơ (Mê Kông)',
                'location' => 'Khu Công Nghiệp Trà Nóc, Bình Thủy, Cần Thơ',
                'contact_email' => 'kho.ct@system.com',
                'contact_phone' => '02921234567',
            ]
        ];

        foreach ($warehouses as $wh) {
            Warehouse::firstOrCreate(
                ['name' => $wh['name']], // Check trùng tên
                [
                    'uuid' => (string) Str::uuid(),
                    'location' => $wh['location'],
                    'contact_email' => $wh['contact_email'],
                    'contact_phone' => $wh['contact_phone'],
                    'is_active' => true,
                    'manager_id' => 1 // Gán tạm cho user ID 1 (Admin) nếu có
                ]
            );
        }
    }
}