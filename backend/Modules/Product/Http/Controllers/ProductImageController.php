<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Product\Services\ProductImageService;
use Modules\Product\Services\ProductService;
use Modules\Product\Domain\Models\ProductImage;
use Modules\Product\Http\Requests\StoreProductImageRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Product Images",
    description: "API quản lý ảnh sản phẩm (Admin)"
)]

class ProductImageController extends BaseController
{
    public function __construct(
        protected ProductImageService $imageService,
        protected ProductService $productService
    ) {
        parent::__construct($imageService); 
    }

    #[OA\Post(
        path: "/api/admin/products/{uuid}/images",
        summary: "Upload ảnh cho sản phẩm",
        security: [['bearerAuth' => []]],
        tags: ["Product Images"],
        parameters: [
            new OA\Parameter(
                name: "uuid", 
                in: "path", 
                description: "UUID của Product",
                required: true, 
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["image"],
                    properties: [
                        new OA\Property(property: "image", description: "File ảnh", type: "string", format: "binary"),
                        new OA\Property(property: "is_primary", description: "Đặt làm ảnh đại diện?", type: "boolean", default: false),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Image uploaded"),
            new OA\Response(response: 404, description: "Product not found"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]

    public function store(StoreProductImageRequest $request): JsonResponse
    {
        $productUuid = $request->route('uuid'); 
         
        $product = $this->productService->findByUuidOrFail($productUuid);
        
        $image = $this->imageService->upload(
            $product, 
            $request->file('image'), 
            (bool)$request->input('is_primary', false)
        );

        return response()->json(ApiResponse::success($image, 'Image uploaded successfully', 201), 201);
    }

    #[OA\Delete(
        path: "/api/admin/product-images/{uuid}",
        summary: "Xóa ảnh sản phẩm",
        security: [['bearerAuth' => []]],
        tags: ["Product Images"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", description: "UUID của Image", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Image deleted"),
            new OA\Response(response: 404, description: "Image not found"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]

    public function destroy(string $uuid): JsonResponse
    {
        $deleted = $this->imageService->delete($uuid);
        
        if (!$deleted) {
             return response()->json(ApiResponse::error('Image not found or could not be deleted', 404), 404);
        }

        return response()->json(ApiResponse::success(null, 'Image deleted successfully'));
    }
}