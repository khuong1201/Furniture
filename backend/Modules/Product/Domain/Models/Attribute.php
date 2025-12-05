<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Attribute extends Model 
{
    protected $fillable = ['uuid', 'name', 'slug', 'type'];

    protected static function boot(): void 
    { 
        parent::boot(); 
        static::creating(fn($m) => $m->uuid = (string) Str::uuid()); 
    }

    public function values(): HasMany 
    { 
        return $this->hasMany(AttributeValue::class); 
    }
}