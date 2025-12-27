<?php

declare(strict_types=1);

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Product\Http\Requests\StoreProductImageRequest;
use Modules\Product\Services\ProductImageService;
use Modules\Product\Services\ProductService;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Traits\ApiResponseTrait;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Product Images", description: "Thêm/Xóa ảnh phụ cho sản phẩm")]
class ProductImageController extends BaseController
{
    public function __construct(
        protected ProductService $productService,
        protected ProductImageService $imageService
    ) {
        //
    }

    #[OA\Post(
        path: "/api/admin/products/{uuid}/images",
        summary: "Upload thêm ảnh cho sản phẩm",
        description: "Upload file ảnh (jpg, png, webp). Max 5MB.",
        security: [['bearerAuth' => []]],
        tags: ["Product Images"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, description: "UUID Sản phẩm")],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["image"],
                    properties: [
                        new OA\Property(
                            property: "image", 
                            type: "string", 
                            format: "binary", 
                            description: "File ảnh upload"
                        ),
                        new OA\Property(
                            property: "is_primary", 
                            type: "boolean", 
                            description: "Nếu true, ảnh này sẽ thành ảnh đại diện chính",
                            default: false
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201, 
                description: "Upload thành công",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "object", properties: [
                        new OA\Property(property: "image_url", type: "string"),
                        new OA\Property(property: "is_primary", type: "boolean")
                    ])
                ])
            ),
            new OA\Response(response: 502, description: "Lỗi Upload Cloud (500112)")
        ]
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

       return $this->successResponse($image, 'Image uploaded successfully', 201);
    }

    #[OA\Delete(
        path: "/api/admin/product-images/{uuid}",
        summary: "Xóa ảnh sản phẩm",
        security: [['bearerAuth' => []]],
        tags: ["Product Images"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, description: "UUID của Ảnh (ProductImage)")],
        responses: [new OA\Response(response: 200, description: "Deleted")]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $image = $this->imageService->findByUuidOrFail($uuid);
        
        $this->authorize('update', $image->product);

        $this->imageService->delete($uuid);
        
        return $this->successResponse(null, 'Image deleted successfully');
    }
}