<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Product\Http\Requests\StoreProductImageRequest;
use Modules\Product\Services\ProductImageService;
use Modules\Product\Services\ProductService;

class ProductImageController extends Controller
{
    public function __construct(
        protected ProductImageService $imageService,
        protected ProductService $productService
    ) {}

    public function store(StoreProductImageRequest $request, string $productUuid)
    {
        $product = $this->productService->findByUuid($productUuid);
        $image = $this->imageService->uploadSingle($product, $request->file('image'), (bool)$request->input('is_primary', false));
        return response()->json($image, 201);
    }

    public function destroy(string $uuid)
    {
        $deleted = $this->imageService->deleteByUuid($uuid);
        return response()->json(['deleted' => $deleted]);
    }
}