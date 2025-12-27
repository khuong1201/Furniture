<?php

declare(strict_types=1);

namespace Modules\Notification\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Notification\Services\NotificationService;
use Modules\Notification\Domain\Models\Notification;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Notifications", description: "API quản lý thông báo cá nhân")]
class NotificationController extends BaseController
{
    public function __construct(protected NotificationService $notificationService)
    {
        parent::__construct($notificationService);
    }

    #[OA\Get(
        path: "/api/notifications",
        summary: "Lấy danh sách thông báo",
        security: [['bearerAuth' => []]],
        tags: ["Notifications"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "array", items: new OA\Items()),
                    new OA\Property(property: "meta", type: "object", properties: [
                        new OA\Property(property: "unread_count", type: "integer", example: 5)
                    ])
                ])
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $result = $this->notificationService->getNotificationsWithUnreadCount(
            $request->user()->id, 
            $request->integer('per_page', 15)
        );

        // Custom response: Kết hợp Paginator chuẩn + unread_count
        // Ta lấy data từ Paginator ra để wrap lại
        $paginator = $result['paginator'];
        
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách thông báo thành công',
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'unread_count' => $result['unread_count'] // <--- Extra field quan trọng
            ]
        ]);
    }

    #[OA\Patch(
        path: "/api/notifications/{uuid}/read",
        summary: "Đánh dấu đã đọc 1 thông báo",
        security: [['bearerAuth' => []]],
        tags: ["Notifications"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function read(string $uuid): JsonResponse
    {
        $notification = $this->notificationService->findByUuidOrFail($uuid);
        $this->authorize('update', $notification); // Policy check: User sở hữu noti này

        $this->notificationService->markAsRead($uuid);
        
        return $this->successResponse(null, 'Đã đánh dấu đã đọc');
    }

    #[OA\Post(
        path: "/api/notifications/read-all",
        summary: "Đánh dấu đã đọc tất cả",
        security: [['bearerAuth' => []]],
        tags: ["Notifications"],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function readAll(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user()->id);
        return $this->successResponse(null, 'Đã đánh dấu tất cả là đã đọc');
    }

    #[OA\Delete(
        path: "/api/notifications/{uuid}",
        summary: "Xóa thông báo",
        security: [['bearerAuth' => []]],
        tags: ["Notifications"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [new OA\Response(response: 200, description: "Deleted")]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $notification = $this->notificationService->findByUuidOrFail($uuid);
        $this->authorize('delete', $notification);
        
        $this->notificationService->delete($uuid);
        return $this->successResponse(null, 'Xóa thông báo thành công');
    }
}