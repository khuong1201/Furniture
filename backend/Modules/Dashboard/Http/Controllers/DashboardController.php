<?php

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController; // Kế thừa BaseController cho đồng bộ
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Dashboard\Services\DashboardService;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Dashboard", description: "Báo cáo thống kê hệ thống (Admin Only)")]

class DashboardController extends BaseController
{
    public function __construct(protected DashboardService $dashboardService)
    {
    }

    #[OA\Get(
        path: "/admin/dashboard/summary",
        summary: "Thống kê tổng quan",
        security: [['bearerAuth' => []]],
        tags: ["Dashboard"],
        responses: [
            new OA\Response(response: 200, description: "Success", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "object")
            ]))
        ]
    )]
    public function summary(Request $request): JsonResponse
    {
        // Check permission thủ công
        if (!$request->user()->hasPermissionTo('dashboard.view')) {
            return response()->json(ApiResponse::error('Forbidden', 403), 403);
        }

        $data = $this->dashboardService->getSummaryStats();
        return response()->json(ApiResponse::success($data));
    }

    #[OA\Get(
        path: "/admin/dashboard/revenue",
        summary: "Biểu đồ doanh thu theo năm",
        security: [['bearerAuth' => []]],
        tags: ["Dashboard"],
        parameters: [
            new OA\Parameter(name: "year", in: "query", schema: new OA\Schema(type: "integer", example: 2025))
        ],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function revenue(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('dashboard.view')) {
            return response()->json(ApiResponse::error('Forbidden', 403), 403);
        }

        $year = $request->input('year', date('Y'));
        $data = $this->dashboardService->getRevenueChart($year);
        
        return response()->json(ApiResponse::success($data));
    }
}