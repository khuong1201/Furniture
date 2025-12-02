<?php

namespace Modules\Dashboard\Services;

use Modules\Order\Domain\Models\Order;
use Modules\User\Domain\Models\User;
use Modules\Product\Domain\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    public function getSummaryStats(): array
    {
        $today = Carbon::today();

        return [
            'revenue_today' => Order::whereDate('created_at', $today)->where('status', 'completed')->sum('total_amount'),
            'orders_today' => Order::whereDate('created_at', $today)->count(),
            'total_users' => User::count(),
            'low_stock_products' => Product::where('status', true)->whereHas('warehouses', function($q) {
                $q->where('quantity', '<', 10);
            })->count()
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
        ->where('status', 'completed')
        ->groupBy('month')
        ->orderBy('month')
        ->get();
    }
}