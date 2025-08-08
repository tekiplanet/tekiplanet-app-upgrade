<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSharePurchase extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_product_share_id',
        'order_id',
        'purchaser_user_id',
        'purchased_at',
        'order_amount',
        'status'
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'order_amount' => 'decimal:2',
    ];

    public function userProductShare(): BelongsTo
    {
        return $this->belongsTo(UserProductShare::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchaser_user_id');
    }

    /**
     * Mark this purchase as completed and update the share count.
     */
    public function markAsCompleted(): void
    {
        $this->status = 'completed';
        $this->purchased_at = now();
        $this->save();

        // Increment the purchase count on the user product share
        $this->userProductShare->incrementPurchaseCount();
    }
}
