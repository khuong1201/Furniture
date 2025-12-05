<?php

declare(strict_types=1);

namespace Modules\Dashboard\Services;

use Modules\Order\Domain\Models\Order;
use Modules\User\Domain\Models\User;
use Modules\Inventory\Domain\Models\InventoryStock;
use Modules\Product\Domain\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Order\Enums\PaymentStatus; // Sử dụng Enum thay vì string cứng

class DashboardService
{
    public function getSummaryStats(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // Revenue
        $revenueThisMonth = Order::where('created_at', '>=', $startOfMonth)
            ->where('payment_status', PaymentStatus::PAID)
            ->sum('total_amount');
        
        $revenueLastMonth = Order::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->where('payment_status', PaymentStatus::PAID)
            ->sum('total_amount');

        $revenueGrowth = $revenueLastMonth > 0 
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 2)
            : ($revenueThisMonth > 0 ? 100 : 0);

        // Counts
        $totalOrders = Order::where('created_at', '>=', $startOfMonth)->count();
        $totalUsers = User::count();
        $lowStockVariants = InventoryStock::whereColumn('quantity', '<=', 'min_threshold')->count();

        return [
            'total_revenue' => (float)$revenueThisMonth,
            'revenue_growth' => $revenueGrowth,
            'total_orders' => $totalOrders,
            'total_users' => $totalUsers,
            'low_stock_variants' => $lowStockVariants
        ];
    }

    public function getRevenueChart(string $period = 'week'): \Illuminate\Support\Collection
    {
        $query = Order::where('payment_status', PaymentStatus::PAID);

        if ($period === 'week') {
            $start = Carbon::now()->startOfWeek();
            $end = Carbon::now()->endOfWeek();
            
            return $query->whereBetween('created_at', [$start, $end])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('DAYNAME(created_at) as label'), 
                    DB::raw('SUM(total_amount) as value')
                )
                ->groupBy('date', 'label')
                ->orderBy('date')
                ->get();
        } 

        // Year view: Group by month
        return $query->whereYear('created_at', date('Y'))
            ->select(
                DB::raw('MONTH(created_at) as month_num'),
                DB::raw('MONTHNAME(created_at) as label'), 
                DB::raw('SUM(total_amount) as value')
            )
            ->groupBy('month_num', 'label')
            ->orderBy('month_num')
            ->get();
    }

    public function getCustomerStats(): array
    {
        $totalCustomers = User::whereHas('roles', fn($q) => $q->where('name', 'customer'))->count();
        
        $newCustomers = User::whereHas('roles', fn($q) => $q->where('name', 'customer'))
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->count();

        $returningCustomers = max(0, $totalCustomers - $newCustomers);

        return [
            'total' => $totalCustomers,
            'new' => $newCustomers,
            'returning' => $returningCustomers,
            'new_percentage' => $totalCustomers > 0 ? round(($newCustomers / $totalCustomers) * 100, 1) : 0
        ];
    }

    public function getTopSpenders(int $limit = 5): \Illuminate\Support\Collection
    {
        return Order::select('user_id', DB::raw('SUM(total_amount) as total_spent'))
            ->where('payment_status', PaymentStatus::PAID)
            ->with('user:id,name,email,phone') 
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();
    }

    public function getTopSellingProducts(int $limit = 5): \Illuminate\Support\Collection
    {
        // Sử dụng Query Builder để join hiệu quả hơn Eloquent thuần cho báo cáo phức tạp
        return DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            // Left join ảnh để lấy ảnh đại diện (is_primary = 1)
            ->leftJoin('product_images', function($join) {
                $join->on('products.id', '=', 'product_images.product_id')
                     ->where('product_images.is_primary', '=', 1);
            })
            ->select(
                'products.id',
                'products.name',
                'products.uuid',
                'product_images.image_url',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.uuid', 'product_images.image_url')
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