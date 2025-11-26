<?php

namespace Modules\Category\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Category\Database\Factories\CategoryFactory;
class Category extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'categories';

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'parent_id',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    protected static function newFactory()
    {
        return CategoryFactory::new();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
