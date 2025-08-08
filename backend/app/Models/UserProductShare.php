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
        'expires_at',
        'purchase_count',
        'click_count',
        'visitor_session_id',
        'status'
    ];

    protected $casts = [
        'shared_at' => 'datetime',
        'expires_at' => 'datetime',
        'purchase_count' => 'integer',
        'click_count' => 'integer',
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

    public function visits(): HasMany
    {
        return $this->hasMany(ShareLinkVisit::class);
    }

    /**
     * Generate a unique share link for this product share.
     */
    public function generateShareLink(): string
    {
        $baseUrl = config('app.frontend_url', 'https://app.tekiplanet.org');
        return $baseUrl . '/dashboard#/dashboard/store/product/' . $this->product_id . '?share=' . $this->id;
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
     * Check if this share link has expired.
     */
    public function hasExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if this share link is still active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->hasExpired();
    }

    /**
     * Get the conversion rate for this share link.
     */
    public function getConversionRate(): float
    {
        if ($this->click_count === 0) {
            return 0.0;
        }
        
        return round(($this->purchase_count / $this->click_count) * 100, 2);
    }

    /**
     * Record a visit to this share link.
     */
    public function recordVisit(string $visitorIp = null, string $userAgent = null, string $referrer = null): void
    {
        $this->increment('click_count');
        
        // Create visit record
        $this->visits()->create([
            'visitor_ip' => $visitorIp,
            'user_agent' => $userAgent,
            'referrer' => $referrer,
            'visited_at' => now(),
        ]);
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

    /**
     * Set expiration date for this share link (default 30 days).
     */
    public function setExpiration(int $days = 30): void
    {
        $this->expires_at = now()->addDays($days);
        $this->save();
    }
}
