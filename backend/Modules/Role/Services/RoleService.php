<?php

declare(strict_types=1);

namespace Modules\Role\Services;

use Modules\Shared\Services\BaseService;
use Modules\Role\Domain\Repositories\RoleRepositoryInterface;
use Modules\Role\Events\RoleAssigned;
use Modules\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use RuntimeException;
use Illuminate\Support\Str;

class RoleService extends BaseService
{
    public function __construct(RoleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function beforeCreate(array &$data): void 
    {
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
    }

    protected function beforeUpdate(Model $model, array &$data): void
    {
        if ($model->is_system) {
            unset($data['slug'], $data['name'], $data['is_system']);
        } else {
             if (isset($data['name']) && empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }
        }
    }

    protected function beforeDelete(Model $model): void
    {
        if ($model->is_system) {
            throw new RuntimeException("Cannot delete a system role: {$model->name}");
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
    
    public function getRolesPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->search($filters, $perPage);
    }
}