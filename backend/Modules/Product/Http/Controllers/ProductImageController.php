<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Product\Services\ProductImageService;
use Modules\Product\Services\ProductService;
use Modules\Product\Http\Requests\StoreProductImageRequest;

class ProductImageController extends BaseController
{
    public function __construct(
        protected ProductImageService $imageService,
        protected ProductService $productService
    ) {
        parent::__construct($imageService); 
    }

    public function store(Request $request): JsonResponse
    {
        $productUuid = $request->route('uuid'); 

        $validatedRequest = app(StoreProductImageRequest::class);
         
        $product = $this->productService->findByUuidOrFail($productUuid);
        
        $image = $this->imageService->upload(
            $product, 
            $request->file('image'), 
            (bool)$request->input('is_primary', false)
        );

        return response()->json(ApiResponse::success($image, 'Image uploaded successfully', 201), 201);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $deleted = $this->imageService->delete($uuid);
        
        if (!$deleted) {
             return response()->json(ApiResponse::error('Image not found or could not be deleted', 404), 404);
        }

        return response()->json(ApiResponse::success(null, 'Image deleted successfully'));
    }
}