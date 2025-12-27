<?php

declare(strict_types=1);

namespace Modules\Media\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Media\Http\Requests\StoreMediaRequest;
use Modules\Media\Services\MediaService;
use Modules\Media\Domain\Models\Media;
use Modules\Shared\Http\Controllers\BaseController;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Media", description: "API quản lý File/Ảnh hệ thống")]
class MediaController extends BaseController
{
    public function __construct(protected MediaService $mediaService)
    {
        parent::__construct($mediaService);
    }

    #[OA\Post(
        path: "/api/admin/media",
        summary: "Upload file",
        description: "Upload file gắn vào User hiện tại (Mặc định).",
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
        responses: [
            new OA\Response(
                response: 200,
                description: "Upload thành công",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "object")
                ])
            )
        ]
    )]
    public function store(StoreMediaRequest $request): JsonResponse
    {
        $this->authorize('create', Media::class);

        $media = $this->mediaService->createMedia(
            $request->user(),
            $request->file('file'),
            $request->input('collection', 'default')
        );

        return $this->successResponse($media, 'File uploaded successfully', 201);
    }

    #[OA\Delete(
        path: "/api/admin/media/{uuid}",
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
        $this->mediaService->deleteMedia($uuid);
        
        return $this->successResponse(null, 'File deleted successfully');
    }
}