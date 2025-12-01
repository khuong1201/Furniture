<?php

namespace Modules\User\Http\Controllers;

use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\User\Services\UserService;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class UserController extends BaseController
{
    public function __construct(UserService $service)
    {
        parent::__construct($service);
    }

    public function index(Request $request): JsonResponse
    {
        return parent::index($request); 
    }

    public function store(Request $request): JsonResponse
    {
        $request = app(StoreUserRequest::class); 
        $data = $this->service->create($request->validated());

        return response()->json(ApiResponse::success($data, 'User created successfully', 201), 201);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $request = app(UpdateUserRequest::class);
        $data = $this->service->update($uuid, $request->validated());

        return response()->json(ApiResponse::success($data, 'User updated successfully'));
    }
}