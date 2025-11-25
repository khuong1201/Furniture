<?php

namespace Modules\User\Http\Controllers;

use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Modules\User\Domain\Repositories\UserRepositoryInterface;
use Modules\User\Services\UserService;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $repo,
        protected UserService $service
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $filters = ['q' => $request->query('q')];
        $res = $this->service->paginate($perPage, $filters);

        return response()->json([
            'data' => $res['items'],
            'meta' => $res['meta'],
        ], 200);
    }

    public function show(string $uuid)
    {
        $user = $this->repo->findByUuid($uuid);
        return response()->json(['user' => $user], 200);
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->service->create($request->validated());
        return response()->json(['user' => $user], 201);
    }

    public function update(UpdateUserRequest $request, string $uuid)
    {
        $updated = $this->service->update($uuid, $request->validated());
        return response()->json(['user' => $updated], 200);
    }

    public function destroy(string $uuid)
    {
        $this->service->delete($uuid);
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}