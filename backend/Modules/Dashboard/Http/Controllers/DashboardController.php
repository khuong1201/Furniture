<?php

declare(strict_types=1);

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Dashboard\Services\DashboardService;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Dashboard", description: "Báo cáo thống kê hệ thống (Admin Only)")]
class DashboardController extends BaseController
{
    public function __construct(protected DashboardService $dashboardService)
    {
        // Không cần parent construct nếu không dùng base service
    }

    #[OA\Get(
        path: "/admin/dashboard/summary",
        summary: "Lấy toàn bộ dữ liệu Dashboard",
        description: "Trả về Cards, Revenue Chart, Customer Chart, Top Spenders, Best Sellers.",
        security: [['bearerAuth' => []]],
        tags: ["Dashboard"],
        parameters: [
            new OA\Parameter(
                name: "period", 
                in: "query", 
                schema: new OA\Schema(type: "string", enum: ["week", "year"], default: "week"),
                description: "Khoảng thời gian cho biểu đồ doanh thu"
            )
        ],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "object", properties: [
                        new OA\Property(property: "cards", type: "object", properties: [
                            new OA\Property(property: "total_revenue", type: "number"),
                            new OA\Property(property: "revenue_growth", type: "number"),
                            new OA\Property(property: "total_orders", type: "integer"),
                            new OA\Property(property: "total_users", type: "integer"),
                            new OA\Property(property: "low_stock_variants", type: "integer")
                        ]),
                        new OA\Property(property: "revenue_chart", type: "array", items: new OA\Items(properties: [
                             new OA\Property(property: "date", type: "string"),
                             new OA\Property(property: "label", type: "string"),
                             new OA\Property(property: "value", type: "number")
                        ])),
                        new OA\Property(property: "customer_stats", type: "object"),
                        new OA\Property(property: "top_spenders", type: "array", items: new OA\Items()),
                        new OA\Property(property: "best_sellers", type: "array", items: new OA\Items()),
                    ])
                ])
            ),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    
    public function summary(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('dashboard.view')) {
            return response()->json(ApiResponse::error('Forbidden', 403), 403);
        }

        $period = $request->input('period', 'week'); 

        // Cache dashboard data trong 5 phút để giảm tải DB nếu traffic cao
        // $cacheKey = "dashboard_summary_{$period}";
        // $data = cache()->remember($cacheKey, 300, function() use ($period) { ... });

        $data = [
            'cards'          => $this->dashboardService->getSummaryStats(),
            'revenue_chart'  => $this->dashboardService->getRevenueChart($period),
            'customer_stats' => $this->dashboardService->getCustomerStats(),
            'top_spenders'   => $this->dashboardService->getTopSpenders(5),
            'best_sellers'   => $this->dashboardService->getTopSellingProducts(5),
        ];

        return response()->json(ApiResponse::success($data));
    }
}