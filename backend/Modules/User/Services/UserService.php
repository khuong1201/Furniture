<?php

declare(strict_types=1);

namespace Modules\User\Services;

use Modules\Shared\Services\BaseService;
use Modules\User\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserService extends BaseService
{
    public function __construct(UserRepositoryInterface $repo) 
    {
        parent::__construct($repo);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $data['is_active'] = $data['is_active'] ?? true;

            $user = $this->repository->create($data);

            if (isset($data['roles'])) {
                $this->syncRoles($user, $data['roles']);
            }

            return $user->load('roles'); 
        });
    }

    public function update(string $uuid, array $data): Model
    {
        $user = $this->repository->findByUuidOrFail($uuid);

        return DB::transaction(function () use ($user, $data) {
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $updatedUser = $this->repository->update($user, $data);

            if (isset($data['roles'])) {
                $this->syncRoles($updatedUser, $data['roles']);
                $updatedUser->clearPermissionCache();
            }

            return $updatedUser->load('roles');
        });
    }

    protected function syncRoles(Model $user, array $roleIds): void
    {
        if (method_exists($user, 'roles')) {
            $user->roles()->sync($roleIds);
        }
    }
}