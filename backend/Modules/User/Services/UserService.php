<?php

namespace Modules\User\Services;

use Modules\Shared\Services\BaseService;
use Modules\User\Domain\Repositories\UserRepositoryInterface;
use Modules\Role\Domain\Repositories\RoleRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserService extends BaseService
{
    public function __construct(
        UserRepositoryInterface $repo,
        protected RoleRepositoryInterface $roleRepo
    ) {
        parent::__construct($repo);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user = $this->repository->create($data);

            if (isset($data['roles']) && is_array($data['roles'])) {
                $this->syncRoles($user, $data['roles']);
            }

            return $user;
        });
    }

    public function update(string $uuid, array $data): Model
    {
        $user = $this->repository->findByUuid($uuid);
        if (!$user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("User not found");
        }

        return DB::transaction(function () use ($user, $data) {
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $updatedUser = $this->repository->update($user, $data);

            if (isset($data['roles']) && is_array($data['roles'])) {
                $this->syncRoles($updatedUser, $data['roles']);
            }

            return $updatedUser;
        });
    }

    protected function syncRoles(Model $user, array $roles): void
    {
        if (method_exists($user, 'syncRoles')) {
            $user->syncRoles($roles);
        } else {
            if (method_exists($user, 'roles')) {
                $user->roles()->sync($roles);
            }
        }
    }
    
    protected function beforeCreate(array &$data): void {}
    protected function afterCreate(Model $model): void {}
    protected function beforeUpdate(Model $model, array &$data): void {}
    protected function afterUpdate(Model $model): void {}
}