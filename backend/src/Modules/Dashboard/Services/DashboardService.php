<?php

declare(strict_types=1);

namespace Modules\Dashboard\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Domain\Models\InventoryStock;
use Modules\Order\Domain\Models\Order;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Enums\PaymentStatus;
use Modules\Shared\Exceptions\BusinessException;
use Modules\User\Domain\Models\User;

class DashboardService
{
    /**
     * Lấy thống kê Cards (Total Revenue, Orders...) với so sánh thời gian tương đối
     */
    public function getSummaryStats(string $range = 'month', ?int $month = null, ?int $year = null): array
    {
        try {
            $now = Carbon::now();
            $targetYear = $year ?? $now->year;
            
            // Xử lý Logic Thời Gian (Start/End Current vs Start/End Previous)
            switch ($range) {
                case 'today':
                    $startCurrent  = $now->copy()->startOfDay();
                    $endCurrent    = $now; 
                    
                    $startPrevious = $now->copy()->subDay()->startOfDay();
                    $endPrevious   = $now->copy()->subDay();
                    $label = 'vs yesterday';
                    break;

                case 'week':
                    $startCurrent  = $now->copy()->startOfWeek();
                    $endCurrent    = $now;
                    
                    $startPrevious = $now->copy()->subWeek()->startOfWeek();
                    $endPrevious   = $now->copy()->subWeek();
                    $label = 'vs last week';
                    break;

                case 'year':
                    $targetDate = Carbon::createFromDate($targetYear, 1, 1);
                    $isCurrentYear = ($targetYear === $now->year);

                    $startCurrent = $targetDate->copy()->startOfYear();

                    if ($isCurrentYear) {
                        $endCurrent = $now;
                        $startPrevious = $now->copy()->subYear()->startOfYear();
                        $endPrevious   = $now->copy()->subYear();
                        $label = 'vs last year';
                    } else {
                        $endCurrent = $targetDate->copy()->endOfYear();
                        $startPrevious = $targetDate->copy()->subYear()->startOfYear();
                        $endPrevious   = $targetDate->copy()->subYear()->endOfYear();
                        $label = 'vs previous year';
                    }
                    break;

                case 'month':
                default:
                    $targetMonth = $month ?? $now->month;
                    $targetDate  = Carbon::createFromDate($targetYear, $targetMonth, 1);
                    $isCurrentMonth = ($targetYear === $now->year && $targetMonth === $now->month);

                    $startCurrent = $targetDate->copy()->startOfMonth();

                    if ($isCurrentMonth) {
                        $endCurrent = $now; 
                        $startPrevious = $now->copy()->subMonth()->startOfMonth();
                        $endPrevious   = $now->copy()->subMonth(); 
                        $label = 'vs last month';
                    } else {
                        $endCurrent = $targetDate->copy()->endOfMonth();
                        $startPrevious = $targetDate->copy()->subMonth()->startOfMonth();
                        $endPrevious   = $targetDate->copy()->subMonth()->endOfMonth();
                        $label = 'vs previous month';
                    }
                    break;
            }

            // QUERY DB
            $revenueCurrent = $this->queryRevenue($startCurrent, $range === 'today' ? $now : $endCurrent);
            $revenuePrevious = $this->queryRevenue($startPrevious, $endPrevious);
            $revenueGrowth = $this->calculateGrowth((float)$revenueCurrent, (float)$revenuePrevious);

            $ordersCurrent = $this->queryOrderCount($startCurrent, $range === 'today' ? $now : $endCurrent);
            $ordersPrevious = $this->queryOrderCount($startPrevious, $endPrevious);
            $orderGrowth = $this->calculateGrowth((float)$ordersCurrent, (float)$ordersPrevious);

            $totalUsers = User::count();
            $newUsersPeriod = User::whereBetween('created_at', [$startCurrent, $range === 'today' ? $now : $endCurrent])->count();
            
            $lowStockVariants = 0;
            try { $lowStockVariants = InventoryStock::whereColumn('quantity', '<=', 'min_threshold')->count(); } catch (\Exception $e) {}

            return [
                'total_revenue'    => (float)$revenueCurrent,
                'revenue_previous' => (float)$revenuePrevious,
                'revenue_growth'   => $revenueGrowth,
                'total_orders'     => $ordersCurrent,
                'orders_previous'  => $ordersPrevious,
                'order_growth'     => $orderGrowth,
                'total_users'      => $totalUsers,
                'new_users_period' => $newUsersPeriod,
                'low_stock_variants' => $lowStockVariants,
                'compare_label'    => $label,
                'filter_range'     => $range
            ];

        } catch (\Exception $e) {
            Log::error("Dashboard Stats Error: " . $e->getMessage());
            throw new BusinessException(500080); 
        }
    }

    /**
     * Lấy biểu đồ doanh thu theo Unit (Day/Week/Month)
     * @param string $unit 'day', 'week', 'month'
     */
    public function getRevenueChart(string $unit = 'day', int $year = 2024, int $month = 1): Collection
    {
        $query = Order::where('payment_status', PaymentStatus::PAID)
            ->where('status', '!=', OrderStatus::CANCELLED);

        // --- CASE 1: DAY (Hiện 7 ngày trong tuần - English Labels) ---
        // Dùng khi filter Range = Week/Today
        if ($unit === 'day') {
            $start = Carbon::now()->startOfWeek();
            $end   = Carbon::now()->endOfWeek();

            $data = $query->whereBetween('ordered_at', [$start, $end])
                ->select(DB::raw('DATE(ordered_at) as date'), DB::raw('SUM(grand_total) as value'))
                ->groupBy('date')->get()->keyBy('date');

            $stats = collect();
            for ($i = 0; $i < 7; $i++) {
                $date = $start->copy()->addDays($i)->format('Y-m-d');
                $stats->push([
                    'date'  => $date,
                    'label' => Carbon::parse($date)->format('l'), // Monday, Tuesday...
                    'value' => (float) ($data[$date]->value ?? 0)
                ]);
            }
            return $stats;
        }

        // --- CASE 2: WEEK (Hiện các tuần trong tháng) ---
        // Dùng khi filter Range = Month
        if ($unit === 'week') {
            $targetDate = Carbon::createFromDate($year, $month, 1);
            $start = $targetDate->copy()->startOfMonth();
            $end   = $targetDate->copy()->endOfMonth();

            $orders = $query->whereBetween('ordered_at', [$start, $end])
                ->select('ordered_at', 'grand_total')
                ->get();

            // Khởi tạo 4 tuần với giá trị 0
            $weeklyData = [
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0
            ];

            foreach ($orders as $order) {
                $day = Carbon::parse($order->ordered_at)->day;
                
                // Logic chia 4 khoảng (Bucket)
                if ($day <= 7) {
                    $weekNum = 1;
                } elseif ($day <= 14) {
                    $weekNum = 2;
                } elseif ($day <= 21) {
                    $weekNum = 3;
                } else {
                    $weekNum = 4; // Từ ngày 22 đến hết tháng
                }

                $weeklyData[$weekNum] += $order->grand_total;
            }

            $stats = collect();
            // Loop đúng 4 lần
            for ($i = 1; $i <= 4; $i++) {
                // Tạo label chi tiết để user hiểu (VD: Week 1 (1-7))
                $label = match($i) {
                    1 => "Week 1",
                    2 => "Week 2",
                    3 => "Week 3",
                    4 => "Week 4",
                };

                $stats->push([
                    'week_num' => $i,
                    'label'    => $label,
                    'value'    => (float) ($weeklyData[$i] ?? 0)
                ]);
            }
            return $stats;
        }

        // --- CASE 3: MONTH (Hiện 12 tháng) ---
        // Dùng khi filter Range = Year
        $data = $query->whereYear('ordered_at', $year)
            ->select(DB::raw('MONTH(ordered_at) as month_num'), DB::raw('SUM(grand_total) as value'))
            ->groupBy('month_num')->get()->keyBy('month_num');

        $stats = collect();
        for ($i = 1; $i <= 12; $i++) {
            $stats->push([
                'month_num' => $i,
                'label'     => Carbon::create()->month($i)->format('M'), // Jan, Feb...
                'value'     => (float) ($data[$i]->value ?? 0)
            ]);
        }
        return $stats;
    }

    // --- Helpers ---
    private function queryRevenue($start, $end) {
        return Order::whereBetween('ordered_at', [$start, $end])
                ->where('payment_status', PaymentStatus::PAID)
                ->where('status', '!=', OrderStatus::CANCELLED)->sum('grand_total');
    }

    private function queryOrderCount($start, $end) {
        return Order::whereBetween('ordered_at', [$start, $end])
                ->where('status', '!=', OrderStatus::CANCELLED)->count();
    }

    private function calculateGrowth(float $current, float $previous): float {
        if ($previous > 0) return round((($current - $previous) / $previous) * 100, 2);
        return $current > 0 ? 100 : 0;
    }

    // --- Other Methods (No changes needed, included for completeness) ---
    public function getCategorySales(): Collection {
        try {
            return DB::table('order_items')
                ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
                ->join('products', 'product_variants.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', '!=', OrderStatus::CANCELLED->value)
                ->where('orders.payment_status', '=', PaymentStatus::PAID->value)
                ->whereNull('orders.deleted_at')
                ->select('categories.name', DB::raw('SUM(order_items.subtotal) as value'))
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('value')->limit(5)->get();
        } catch (\Exception $e) { return collect([]); }
    }

    public function getCustomerStats(): array {
        $new = User::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        $total = User::count();
        return ['total' => $total, 'new' => $new, 'returning' => max(0, $total - $new), 'new_percentage' => $total > 0 ? round(($new/$total)*100,1) : 0];
    }

    public function getTopSpenders(int $limit = 5): Collection {
        return Order::select('user_id', DB::raw('SUM(grand_total) as total_spent'))
            ->where('payment_status', PaymentStatus::PAID)->where('status', '!=', OrderStatus::CANCELLED)
            ->with('user:id,name,email,avatar_url')->groupBy('user_id')->orderByDesc('total_spent')->limit($limit)->get();
    }

    public function getTopSellingProducts(int $limit = 5): Collection {
        return DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->leftJoin('product_images', function($join) {
                $join->on('products.id', '=', 'product_images.product_id')->where('product_images.is_primary', '=', 1);
            })
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', OrderStatus::CANCELLED->value)->where('orders.payment_status', '=', PaymentStatus::PAID->value)
            ->whereNull('orders.deleted_at')->whereNull('products.deleted_at')
            ->select('products.id', 'products.name', 'product_images.image_url', DB::raw('SUM(order_items.quantity) as total_sold'), DB::raw('SUM(order_items.subtotal) as total_revenue'))
            ->groupBy('products.id', 'products.name', 'product_images.image_url')->orderByDesc('total_sold')->limit($limit)->get();
    }
}