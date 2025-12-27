<?php

declare(strict_types=1);

namespace Modules\Brand\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Brand\Services\BrandService;
use Modules\Brand\Http\Resources\BrandResource;
use Modules\Brand\Http\Requests\StoreBrandRequest;
use Modules\Brand\Http\Requests\UpdateBrandRequest; // Cấu trúc giống Store nhưng rules là sometimes
use Modules\Brand\Domain\Models\Brand;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Brands", description: "API quản lý thương hiệu")]
class BrandController extends BaseController
{
    public function __construct(BrandService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/api/public/brands",
        summary: "Danh sách thương hiệu (Public)",
        tags: ["Brands"],
        parameters: [
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string"))
        ],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $filters['is_active'] = true;
        
        // Nếu user chỉ muốn list all không phân trang cho dropdown
        if ($request->has('all')) {
            $brands = $this->service->getRepository()->getBy(['is_active' => true]);
            return $this->successResponse(BrandResource::collection($brands));
        }

        $paginator = $this->service->filter($request->integer('per_page', 20), $filters);
        $paginator->through(fn($b) => new BrandResource($b));

        return $this->successResponse($paginator);
    }

    #[OA\Get(
        path: "/api/admin/brands",
        summary: "Danh sách thương hiệu (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Brands"],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function adminIndex(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Brand::class);
        $paginator = $this->service->filter($request->integer('per_page', 20), $request->all());
        $paginator->through(fn($b) => new BrandResource($b));
        return $this->successResponse($paginator);
    }

    #[OA\Post(
        path: "/api/admin/brands",
        summary: "Tạo thương hiệu",
        security: [['bearerAuth' => []]],
        tags: ["Brands"],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(properties: [
                new OA\Property(property: "name", type: "string"),
                new OA\Property(property: "logo", type: "string", format: "binary"),
            ])
        )),
        responses: [new OA\Response(response: 201, description: "Created")]
    )]
    public function store(StoreBrandRequest $request): JsonResponse
    {
        $this->authorize('create', Brand::class);
        $brand = $this->service->create($request->validated());
        return $this->successResponse(new BrandResource($brand), 'Tạo thương hiệu thành công', 201);
    }

    #[OA\Put(
        path: "/api/admin/brands/{uuid}",
        summary: "Cập nhật thương hiệu",
        security: [['bearerAuth' => []]],
        tags: ["Brands"],
        responses: [new OA\Response(response: 200, description: "Updated")]
    )]
    public function update(UpdateBrandRequest $request, string $uuid): JsonResponse
    {
        $brand = $this->service->findByUuidOrFail($uuid);
        $this->authorize('update', $brand);
        
        $updated = $this->service->update($uuid, $request->validated());
        return $this->successResponse(new BrandResource($updated), 'Cập nhật thành công');
    }

    #[OA\Delete(
        path: "/api/admin/brands/{uuid}",
        summary: "Xóa thương hiệu",
        security: [['bearerAuth' => []]],
        tags: ["Brands"],
        responses: [new OA\Response(response: 200, description: "Deleted")]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $brand = $this->service->findByUuidOrFail($uuid);
        $this->authorize('delete', $brand);
        $this->service->delete($uuid);
        return $this->successResponse(null, 'Xóa thương hiệu thành công');
    }
}