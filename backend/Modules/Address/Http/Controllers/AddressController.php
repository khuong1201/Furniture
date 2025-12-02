<?php

namespace Modules\Address\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Address\Services\AddressService;
use Modules\Address\Domain\Models\Address;
use Modules\Address\Http\Requests\StoreAddressRequest;
use Modules\Address\Http\Requests\UpdateAddressRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Address",
    description: "API quản lý địa chỉ giao hàng của người dùng"
)]

class AddressController extends BaseController
{
    public function __construct(AddressService $service) 
    {
        parent::__construct($service);
    }
    
    #[OA\Get(
        path: "/addresses",
        summary: "Lấy danh sách địa chỉ của tôi",
        security: [['bearerAuth' => []]],
        tags: ["Address"],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $data = $this->service->listForUser($userId);
        
        return response()->json(ApiResponse::success($data));
    }

    #[OA\Post(
        path: "/addresses",
        summary: "Thêm địa chỉ mới",
        security: [['bearerAuth' => []]],
        tags: ["Address"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["full_name", "phone", "province", "district", "ward", "street"],
                properties: [
                    new OA\Property(property: "full_name", type: "string", example: "Nguyen Van A"),
                    new OA\Property(property: "phone", type: "string", example: "0901234567"),
                    new OA\Property(property: "province", type: "string"),
                    new OA\Property(property: "district", type: "string"),
                    new OA\Property(property: "ward", type: "string"),
                    new OA\Property(property: "street", type: "string"),
                    new OA\Property(property: "is_default", type: "boolean", example: false)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 422, description: "Validation Error")
        ]
    )]
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $validatedData['user_id'] = $request->user()->id; 

        $address = $this->service->create($validatedData);
        
        return response()->json(ApiResponse::success($address, 'Address created successfully', 201), 201);
    }

    #[OA\Get(
        path: "/addresses/{uuid}",
        summary: "Xem chi tiết địa chỉ",
        security: [['bearerAuth' => []]],
        tags: ["Address"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]

    public function show(string $uuid): JsonResponse
    {
        $address = $this->service->findByUuidOrFail($uuid);

        $this->authorize('view', $address);

        return response()->json(ApiResponse::success($address));
    }

    #[OA\Put(
        path: "/addresses/{uuid}",
        summary: "Cập nhật địa chỉ",
        security: [['bearerAuth' => []]],
        tags: ["Address"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "full_name", type: "string"),
                    new OA\Property(property: "phone", type: "string"),
                    new OA\Property(property: "is_default", type: "boolean")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Updated"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function update(UpdateAddressRequest $request, string $uuid): JsonResponse
    {
        $address = $this->service->findByUuidOrFail($uuid);

        $this->authorize('update', $address);

        $updated = $this->service->update($uuid, $request->validated());
        
        return response()->json(ApiResponse::success($updated, 'Address updated successfully'));
    }

    #[OA\Delete(
        path: "/addresses/{uuid}",
        summary: "Xóa địa chỉ",
        security: [['bearerAuth' => []]],
        tags: ["Address"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Deleted"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function destroy(string $uuid): JsonResponse 
    {
        $address = $this->service->findByUuidOrFail($uuid);

        $this->authorize('delete', $address);
        
        $this->service->delete($uuid); 
        
        return response()->json(ApiResponse::success(null, 'Address deleted successfully'));
    }
}