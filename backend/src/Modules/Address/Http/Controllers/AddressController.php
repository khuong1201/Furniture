<?php

declare(strict_types=1);

namespace Modules\Address\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Address\Http\Requests\StoreAddressRequest;
use Modules\Address\Http\Requests\UpdateAddressRequest;
use Modules\Address\Services\AddressService;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Traits\ApiResponseTrait;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Address", description: "API quản lý sổ địa chỉ giao hàng")]
class AddressController extends BaseController
{
    public function __construct(AddressService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/api/addresses",
        summary: "Lấy danh sách địa chỉ của tôi",
        security: [["bearerAuth" => []]],
        tags: ["Address"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "array", items: new OA\Items(properties: [
                        new OA\Property(property: "uuid", type: "string"),
                        new OA\Property(property: "full_name", type: "string"),
                        new OA\Property(property: "phone", type: "string"),
                        new OA\Property(property: "full_address", type: "string", description: "Địa chỉ ghép"),
                        new OA\Property(property: "is_default", type: "boolean")
                    ]))
                ])
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $data = $this->service->listForUser($userId);
        return $this->successResponse($data);
    }

    #[OA\Post(
        path: "/api/addresses",
        summary: "Thêm địa chỉ mới",
        security: [["bearerAuth" => []]],
        tags: ["Address"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["full_name", "phone", "province", "district", "ward", "street"],
                properties: [
                    new OA\Property(property: "full_name", type: "string", example: "Nguyễn Văn A"),
                    new OA\Property(property: "phone", type: "string", example: "0901234567"),
                    new OA\Property(property: "province", type: "string", example: "Hồ Chí Minh"),
                    new OA\Property(property: "district", type: "string", example: "Quận 1"),
                    new OA\Property(property: "ward", type: "string", example: "Phường Bến Nghé"),
                    new OA\Property(property: "street", type: "string", example: "123 Lê Lợi"),
                    new OA\Property(property: "is_default", type: "boolean", default: false),
                    new OA\Property(property: "type", type: "string", enum: ["home", "office"], default: "home"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 400, description: "Limit reached (400033)")
        ]
    )]
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data["user_id"] = $request->user()->id;

        $address = $this->service->create($data);

        return $this->successResponse($address, "Address created successfully", 201);
    }

    #[OA\Get(
        path: "/api/addresses/{uuid}",
        summary: "Xem chi tiết địa chỉ",
        security: [["bearerAuth" => []]],
        tags: ["Address"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $address = $this->service->findByUuidOrFail($uuid);
        $this->authorize("view", $address);

        return $this->successResponse($address);
    }

    #[OA\Put(
        path: "/api/addresses/{uuid}",
        summary: "Cập nhật địa chỉ",
        security: [["bearerAuth" => []]],
        tags: ["Address"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: "full_name", type: "string"),
            new OA\Property(property: "is_default", type: "boolean")
        ])),
        responses: [new OA\Response(response: 200, description: "Updated")]
    )]
    public function update(UpdateAddressRequest $request, string $uuid): JsonResponse
    {
        $address = $this->service->findByUuidOrFail($uuid);
        $this->authorize("update", $address);

        $updated = $this->service->update($uuid, $request->validated());

        return $this->successResponse($updated, "Address updated successfully");
    }

    #[OA\Delete(
        path: "/api/addresses/{uuid}",
        summary: "Xóa địa chỉ",
        security: [["bearerAuth" => []]],
        tags: ["Address"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [
            new OA\Response(response: 200, description: "Deleted"),
            new OA\Response(response: 403, description: "Cannot delete default address (403032)")
        ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $address = $this->service->findByUuidOrFail($uuid);
        $this->authorize("delete", $address);

        $this->service->delete($uuid);

        return $this->successResponse(null, "Address deleted successfully");
    }
}