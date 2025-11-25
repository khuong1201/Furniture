<?php

namespace Modules\Category\Domain\Repositories;

use Modules\Category\Domain\Models\Category;
use Modules\Shared\Repositories\BaseRepositoryInterface;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function allRootWithChildren();
}
