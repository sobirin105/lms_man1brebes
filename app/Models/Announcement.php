<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'is_active',
        'user_id',
        'target_role'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
