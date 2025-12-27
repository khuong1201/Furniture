<?php

declare(strict_types=1);

namespace Modules\Log\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Log\Services\LogService;
use Modules\Shared\Http\Controllers\BaseController;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "System Logs",
    description: "Quản lý nhật ký hoạt động hệ thống (Audit & Error Logs)"
)]
class LogController extends BaseController
{
    public function __construct(LogService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/api/admin/logs",
        summary: "Lấy danh sách Logs (Có lọc & Phân trang)",
        security: [['bearerAuth' => []]],
        tags: ["System Logs"],
        parameters: [
            new OA\Parameter(
                name: "page",
                in: "query",
                description: "Số trang hiện tại",
                schema: new OA\Schema(type: "integer", default: 1)
            ),
            new OA\Parameter(
                name: "per_page",
                in: "query",
                description: "Số lượng bản ghi mỗi trang",
                schema: new OA\Schema(type: "integer", default: 20)
            ),
            new OA\Parameter(
                name: "type",
                in: "query",
                description: "Loại log (audit, system_error)",
                schema: new OA\Schema(type: "string", enum: ["audit", "system_error"])
            ),
            new OA\Parameter(
                name: "action",
                in: "query",
                description: "Hành động (created, updated, deleted, exception...)",
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "user_id",
                in: "query",
                description: "ID người dùng thực hiện",
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "model",
                in: "query",
                description: "Tên Model (VD: App\Models\Product)",
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "model_uuid",
                in: "query",
                description: "UUID của đối tượng bị tác động",
                schema: new OA\Schema(type: "string", format: "uuid")
            ),
            new OA\Parameter(
                name: "date_from",
                in: "query",
                description: "Lọc từ ngày (YYYY-MM-DD)",
                schema: new OA\Schema(type: "string", format: "date")
            ),
            new OA\Parameter(
                name: "date_to",
                in: "query",
                description: "Lọc đến ngày (YYYY-MM-DD)",
                schema: new OA\Schema(type: "string", format: "date")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Thành công",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Success"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "uuid", type: "string", format: "uuid"),
                                    new OA\Property(property: "type", type: "string"),
                                    new OA\Property(property: "action", type: "string"),
                                    new OA\Property(property: "message", type: "string"),
                                    new OA\Property(property: "created_at", type: "string", format: "date-time"),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "meta",
                            properties: [
                                new OA\Property(property: "current_page", type: "integer"),
                                new OA\Property(property: "total", type: "integer"),
                                new OA\Property(property: "per_page", type: "integer"),
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Chưa đăng nhập"),
            new OA\Response(response: 403, description: "Không có quyền truy cập"),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Log::class); 

        $filters = $request->only(['type', 'action', 'user_id', 'model', 'model_uuid', 'date_from', 'date_to']);
        $perPage = $request->integer('per_page', 20);

        $perPage = ($perPage > 100 || $perPage < 1) ? 20 : $perPage;

        $logs = $this->service->getLogs($filters, $perPage);

        return $this->successResponse($logs);
    }

    #[OA\Get(
        path: "/api/admin/logs/{uuid}",
        summary: "Xem chi tiết một Log",
        security: [['bearerAuth' => []]],
        tags: ["System Logs"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                in: "path",
                required: true,
                description: "UUID của Log",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Thành công",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "uuid", type: "string"),
                                new OA\Property(property: "message", type: "string"),
                                new OA\Property(property: "metadata", type: "object", description: "Chi tiết thay đổi hoặc lỗi"),
                                new OA\Property(property: "ip_address", type: "string"),
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Không tìm thấy Log"),
        ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $log = $this->service->findByUuidOrFail($uuid);
        
        $this->authorize('view', $log);

        return $this->successResponse($log);
    }
}