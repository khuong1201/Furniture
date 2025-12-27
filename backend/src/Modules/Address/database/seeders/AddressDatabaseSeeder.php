<?php

namespace Modules\Address\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Address\Domain\Models\Address;
use Modules\User\Domain\Models\User;

class AddressDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo địa chỉ cố định cho khách mẫu
        $sampleCustomer = User::where('email', 'customer@gmail.com')->first();
        if ($sampleCustomer) {
            Address::firstOrCreate(['user_id' => $sampleCustomer->id, 'type' => 'home'], [
                'full_name' => 'Nguyen Van A',
                'phone' => '0901234567',
                'province' => 'Hồ Chí Minh',
                'district' => 'Quận 1',
                'ward' => 'Phường Bến Nghé',
                'street' => '123 Đường Lê Lợi',
                'is_default' => true,
            ]);
        }

        // 2. Quét toàn bộ User là customer chưa có địa chỉ -> Tạo fake
        $customers = User::whereHas('roles', function ($q) {
            $q->where('name', 'customer');
        })->get();

        foreach ($customers as $customer) {
            // Kiểm tra nếu chưa có address nào thì tạo
            if ($customer->addresses()->count() == 0) {
                Address::factory()->create([
                    'user_id' => $customer->id,
                    'is_default' => true, // Bắt buộc set default để dễ lấy
                    'full_name' => $customer->name,
                ]);
            }
        }
    }
}