<?php

namespace Modules\Category\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Shared\Traits\Loggable;

class Category extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = [
        'uuid', 'name', 'slug', 'description', 'parent_id',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
        
        static::updating(function ($model) {
             if ($model->isDirty('name') && !$model->isDirty('slug')) {
                 $model->slug = Str::slug($model->name);
             }
        });
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    protected static function newFactory()
    {
        return \Modules\Category\Database\factories\CategoryFactory::new();
    }
}