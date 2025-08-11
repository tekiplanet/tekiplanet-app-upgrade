<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GritEscrowTransaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'grit_id',
        'user_id',
        'transaction_type',
        'amount',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
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
