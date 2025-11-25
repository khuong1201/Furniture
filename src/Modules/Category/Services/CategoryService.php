<?php

namespace Modules\Category\Services;

use Modules\Shared\Services\BaseService;
use Modules\Category\Domain\Repositories\CategoryRepositoryInterface;
use Modules\Category\Domain\Models\Category;

class CategoryService extends BaseService
{
    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getAll()
    {
        return $this->repository->allRootWithChildren();
    }
    
    public function findByUuid(string $uuid)
    {
        return $this->repository->findByUuid($uuid);
    }
}
