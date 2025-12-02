<?php

namespace Modules\Role\Http\Controllers;

use Modules\Shared\Http\Controllers\BaseController;
use Modules\Role\Services\RoleService;
use Modules\Role\Http\Requests\StoreRoleRequest;
use Modules\Role\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\Request;

class RoleController extends BaseController
{
    public function __construct(RoleService $service)
    {
        parent::__construct($service);
    }

    public function store(StoreRoleRequest $request): \Illuminate\Http\JsonResponse 
    {
        $data = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $data
        ], 201);
    }

    public function update(UpdateRoleRequest $request, string $uuid): \Illuminate\Http\JsonResponse
    {
        $data = $this->service->update($uuid, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $data
        ]);
    }
    
}