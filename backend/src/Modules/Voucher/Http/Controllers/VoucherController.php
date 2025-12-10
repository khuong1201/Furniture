<?php

declare(strict_types=1);

namespace Modules\Voucher\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Voucher\Services\VoucherService;
use Modules\Voucher\Http\Requests\StoreVoucherRequest;
use Modules\Voucher\Http\Requests\UpdateVoucherRequest; 
use Modules\Voucher\Domain\Models\Voucher;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Vouchers", description: "Quản lý Mã giảm giá (Admin)")]
class VoucherController extends BaseController
{
    public function __construct(VoucherService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/admin/vouchers",
        summary: "Danh sách Voucher",
        security: [['bearerAuth' => []]],
        tags: ["Vouchers"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "is_active", in: "query", schema: new OA\Schema(type: "boolean")),
        ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Voucher::class); 
        $data = $this->service->paginate($request->integer('per_page', 20), $request->all());
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Post(
        path: "/admin/vouchers",
        summary: "Tạo Voucher",
        security: [['bearerAuth' => []]],
        tags: ["Vouchers"],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            required: ["code", "value", "type", "name", "quantity"],
            properties: [
                new OA\Property(property: "code", type: "string", example: "SALE50"),
                new OA\Property(property: "name", type: "string"),
                new OA\Property(property: "type", type: "string", enum: ["fixed", "percentage"]),
                new OA\Property(property: "value", type: "number"),
                new OA\Property(property: "quantity", type: "integer"),
                new OA\Property(property: "min_order_value", type: "number"),
                new OA\Property(property: "limit_per_user", type: "integer"),
                new OA\Property(property: "start_date", type: "string", format: "date-time"),
                new OA\Property(property: "end_date", type: "string", format: "date-time"),
                new OA\Property(property: "is_active", type: "boolean"),
            ]
        )),
        responses: [ new OA\Response(response: 201, description: "Created") ]
    )]
    public function store(StoreVoucherRequest $request): JsonResponse
    {
        $this->authorize('create', Voucher::class);
        $voucher = $this->service->create($request->validated());
        return response()->json(ApiResponse::success($voucher, 'Voucher created', 201), 201);
    }

    #[OA\Get(
        path: "/admin/vouchers/{uuid}",
        summary: "Xem chi tiết Voucher",
        security: [['bearerAuth' => []]],
        tags: ["Vouchers"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")) ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $voucher = $this->service->findByUuidOrFail($uuid);
        $this->authorize('view', $voucher);
        return response()->json(ApiResponse::success($voucher));
    }

    #[OA\Put(
        path: "/admin/vouchers/{uuid}",
        summary: "Cập nhật Voucher",
        security: [['bearerAuth' => []]],
        tags: ["Vouchers"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")) ],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string"),
                new OA\Property(property: "quantity", type: "integer"),
                new OA\Property(property: "is_active", type: "boolean"),
            ]
        )),
        responses: [ new OA\Response(response: 200, description: "Updated") ]
    )]
    public function update(UpdateVoucherRequest $request, string $uuid): JsonResponse
    {
        $voucher = $this->service->findByUuidOrFail($uuid);
        $this->authorize('update', $voucher);
        
        $updated = $this->service->update($uuid, $request->validated());
        return response()->json(ApiResponse::success($updated, 'Voucher updated'));
    }

    #[OA\Delete(
        path: "/admin/vouchers/{uuid}",
        summary: "Xóa Voucher",
        security: [['bearerAuth' => []]],
        tags: ["Vouchers"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")) ],
        responses: [ new OA\Response(response: 200, description: "Deleted") ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $voucher = $this->service->findByUuidOrFail($uuid);
        $this->authorize('delete', $voucher);
        
        $this->service->delete($uuid);
        return response()->json(ApiResponse::success(null, 'Voucher deleted'));
    }
}