<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'type',
        'value_type',
        'value',
        'min_order_amount',
        'max_discount',
        'category_id',
        'product_id',
        'usage_limit_per_user',
        'usage_limit_total',
        'times_used',
        'starts_at',
        'expires_at',
        'is_active',
        'requires_task'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'times_used' => 'integer',
        'usage_limit_per_user' => 'integer',
        'usage_limit_total' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'requires_task' => 'boolean'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function usage(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();
        if ($now->lt($this->starts_at) || $now->gt($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit_total && $this->times_used >= $this->usage_limit_total) {
            return false;
        }

        return true;
    }

    public function canBeUsedByUser(User $user): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        if ($this->usage_limit_per_user) {
            $userUsage = $this->usage()->where('user_id', $user->id)->count();
            if ($userUsage >= $this->usage_limit_per_user) {
                return false;
            }
        }

        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if ($amount < $this->min_order_amount) {
            return 0;
        }

        $discount = $this->value_type === 'percentage'
            ? $amount * ($this->value / 100)
            : $this->value;

        if ($this->max_discount) {
            $discount = min($discount, $this->max_discount);
        }

        return $discount;
    }

    /**
     * Check if a user has completed the required task for this coupon.
     * This method verifies that the user has completed a conversion task
     * that is associated with this coupon.
     */
    public function hasUserCompletedRequiredTask(User $user): bool
    {
        if (!$this->requires_task) {
            return true; // No task required, so always valid
        }

        // Find ALL conversion tasks that use this coupon
        $conversionTasks = \App\Models\ConversionTask::where('coupon_id', $this->id)->get();
        
        if ($conversionTasks->isEmpty()) {
            return false; // No tasks found for this coupon
        }

        // Check if user has completed ANY of the tasks that use this coupon
        $userTask = \App\Models\UserConversionTask::where('user_id', $user->id)
            ->whereIn('conversion_task_id', $conversionTasks->pluck('id'))
            ->where('status', 'completed')
            ->first();

        return $userTask !== null;
    }
} 