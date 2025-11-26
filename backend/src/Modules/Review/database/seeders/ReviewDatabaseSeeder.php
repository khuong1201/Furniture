<?php

namespace Modules\Review\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Review\Domain\Models\Review;
use Modules\User\Domain\Models\User;
use Modules\Product\Domain\Models\Product;

class ReviewDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(3)->get();
        $products = Product::take(5)->get();

        foreach ($products as $product) {
            foreach ($users as $user) {
                Review::factory()->create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ]);
            }
        }
    }
}
