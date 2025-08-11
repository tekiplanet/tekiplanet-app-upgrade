<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GritDisputeEvidence extends Model
{
    use HasUuids;

    protected $fillable = [
        'grit_dispute_id',
        'user_id',
        'evidence_type',
        'content',
    ];

    public function dispute()
    {
        return $this->belongsTo(GritDispute::class, 'grit_dispute_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
