<?php

namespace Modules\Collection\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Collection\Domain\Repositories\CollectionRepositoryInterface;
use Modules\Collection\Domain\Models\Collection;

class EloquentCollectionRepository extends EloquentBaseRepository implements CollectionRepositoryInterface
{
    public function __construct(Collection $model) {
        parent::__construct($model);
    }

    public function getModel(): string
    {
        return Collection::class;
    }
}