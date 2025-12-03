<?php
namespace Modules\Product\Infrastructure\Repositories;
use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Product\Domain\Repositories\AttributeRepositoryInterface;
use Modules\Product\Domain\Models\Attribute;

class EloquentAttributeRepository extends EloquentBaseRepository implements AttributeRepositoryInterface {
    public function __construct(Attribute $model)
    {
        parent::__construct($model);
    }
}