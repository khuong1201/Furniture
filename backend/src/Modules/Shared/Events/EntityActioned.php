<?php

namespace Modules\Shared\Events;

use Illuminate\Database\Eloquent\Model;

class EntityActioned
{
    public function __construct(
        public Model $model,
        public string $action, 
        public ?int $userId,
        public array $changes = []
    ) {}
}