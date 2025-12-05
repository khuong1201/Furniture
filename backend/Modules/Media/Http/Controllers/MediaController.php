<?php

declare(strict_types=1);

namespace Modules\Media\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Media\Services\MediaService;
use Modules\Media\Domain\Models\Media;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Media", description: "API quản lý File/Ảnh hệ thống")]
class MediaController extends BaseController
{
    public function __construct(protected MediaService $mediaService)
    {
        //
    }

    #[OA\Post(
        path: "/admin/media",
        summary: "Upload file (Generic)",
        description: "Upload file gắn vào User hiện tại (Ví dụ: Avatar tạm).",
        security: [['bearerAuth' => []]],
        tags: ["Media"],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(properties: [
                    new OA\Property(property: "file", type: "string", format: "binary"),
                    new OA\Property(property: "collection", type: "string", default: "default"),
                ])
            )
        ),
        responses: [new OA\Response(response: 201, description: "Uploaded")]
    )]
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Media::class); 

        $request->validate([
            'file' => 'required|file|max:5120|mimes:jpeg,png,jpg,gif,pdf,doc,docx', 
            'collection' => 'nullable|string|max:50'
        ]);

        // Gọi method createMedia (vừa refactor) thay vì upload cũ
        $media = $this->mediaService->createMedia(
            $request->user(), 
            $request->file('file'), 
            $request->input('collection', 'default')
        );

        return response()->json(ApiResponse::success($media, 'File uploaded successfully', 201), 201);
    }

    #[OA\Delete(
        path: "/admin/media/{uuid}",
        summary: "Xóa file/ảnh",
        security: [['bearerAuth' => []]],
        tags: ["Media"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [new OA\Response(response: 200, description: "Deleted")]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $media = $this->mediaService->findByUuidOrFail($uuid);

        $this->authorize('delete', $media);

        // Gọi method deleteMedia (xóa cả DB và File)
        $this->mediaService->deleteMedia($uuid);
        
        return response()->json(ApiResponse::success(null, 'File deleted successfully'));
    }
}