<?php

namespace Modules\Log\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Log\Services\LogService;
use Illuminate\Http\JsonResponse;
class LogController extends BaseController
{
    public function __construct(LogService $service)
    {
        parent::__construct($service);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $logs = $this->service->getLogs($filters);

        return response()->json(ApiResponse::paginated($logs));
    }

    public function show(string $uuid): JsonResponse
    {
        $log = $this->service->findByUuidOrFail($uuid);
        return response()->json(ApiResponse::success($log));
    }
}