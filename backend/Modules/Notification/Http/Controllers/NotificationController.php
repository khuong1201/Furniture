<?php

namespace Modules\Notification\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Notification\Services\NotificationService;
use Illuminate\Http\JsonResponse;
class NotificationController extends BaseController
{
    public function __construct(NotificationService $service)
    {
        parent::__construct($service);
    }

    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $result = $this->service->getMyNotifications($userId, $request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $result['items']->items(),
            'meta' => [
                'current_page' => $result['items']->currentPage(),
                'last_page' => $result['items']->lastPage(),
                'total' => $result['items']->total(),
                'unread_count' => $result['unread_count'] 
            ]
        ]);
    }

    public function read(string $uuid): JsonResponse
    {
        $this->service->markAsRead($uuid, auth()->id());
        return response()->json(ApiResponse::success(null, 'Marked as read'));
    }

    public function readAll(): JsonResponse
    {
        $this->service->markAllAsRead(auth()->id());
        return response()->json(ApiResponse::success(null, 'All marked as read'));
    }

    public function destroy(string $uuid): JsonResponse
    {
        $notification = $this->service->findByUuidOrFail($uuid);
        if ($notification->user_id !== auth()->id()) {
            return response()->json(ApiResponse::error('Unauthorized', 403), 403);
        }
        
        $this->service->delete($uuid);
        return response()->json(ApiResponse::success(null, 'Deleted successfully'));
    }
}