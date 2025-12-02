<?php

namespace Modules\Notification\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Notification\Services\NotificationService;
use Illuminate\Notifications\DatabaseNotification;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Notifications",
    description: "API quản lý thông báo (User)"
)]

class NotificationController extends BaseController
{
    public function __construct(NotificationService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/notifications",
        summary: "Lấy danh sách thông báo",
        security: [['bearerAuth' => []]],
        tags: ["Notifications"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        
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

    #[OA\Patch(
        path: "/notifications/{uuid}/read",
        summary: "Đánh dấu đã đọc 1 thông báo",
        security: [['bearerAuth' => []]],
        tags: ["Notifications"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [ new OA\Response(response: 200, description: "Marked as read") ]
    )]

    public function read(string $uuid): JsonResponse
    {

        $notification = $this->service->findByUuidOrFail($uuid);

        $this->authorize('update', $notification);

        $this->service->markAsRead($uuid, auth()->id());
        
        return response()->json(ApiResponse::success(null, 'Marked as read'));
    }

    #[OA\Post(
        path: "/notifications/read-all",
        summary: "Đánh dấu đã đọc tất cả",
        security: [['bearerAuth' => []]],
        tags: ["Notifications"],
        responses: [ new OA\Response(response: 200, description: "All read") ]
    )]
    public function readAll(): JsonResponse
    {
        $this->service->markAllAsRead(auth()->id());
        
        return response()->json(ApiResponse::success(null, 'All marked as read'));
    }

    #[OA\Delete(
        path: "/notifications/{uuid}",
        summary: "Xóa thông báo",
        security: [['bearerAuth' => []]],
        tags: ["Notifications"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [ new OA\Response(response: 200, description: "Deleted") ]
    )]

    public function destroy(string $uuid): JsonResponse
    {
        $notification = $this->service->findByUuidOrFail($uuid);
        
        $this->authorize('delete', $notification);
        
        $this->service->delete($uuid);
        
        return response()->json(ApiResponse::success(null, 'Deleted successfully'));
    }
}