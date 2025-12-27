<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Repositories;

use Modules\Shared\Contracts\BaseRepositoryInterface;
use Modules\Auth\Domain\Models\RefreshToken;

interface RefreshTokenRepositoryInterface extends BaseRepositoryInterface
{
    public function findByToken(string $token): ?RefreshToken;
    public function revokeAllForUser(int $userId): void;
}