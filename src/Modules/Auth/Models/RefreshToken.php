<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Auth\Database\Factories\RefreshTokenFactory;

class RefreshToken extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'token', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(\App\Models\User::class);
    }
}
