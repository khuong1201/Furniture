<?php

namespace Modules\User\Services;

use Modules\Shared\DTO\UserDTO;
use Modules\User\Domain\Models\User;
use Modules\Shared\Contracts\IUserService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Modules\User\Domain\Repositories\UserRepositoryInterface;
use Modules\Role\Domain\Models\Role;

class UserService implements IUserService
{
    public function __construct(protected UserRepositoryInterface $repo) {}

    protected function toDto(User $user): UserDTO
    {
        return UserDTO::fromModel($user);
    }

    public function findById(string $id): ?UserDTO
    {
        $user = $this->repo->findById($id);
        if (! $user) return null;
        $user->load('roles.permissions');
        return $this->toDto($user);
    }

    public function findByUuid(string $uuid): ?UserDTO
    {
        $user = $this->repo->findByUuid($uuid); 
        $user->load('roles.permissions');
        return $this->toDto($user);
    }

    public function hasPermission(string $userId, string $permission): bool
    {
        $user = $this->repo->findByUuid($userId) ?? $this->repo->findById($userId);
        if (! $user || ! $user->is_active) return false;
        $user->load('roles.permissions');
        $perms = $user->roles->flatMap(fn($r) => $r->permissions->pluck('name'))->map(fn($n) => strtolower($n))->unique()->all();
        return in_array(strtolower($permission), $perms, true);
    }

    public function invalidatePermissionCache(string $userId): void
    {
        // implement cache invalidation if used, e.g. Cache::forget("user_permissions_{$userId}");
    }

    public function create(array $data): UserDTO
    {
        $payload = Arr::only($data, ['name','email','password','is_active','avatar_url']);
        $payload['uuid'] = (string) Str::uuid();
        $payload['password'] = isset($payload['password']) ? Hash::make($payload['password']) : Hash::make(Str::random(12));

        $user = $this->repo->create($payload);

        if (!empty($data['roles']) && is_array($data['roles'])) {
            $roleIds = Role::whereIn('name', $data['roles'])->orWhereIn('id', $data['roles'])->get()->pluck('id')->toArray();
            $user->roles()->sync($roleIds);
        }

        return $this->toDto($user->fresh('roles.permissions'));
    }

    public function update(string $uuid, array $data): UserDTO
    {
        $user = $this->repo->findByUuid($uuid);

        $payload = Arr::only($data, ['name','email','password','is_active','avatar_url']);
        if (isset($payload['password']) && $payload['password']) {
            $payload['password'] = Hash::make($payload['password']);
        } else {
            unset($payload['password']);
        }

        $user = $this->repo->update($user, $payload);

        if (array_key_exists('roles', $data) && is_array($data['roles'])) {
            $roleIds = Role::whereIn('name', $data['roles'])->orWhereIn('id', $data['roles'])->get()->pluck('id')->toArray();
            $user->roles()->sync($roleIds);
        }

        return $this->toDto($user->fresh('roles.permissions'));
    }

    public function delete(string $uuid): void
    {
        $user = $this->repo->findByUuid($uuid);
        $this->repo->delete($user);
    }

    public function paginate(int $perPage = 15, array $filters = []): array
    {
        $p = $this->repo->paginate($perPage, $filters);

        return [
            'items' => $p->items(),
            'meta' => [
                'current_page' => $p->currentPage(),
                'per_page' => $p->perPage(),
                'total' => $p->total(),
                'last_page' => $p->lastPage(),
            ],
        ];
    }
}