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
        return [
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'total_orders' => Order::count(),
            'total_users' => User::count(),
            'total_products' => \Modules\Product\Domain\Models\Product::count()
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
                'products.price', // Added price for display
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->groupBy('products.id', 'products.name', 'products.uuid', 'products.price')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }

    public function getOrderStatusDistribution()
    {
        return Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
    }

    public function getCategorySales()
    {
        return DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(order_items.subtotal) as revenue'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();
    }

    public function getTopCustomers(int $limit = 5)
    {
        return Order::select('users.name', 'users.email', DB::raw('SUM(orders.total_amount) as total_spent'), DB::raw('COUNT(orders.id) as order_count'))
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('orders.payment_status', 'paid')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();
    }
}