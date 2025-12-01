<?php

namespace Modules\Category\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Category\Services\CategoryService;
use Modules\Category\Http\Requests\StoreCategoryRequest;
use Modules\Category\Http\Requests\UpdateCategoryRequest;

class CategoryController extends BaseController
{
    public function __construct(CategoryService $service)
    {
        parent::__construct($service);
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        if ($request->has('tree')) {
            $data = $this->service->getTree();
            return response()->json(ApiResponse::success($data));
        }

        return parent::index($request);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request = app(StoreCategoryRequest::class);
        $data = $this->service->create($request->validated());
        
        return response()->json(ApiResponse::success($data, 'Category created', 201), 201);
    }

    public function update(Request $request, string $uuid): \Illuminate\Http\JsonResponse
    {
        $request = app(UpdateCategoryRequest::class);
        $data = $this->service->update($uuid, $request->validated());

        return response()->json(ApiResponse::success($data, 'Category updated'));
    }
}