<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class QuoteMessage extends Model
{
    use HasUuids;

    protected $fillable = [
        'quote_id',
        'user_id',
        'message',
        'sender_type', // 'admin' or 'user'
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime'
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 