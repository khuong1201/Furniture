<?php

namespace Modules\Log\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Log\Services\LogService;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "System Logs",
    description: "API xem nhật ký hệ thống (Admin Only)"
)]

class LogController extends BaseController
{
    public function __construct(LogService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/admin/logs",
        summary: "Xem danh sách Log",
        security: [['bearerAuth' => []]],
        tags: ["System Logs"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "level", in: "query", description: "error, info, warning...", schema: new OA\Schema(type: "string")),
        ],
        responses: [ 
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \Modules\Log\Domain\Models\Log::class);

        $filters = $request->all();
        $logs = $this->service->paginate($request->get('per_page', 20), $filters);

        return response()->json(ApiResponse::paginated($logs));
    }

    #[OA\Get(
        path: "/admin/logs/{uuid}",
        summary: "Xem chi tiết Log",
        security: [['bearerAuth' => []]],
        tags: ["System Logs"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]

    public function show(string $uuid): JsonResponse
    {
        $log = $this->service->findByUuidOrFail($uuid);
        
        $this->authorize('view', $log);
        
        return response()->json(ApiResponse::success($log));
    }
}