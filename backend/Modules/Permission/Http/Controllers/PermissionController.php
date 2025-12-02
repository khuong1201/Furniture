<?php

namespace Modules\Permission\Http\Controllers;

use Modules\Shared\Http\Controllers\BaseController;
use Modules\Permission\Services\PermissionService;
use Modules\Permission\Http\Requests\StorePermissionRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PermissionController extends BaseController
{
    public function __construct(PermissionService $service)
    {
        parent::__construct($service);
    }

    public function index(Request $request): JsonResponse
    {
        return parent::index($request);
    }
    
    public function myPermissions(Request $request): JsonResponse
    {
        $permissions = $this->service->getUserPermissions($request->user()->id);
        
        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        $data = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Permission created successfully',
            'data' => $data
        ], 201);
    }

    public function show(string $uuid): JsonResponse
    {
        return parent::show($uuid);
    }
}