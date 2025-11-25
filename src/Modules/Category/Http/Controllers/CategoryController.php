<?php

namespace Modules\Category\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Category\Services\CategoryService;
use Modules\Category\Http\Requests\StoreCategoryRequest;
use Modules\Category\Http\Requests\UpdateCategoryRequest;
use Modules\Category\Domain\Models\Category;

class CategoryController extends Controller
{
    public function __construct(protected CategoryService $service)
    {
        $this->middleware(\Modules\Auth\Http\Middleware\JwtAuthenticate::class);
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->service->create($request->validated());
        return response()->json($category, 201);
    }

    public function update(UpdateCategoryRequest $request, string $uuid): JsonResponse
    {
        $category = $this->service->findByUuid($uuid); 
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        $category = $this->service->update($uuid, $request->validated());
        return response()->json($category);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $category = $this->service->findByUuid($uuid);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        $this->service->delete($uuid);
        return response()->json(['message' => 'deleted category successfully'], 204); 
    }
}
