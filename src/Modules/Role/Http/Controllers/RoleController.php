<?php

namespace Modules\Role\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Role\Domain\Repositories\RoleRepositoryInterface;
use Modules\Role\Services\RoleService;

class RoleController extends Controller
{
    public function __construct(
        protected RoleRepositoryInterface $repo,
        protected RoleService $service
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $filters = ['q' => $request->query('q')];
        $roles = $this->service->listRoles($perPage, $filters);
        return response()->json([
            'data' => $roles->items(),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
                'last_page' => $roles->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'description' => 'nullable|string',
        ]);
        $role = $this->service->createRole($payload);
        return response()->json(['role' => $role], 201);
    }

    public function show($id)
    {
        $role = $this->repo->findById($id);
        if (! $role) abort(404);
        return response()->json(['role' => $role]);
    }

    public function update(Request $request, $id)
    {
        $payload = $request->validate([
            'name' => 'sometimes|string|unique:roles,name,'.$id,
            'description' => 'nullable|string',
        ]);
        $role = $this->service->updateRole($id, $payload);
        return response()->json(['role' => $role]);
    }

    public function destroy($id)
    {
        $this->service->deleteRole($id);
        return response()->json(['message' => 'Role deleted'], 200);
    }
}
