<?php

declare(strict_types=1);

namespace Modules\Shared\Traits;

use Modules\Log\Events\ModelActionLogged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

trait Loggable
{
    public static function bootLoggable(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        static::created(function (Model $model) {
            static::logAction($model, 'created');
        });

        static::updated(function (Model $model) {
            if ($model->wasChanged()) { 
                $changes = $model->getChanges();
                unset($changes['updated_at']);
                
                if (!empty($changes)) {
                    static::logAction($model, 'updated', $changes);
                }
            }
        });

        static::deleted(function (Model $model) {
            static::logAction($model, 'deleted');
        });
    }

    protected static function logAction(Model $model, string $action, array $changes = []): void
    {
        if (!class_exists(ModelActionLogged::class)) {
            return;
        }

        $ip = request()?->ip() ?? '127.0.0.1';
        $userId = Auth::id(); 

        $uuid = $model->getAttribute('uuid') ?? (string) $model->getKey();

        event(new ModelActionLogged(
            $userId, 
            $action,
            get_class($model),
            $uuid,
            $ip,
            $changes
        ));
    }
}