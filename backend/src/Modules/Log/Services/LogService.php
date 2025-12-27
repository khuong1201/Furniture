<?php

declare(strict_types=1);

namespace Modules\Log\Services;

use Modules\Log\Domain\Repositories\LogRepositoryInterface;
use Modules\Shared\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LogService extends BaseService
{
    public function __construct(
        LogRepositoryInterface $repository
    ) {
        parent::__construct($repository);
    }

    public function getLogs(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->getLogsByFilters($filters, $perPage);
    }

    public function createLog(array $data): void
    {
        if (isset($data['message'])) {
            $data['message'] = substr($data['message'], 0, 60000);
        }

        $this->create($data); 
    }
}