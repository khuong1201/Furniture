<?php

declare(strict_types=1);

namespace Modules\Log\Services;

use Modules\Shared\Services\BaseService;
use Modules\Log\Domain\Repositories\LogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LogService extends BaseService
{
    public function __construct(LogRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getLogs(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->getLogsByFilters($filters, $perPage);
    }

    public function createLog(array $data): void
    {
        if (isset($data['message']) && strlen($data['message']) > 60000) {
            $data['message'] = substr($data['message'], 0, 60000) . '... (truncated)';
        }

        $this->create($data);
    }
}