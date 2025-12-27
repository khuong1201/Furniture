<?php

declare(strict_types=1);

namespace Modules\Role\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Role\Domain\Models\Role;
use Modules\Role\Domain\Repositories\RoleRepositoryInterface;
use Modules\Role\Events\RoleAssigned;
use Modules\Shared\Exceptions\BusinessException;
use Modules\Shared\Services\BaseService;
use Modules\User\Domain\Models\User;

class RoleService extends BaseService
{
    public function __construct(RoleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data): Model
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($this->repository->findBySlug($data['slug'])) {
            throw new BusinessException(409191, "Role slug '{$data['slug']}' already exists");
        }

        return parent::create($data);
    }

    public function update(string $uuid, array $data): Model
    {
        $role = $this->findByUuidOrFail($uuid);
        if ($role->is_system) {
            if (isset($data['name']) || isset($data['slug'])) {
                throw new BusinessException(403192); 
            }
        }

        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if (isset($data['slug'])) {
            $existing = $this->repository->findBySlug($data['slug']);
            if ($existing && $existing->id !== $role->id) {
                throw new BusinessException(409191);
            }
        }

        return parent::update($uuid, $data);
    }

    public function delete(string $uuid): bool
    {
        $role = $this->findByUuidOrFail($uuid);

        if ($role->is_system) {
            throw new BusinessException(403192, 'Không thể xóa vai trò mặc định của hệ thống.');
        }

        if ($role->users()->exists()) {
            throw new BusinessException(409191, 'Role đang được gán cho người dùng, không thể xóa.');
        }

        return parent::delete($uuid);
    }

    public function assignRoleToUser(User $user, string $roleUuid): void
    {
        $role = $this->findByUuidOrFail($roleUuid);

        DB::transaction(function () use ($user, $role) {
            $user->roles()->syncWithoutDetaching([
                $role->id => [
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                ]
            ]);
            
            event(new RoleAssigned($user));
        });
    }

    public function removeRoleFromUser(User $user, string $roleUuid): void
    {
        $role = $this->findByUuidOrFail($roleUuid);

        DB::transaction(function () use ($user, $role) {
            $user->roles()->detach($role->id);
            event(new RoleAssigned($user));
        });
    }
}