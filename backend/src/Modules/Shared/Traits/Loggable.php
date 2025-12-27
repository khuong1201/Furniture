<?php

declare(strict_types=1);

namespace Modules\Shared\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\Shared\Events\EntityActioned;

trait Loggable
{
    public static function bootLoggable(): void
    {
        static::created(fn (Model $m) => static::fireLog($m, 'created'));
        static::deleted(fn (Model $m) => static::fireLog($m, 'deleted'));
        
        static::updated(function (Model $m) {
            if ($m->wasChanged()) {
                $changes = $m->getChanges();
                unset($changes['updated_at']);
                if (!empty($changes)) {
                    static::fireLog($m, 'updated', $changes);
                }
            }
        });
    }

    protected static function fireLog(Model $model, string $action, array $changes = []): void
    {
        event(new EntityActioned(
            $model,
            $action,
            Auth::id(),
            $changes
        ));
    }
}