<?php
namespace Modules\Product\Domain\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AttributeValue extends Model {
    protected $fillable = ['uuid', 'attribute_id', 'value', 'code'];
    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->uuid = (string) Str::uuid());
    }
    public function attribute() { return $this->belongsTo(Attribute::class); }
}