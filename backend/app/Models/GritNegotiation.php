<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GritNegotiation extends Model
{
    use HasUuids;

    protected $fillable = [
        'grit_id',
        'user_id',
        'proposed_budget',
        'proposed_deadline',
        'message',
        'status',
    ];

    protected $casts = [
        'proposed_budget' => 'decimal:2',
        'proposed_deadline' => 'date',
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
