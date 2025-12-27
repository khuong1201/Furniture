<?php

declare(strict_types=1);

namespace Modules\Warehouse\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Shared\Traits\Loggable;
use Modules\User\Domain\Models\User;
use Modules\Inventory\Domain\Models\InventoryStock; 

class Warehouse extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = ['uuid', 'name', 'location', 'manager_id', 'is_active', 'contact_email',
    'contact_phone',];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn(Warehouse $model) => $model->uuid = (string) Str::uuid());
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class, 'warehouse_id');
    }

    // Accessor: Tổng số lượng item đang lưu trữ
    public function getTotalItemsAttribute(): int
    {
        return (int) $this->stocks()->sum('quantity');
    }
}