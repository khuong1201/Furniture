<?php

namespace Modules\Category\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Category\Domain\Repositories\CategoryRepositoryInterface;
use Modules\Category\Domain\Models\Category;

class EloquentCategoryRepository extends EloquentBaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function allRootWithChildren()
    {
        return $this->model->with('children')->whereNull('parent_id')->get();
    }

    // public function findByUuid($uuid)
    // {
    //     return $this->model->where('uuid', $uuid)->first(); 
    // }
}
