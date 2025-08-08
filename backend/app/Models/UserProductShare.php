<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserProductShare extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'user_conversion_task_id',
        'product_id',
        'share_link',
        'shared_at',
        'purchase_count',
        'status'
    ];

    protected $casts = [
        'shared_at' => 'datetime',
        'purchase_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userConversionTask(): BelongsTo
    {
        return $this->belongsTo(UserConversionTask::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(ProductSharePurchase::class);
    }

    /**
     * Generate a unique share link for this product share.
     */
    public function generateShareLink(): string
    {
        $baseUrl = config('app.frontend_url', 'https://app.tekiplanet.org');
        return $baseUrl . '/products/' . $this->product_id . '?share=' . $this->id;
    }

    /**
     * Check if this share has reached its target.
     */
    public function hasReachedTarget(): bool
    {
        $target = $this->userConversionTask->task->share_target ?? 1;
        return $this->purchase_count >= $target;
    }

    /**
     * Increment purchase count and check if task should be completed.
     */
    public function incrementPurchaseCount(): void
    {
        $this->increment('purchase_count');
        
        if ($this->hasReachedTarget()) {
            $this->status = 'completed';
            $this->save();
            
            // Mark the user conversion task as completed
            $userTask = $this->userConversionTask;
            $userTask->status = 'completed';
            $userTask->completed_at = now();
            $userTask->share_count = $this->purchase_count;
            $userTask->save();
        }
    }
}
