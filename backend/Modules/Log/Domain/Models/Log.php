<?php
namespace Modules\Log\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'action', 'model', 'model_uuid',
        'ip_address', 'message', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function newFactory()
    {
        return \Modules\Log\Database\Factories\LogFactory::new();
    }
}
