<?php
namespace Modules\Log\Domain\Repositories;
use Modules\Shared\Repositories\BaseRepositoryInterface;

interface LogRepositoryInterface extends BaseRepositoryInterface
{
    public function filter(array $filters);
}
