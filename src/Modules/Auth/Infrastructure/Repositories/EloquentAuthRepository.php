<?php

namespace Modules\Auth\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Auth\Domain\Repositories\AuthRepositoryInterface;
use Modules\User\Domain\Models\User;

class EloquentAuthRepository extends EloquentBaseRepository implements AuthRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }
}
