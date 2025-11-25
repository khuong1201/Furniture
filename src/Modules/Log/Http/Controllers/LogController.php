<?php
namespace Modules\Log\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Log\Services\LogService;

class LogController extends BaseController
{
    public function __construct(LogService $service)
    {
        parent::__construct($service);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['type', 'action', 'user_id', 'model', 'per_page']);
        $logs = $this->service->getLogs($filters);

        return response()->json($logs);
    }
}
