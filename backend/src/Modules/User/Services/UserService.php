<?php

declare(strict_types=1);

namespace Modules\User\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Role\Domain\Models\Role; 
use Modules\Shared\Exceptions\BusinessException;
use Modules\Shared\Services\BaseService;
use Modules\User\Domain\Repositories\UserRepositoryInterface;

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

            if (!empty($data['roles'])) {
                $this->validateAndSyncRoles($user, $data['roles']);
            }

            return $user->load('roles');
        });
    }

    public function update(string $uuid, array $data): Model
    {
        $user = $this->findByUuidOrFail($uuid);

        return DB::transaction(function () use ($user, $data) {
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }
            $updatedUser = $this->repository->update($user, $data);

            if (array_key_exists('roles', $data)) {
                $this->validateAndSyncRoles($updatedUser, $data['roles']);
                if (method_exists($updatedUser, 'clearPermissionCache')) {
                    $updatedUser->clearPermissionCache();
                }
            }

            return $updatedUser->load('roles');
        });
    }

    public function delete(string $uuid): bool
    {
        $user = $this->findByUuidOrFail($uuid);
        if ($user->id === auth()->id()) {
            throw new BusinessException(403015); 
        }

        return $this->repository->delete($user);
    }

    public function changePassword(Model $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new BusinessException(400016); 
        }

        $this->repository->update($user, [
            'password' => Hash::make($newPassword)
        ]);
    }

    protected function validateAndSyncRoles(Model $user, array $roleIds): void
    {
        if (empty($roleIds)) {
            $user->roles()->detach();
            return;
        }

        $count = Role::whereIn('id', $roleIds)->count();

        if ($count !== count(array_unique($roleIds))) {
            throw new BusinessException(404190, 'One or more roles are invalid');
        }

        $user->roles()->sync($roleIds);
    }

    public function filter(array $filters)
    {
        // Chuyển is_active string 'true'/'false' sang boolean
        if (isset($filters['is_active'])) {
            $filters['is_active'] = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        // Chuẩn hóa ngày
        if (!empty($filters['created_from'])) {
            $filters['created_from'] = date('Y-m-d H:i:s', strtotime($filters['created_from']));
        }
        if (!empty($filters['created_to'])) {
            $filters['created_to'] = date('Y-m-d H:i:s', strtotime($filters['created_to']));
        }

        // Nếu muốn thêm các filter khác, chỉ cần thêm key vào $filters là repo xử lý
        return $this->repository->filter($filters);
    }
}