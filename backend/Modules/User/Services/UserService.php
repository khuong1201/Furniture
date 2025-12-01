<?php

namespace Modules\User\Services;

use Modules\Shared\Services\BaseService;
use Modules\User\Domain\Repositories\UserRepositoryInterface;
use Modules\Role\Domain\Repositories\RoleRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

class UserService extends BaseService
{
    public function __construct(
        UserRepositoryInterface $repo,
        protected RoleRepositoryInterface $roleRepo
    ) {
        parent::__construct($repo);
    }

    public function paginate(int $perPage = 15, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $filters['per_page'] = $perPage;
        return $this->repository->filter($filters);
    }


    protected function beforeCreate(array &$data): void
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
    }

    protected function afterCreate(Model $model): void
    {
        if (request()->has('roles')) {
            $this->syncRoles($model, request()->input('roles'));
        }
    }

    protected function beforeUpdate(Model $model, array &$data): void
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
    }

    protected function afterUpdate(Model $model): void
    {
        if (request()->has('roles')) {
            $this->syncRoles($model, request()->input('roles'));
        }
    }

    protected function syncRoles(Model $user, array $roles): void
    {
    }
}