<?php

namespace Modules\Category\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Modules\Category\Domain\Models\Category;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getTree();
}