<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Auth\Domain\Repositories\RefreshTokenRepositoryInterface;
use Modules\Auth\Domain\Models\RefreshToken;

class EloquentRefreshTokenRepository extends EloquentBaseRepository implements RefreshTokenRepositoryInterface
{
    public function __construct(RefreshToken $model)
    {
        parent::__construct($model);
    }

    public function findByToken(string $token): ?RefreshToken
    {
        return $this->model->where('token', $token)->first();
    }

    public function revokeAllForUser(int $userId): void
    {
        $this->model->where('user_id', $userId)
            ->update(['is_revoked' => true]);
    }
}