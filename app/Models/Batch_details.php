<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch_details extends Model
{
    use HasFactory;

    protected $fillable = [
        'department',
        'semister',
        'bteb_roll',
        'session'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
