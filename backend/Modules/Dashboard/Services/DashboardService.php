<?php

namespace Modules\Dashboard\Services;

use Modules\Order\Domain\Models\Order;
use Modules\User\Domain\Models\User;
use Modules\Inventory\Domain\Models\InventoryStock; 
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{

    public function getSummaryStats(): array
    {
        $today = Carbon::today();

        return [
            'revenue_today' => Order::whereDate('created_at', $today)
                ->where('payment_status', 'paid')
                ->sum('total_amount'),

            'orders_today' => Order::whereDate('created_at', $today)->count(),
            
            'total_users' => User::count(),
            
            'low_stock_variants' => InventoryStock::where('quantity', '<', 10)->count()
        ];
    }

    public function getRevenueChart(int $year)
    {
        return Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total_amount) as revenue'),
            DB::raw('COUNT(id) as total_orders')
        )
        ->whereYear('created_at', $year)
        ->where('payment_status', 'paid') 
        ->groupBy('month')
        ->orderBy('month')
        ->get();
    }

    public function getTopSellingProducts(int $limit = 5)
    {
        return DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select(
                'products.name',
                'products.uuid',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->groupBy('products.id', 'products.name', 'products.uuid')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }
}