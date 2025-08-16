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
        'reply_to_message_id',
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

    /**
     * Get the message this is replying to
     */
    public function replyTo()
    {
        return $this->belongsTo(GritMessage::class, 'reply_to_message_id');
    }

    /**
     * Get all replies to this message
     */
    public function replies()
    {
        return $this->hasMany(GritMessage::class, 'reply_to_message_id');
    }
} 
