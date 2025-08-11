<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GritApplication extends Model
{
    use HasUuids;

    protected $fillable = [
        'grit_id',
        'professional_id',
        'status'
    ];

    public function grit()
    {
        return $this->belongsTo(Grit::class);
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    // Helper method to check if application can be withdrawn
    public function canBeWithdrawn(): bool
    {
        return $this->status === 'pending';
    }
} 
