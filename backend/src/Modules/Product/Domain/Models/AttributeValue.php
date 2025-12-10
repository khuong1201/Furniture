<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AttributeValue extends Model 
{
    protected $fillable = ['uuid', 'attribute_id', 'value', 'code'];

    protected static function boot(): void 
    { 
        parent::boot(); 
        static::creating(fn($m) => $m->uuid = (string) Str::uuid()); 
    }

    public function attribute(): BelongsTo 
    { 
        return $this->belongsTo(Attribute::class); 
    }
}