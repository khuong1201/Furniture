<?php

namespace Modules\Permission\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Permission\Services\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(private PermissionService $service) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $permissions = $this->service->getPermissionsByUserId($user->id);

        $names = array_values(array_unique(array_map('strtolower', $permissions)));

        return response()->json($names);
    }

    public function store(\Modules\Permission\Http\Requests\StorePermissionRequest $request): JsonResponse
    {
        $permission = $this->service->create($request->validated());

        return response()->json($permission, 201);
    }
    public function show($permission): JsonResponse
    {
        $p = $this->service->findByName((string) $permission) ?? null;

        if (! $p) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($p);
    }
}