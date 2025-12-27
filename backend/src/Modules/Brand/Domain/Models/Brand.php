<?php

declare(strict_types=1);

namespace Modules\Brand\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Shared\Traits\Loggable;
use Modules\Product\Domain\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Brand\database\factories\BrandFactory;

class Brand extends Model
{
    use SoftDeletes, Loggable, HasFactory;

    protected $fillable = [
        'uuid', 'name', 'slug', 'description', 
        'logo_url', 'public_id', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn(Brand $m) => $m->uuid = (string) Str::uuid());
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected static function newFactory()
    {
        return BrandFactory::new();
    }

}