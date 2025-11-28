<?php

namespace Modules\Permission\Http\Controllers;

use Modules\Shared\Http\Controllers\BaseController;
use Modules\Permission\Services\PermissionService;
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
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Unauthenticated.'], 401);

        $permissions = $this->service->getPermissionsByUserId($user->id);
        return response()->json($permissions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateData($request);
        $permission = $this->service->create($validated);
        return response()->json($permission, 201);
    }

    public function show(string $name): JsonResponse
    {
        $permission = $this->service->findByName($name);
        if (!$permission) return response()->json(['message' => 'Not found.'], 404);
        return response()->json($permission);
    }
}
