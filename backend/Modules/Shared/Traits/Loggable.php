<?php

namespace Modules\Shared\Traits;

use Modules\Log\Events\ModelActionLogged;
use Illuminate\Support\Facades\Auth;

trait Loggable
{
    public static function bootLoggable(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        static::created(function ($model) {
            static::logAction($model, 'created');
        });

        static::updated(function ($model) {
            if ($model->wasChanged()) { 
                $changes = $model->getChanges();
                unset($changes['updated_at']);
                
                if (!empty($changes)) {
                    static::logAction($model, 'updated', $changes);
                }
            }
        });

        static::deleted(function ($model) {
            static::logAction($model, 'deleted');
        });
    }

    protected static function logAction($model, string $action, array $changes = []): void
    {
        $ip = request() ? request()->ip() : '127.0.0.1';
        
        $uuid = $model->uuid ?? (string)$model->id;

        event(new ModelActionLogged(
            Auth::id(), 
            $action,
            get_class($model),
            $uuid,
            $ip,
            $changes
        ));
    }
}