<?php
namespace Modules\Log\Domain\Repositories;

interface LogRepositoryInterface
{
    public function filter(array $filters);
}
