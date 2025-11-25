<?php
namespace Modules\User\Domain\Repositories;

use Modules\User\Domain\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function all(int $perPage = 10): LengthAwarePaginator;
    public function findByUuid(string $uuid): User;
    public function findById(int|string $id): ?User;
    public function create(array $data): User;
    public function update(User $user, array $data): User;
    public function delete(User $user): bool;
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
}
