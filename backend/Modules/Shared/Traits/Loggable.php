<?php

namespace Modules\Shared\Traits;
use Modules\Log\Events\ModelActionLogged;

trait Loggable
{
    public static function bootLoggable(): void
    {
        static::created(fn($model) => event(new ModelActionLogged(auth()->id(), 'create', get_class($model), $model->uuid ?? $model->id, request()->ip())));
        static::updated(fn($model) => event(new ModelActionLogged(auth()->id(), 'update', get_class($model), $model->uuid ?? $model->id, request()->ip(), $model->getChanges())));
        static::deleted(fn($model) => event(new ModelActionLogged(auth()->id(), 'delete', get_class($model), $model->uuid ?? $model->id, request()->ip())));
    }
}
