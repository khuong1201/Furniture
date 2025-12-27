<?php

declare(strict_types=1);

namespace Modules\Media\Infrastructure\Repositories;

use Modules\Media\Domain\Models\Media;
use Modules\Media\Domain\Repositories\MediaRepositoryInterface;
use Modules\Shared\Repositories\EloquentBaseRepository;

class EloquentMediaRepository extends EloquentBaseRepository implements MediaRepositoryInterface
{
    public function __construct(Media $model)
    {
        parent::__construct($model);
    }
}