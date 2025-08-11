<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GritDispute extends Model
{
    use HasUuids;

    protected $fillable = [
        'grit_id',
        'dispute_starter_id',
        'dispute_reason',
        'desired_outcome',
        'status',
        'resolved_at',
        'resolution_details',
        'winner_id',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function grit()
    {
        return $this->belongsTo(Grit::class);
    }

    public function starter()
    {
        return $this->belongsTo(User::class, 'dispute_starter_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }
}
