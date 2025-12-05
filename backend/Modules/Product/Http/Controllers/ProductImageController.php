<?php

declare(strict_types=1);

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Product\Services\ProductService;
use Modules\Product\Services\ProductImageService;
use Modules\Product\Http\Requests\StoreProductImageRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Product Images", description: "API quản lý ảnh sản phẩm")]
class ProductImageController extends BaseController
{
    public function __construct(
        protected ProductService $productService,
        protected ProductImageService $imageService
    ) {
        // Construct
    }

    #[OA\Post(
        path: "/admin/products/{uuid}/images",
        summary: "Upload ảnh",
        security: [['bearerAuth' => []]],
        tags: ["Product Images"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string")) ],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(properties: [
                    new OA\Property(property: "image", type: "string", format: "binary"),
                    new OA\Property(property: "is_primary", type: "boolean")
                ])
            )
        ),
        responses: [ new OA\Response(response: 201, description: "Uploaded") ]
    )]
    public function store(StoreProductImageRequest $request, string $uuid): JsonResponse
    {
        $product = $this->productService->findByUuidOrFail($uuid);
        $this->authorize('update', $product);

        $image = $this->imageService->upload(
            $product, 
            $request->file('image'), 
            $request->boolean('is_primary', false)
        );

        return response()->json(ApiResponse::success($image, 'Uploaded', 201), 201);
    }

    #[OA\Delete(
        path: "/admin/product-images/{uuid}",
        summary: "Xóa ảnh",
        security: [['bearerAuth' => []]],
        tags: ["Product Images"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string")) ],
        responses: [ new OA\Response(response: 200, description: "Deleted") ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $image = $this->imageService->findByUuidOrFail($uuid);
        $this->authorize('update', $image->product);

        $this->imageService->delete($uuid);
        return response()->json(ApiResponse::success(null, 'Deleted'));
    }
}