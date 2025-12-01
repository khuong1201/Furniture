<?php

namespace Modules\Log\Services;

use Modules\Shared\Services\BaseService;
use Modules\Log\Domain\Repositories\LogRepositoryInterface;

class LogService extends BaseService
{
    public function __construct(LogRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getLogs(array $filters = [])
    {
        return $this->repository->filter($filters);
    }
}