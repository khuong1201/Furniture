<?php
namespace Modules\Product\Domain\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attribute extends Model {
    protected $fillable = ['uuid', 'name', 'slug', 'type'];
    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->uuid = (string) Str::uuid());
    }
    public function values() { return $this->hasMany(AttributeValue::class); }
}