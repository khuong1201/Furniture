<?php

declare(strict_types=1);

namespace Modules\Collection\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Modules\Product\Domain\Models\Product;
use Modules\Shared\Traits\Loggable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Collection extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = [
        'uuid', 'name', 'slug', 'description', 'banner_image', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(fn(Collection $model) => $model->uuid = (string) Str::uuid());
        
        static::saving(function (Collection $model) {
            if ($model->isDirty('name') && empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'collection_product');
    }
    
    protected static function newFactory()
    {
        return \Modules\Collection\Database\factories\CollectionFactory::new();
    }
}