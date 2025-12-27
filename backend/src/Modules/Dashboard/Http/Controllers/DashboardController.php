<?php

declare(strict_types=1);

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Dashboard\Services\DashboardService;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Exceptions\BusinessException;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Dashboard", description: "Báo cáo thống kê hệ thống (Admin Only)")]
class DashboardController extends BaseController
{
    public function __construct(protected DashboardService $dashboardService)
    {
    }

    #[OA\Get(
        path: "/api/admin/dashboard/summary",
        summary: "Lấy toàn bộ dữ liệu Dashboard",
        tags: ["Dashboard"],
        parameters: [
            new OA\Parameter(name: "range", in: "query", schema: new OA\Schema(type: "string", enum: ["today", "week", "month", "year"], default: "month")),
            new OA\Parameter(name: "year", in: "query", schema: new OA\Schema(type: "integer", example: 2025)),
            new OA\Parameter(name: "month", in: "query", schema: new OA\Schema(type: "integer", example: 12))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 422, description: "Validation Error")
        ]
    )]
    public function summary(Request $request): JsonResponse
    {
        // 1. Validation
        $validator = Validator::make($request->all(), [
            'range' => 'sometimes|in:today,week,month,year',
            'year'  => 'nullable|integer|digits:4|min:2020|max:' . (date('Y') + 1),
            'month' => 'nullable|integer|between:1,12',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422000, 422);
        }

        // 2. Lấy tham số
        $range = $request->input('range', 'month');
        $yearParam  = $request->filled('year') ? (int)$request->input('year') : (int)date('Y');
        $monthParam = $request->filled('month') ? (int)$request->input('month') : (int)date('m');

        // 3. LOGIC MAPPING: Quyết định Chart hiển thị theo đơn vị nào dựa trên Range
        // - Xem Năm (year) -> Chart chia theo Tháng (month)
        // - Xem Tháng (month) -> Chart chia theo Tuần (week)
        // - Xem Tuần/Hôm nay -> Chart chia theo Ngày (day)
        $chartUnit = match ($range) {
            'year'  => 'month',
            'month' => 'week', 
            default => 'day'
        };

        try {
            $data = [
                // Cards: Tính toán tăng trưởng
                'cards' => $this->dashboardService->getSummaryStats($range, $monthParam, $yearParam),
                
                // Chart: Dùng $chartUnit đã map ở trên
                'revenue_chart' => $this->dashboardService->getRevenueChart($chartUnit, $yearParam, $monthParam),
                
                // Các bảng xếp hạng
                'category_sales' => $this->dashboardService->getCategorySales(),
                'customer_stats' => $this->dashboardService->getCustomerStats(),
                'top_spenders'   => $this->dashboardService->getTopSpenders(5),
                'best_sellers'   => $this->dashboardService->getTopSellingProducts(5),
            ];

            return $this->successResponse($data);
        } catch (BusinessException $e) {
            return $this->errorResponse($e->getMessage(), $e->getErrorCode());
        } catch (\Throwable $e) {
            report($e);
            return $this->errorResponse('Lỗi server khi lấy dữ liệu dashboard', 500990);
        }
    }
}