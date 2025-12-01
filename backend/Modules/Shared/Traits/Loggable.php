<?php

namespace Modules\Shared\Traits;

use Modules\Log\Events\ModelActionLogged;
use Illuminate\Support\Facades\Auth;

trait Loggable
{
    public static function bootLoggable(): void
    {
        if (!app()->runningInConsole() || app()->environment('testing')) {
            static::created(function ($model) {
                static::logAction($model, 'created');
            });

            static::updated(function ($model) {
                if ($model->wasChanged()) { 
                    static::logAction($model, 'updated', $model->getChanges());
                }
            });

            static::deleted(function ($model) {
                static::logAction($model, 'deleted');
            });
        }
    }

    protected static function logAction($model, string $action, array $changes = []): void
    {
        event(new ModelActionLogged(
            Auth::id(),
            $action,
            get_class($model),
            $model->uuid ?? $model->id,
            request()->ip(),
            $changes
        ));
    }

    public function withoutLogging(\Closure $callback)
    {
        static::flushEventListeners();
        $result = $callback();
        static::bootLoggable();
        
        return $result;
    }
}