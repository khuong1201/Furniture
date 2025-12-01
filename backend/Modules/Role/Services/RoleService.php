<?php

namespace Modules\Role\Services;

use Modules\Shared\Services\BaseService;
use Modules\Role\Domain\Repositories\RoleRepositoryInterface;
use Modules\Role\Events\RoleAssigned;
use Modules\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class RoleService extends BaseService
{
    public function __construct(RoleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function beforeUpdate(Model $model, array &$data): void
    {
        if ($model->is_system) {
             unset($data['slug'], $data['name'], $data['is_system']);
        }
    }

    protected function beforeDelete(Model $model): void
    {
        if ($model->is_system) {
            throw new \RuntimeException("Cannot delete a system role: {$model->name}");
        }
    }

    public function assignRoleToUser(User $user, string $roleUuid): void
    {
        $role = $this->findByUuidOrFail($roleUuid);

        $user->roles()->syncWithoutDetaching([$role->id => [
            'assigned_by' => auth()->id(),
            'assigned_at' => now()
        ]]);

        event(new RoleAssigned($user));
    }

    public function removeRoleFromUser(User $user, string $roleUuid): void
    {
        $role = $this->findByUuidOrFail($roleUuid);
        
        $user->roles()->detach($role->id);
        
        event(new RoleAssigned($user));
    }
    
    public function paginate(int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->repository->query();
        
        if ($search = request()->get('q')) {
            $query->search($search);
        }

        return $query->latest()->paginate($perPage);
    }
}