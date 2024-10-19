<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'last_login_at',
        'devices', 
        'browsing_activity', 
        'blocked_users', 
        'two_factor_enabled', 
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
