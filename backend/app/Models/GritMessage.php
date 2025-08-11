<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GritMessage extends Model
{
    use HasUuids;

    protected $fillable = [
        'grit_id',
        'user_id',
        'message',
        'sender_type',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean'
    ];

    public function grit()
    {
        return $this->belongsTo(Grit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 
