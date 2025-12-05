<?php

declare(strict_types=1);

namespace Modules\Media\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'uuid', 'model_type', 'model_id', 'collection_name',
        'file_name', 'mime_type', 'disk', 'size', 
        'url', 'public_id', 'custom_properties'
    ];

    protected $casts = [
        'custom_properties' => 'array',
        'size' => 'integer'
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn(Media $model) => $model->uuid = (string) Str::uuid());
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}