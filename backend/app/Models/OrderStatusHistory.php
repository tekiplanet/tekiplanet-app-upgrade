<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    use HasUuids;

    protected $table = 'order_status_history';

    protected $fillable = [
        'order_id',
        'status',
        'notes'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
} 