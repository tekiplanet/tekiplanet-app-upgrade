<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DiscountSlip extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'user_conversion_task_id',
        'service_name',
        'discount_percent',
        'discount_code',
        'expires_at',
        'is_used',
        'used_at',
        'terms_conditions',
        'metadata',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userConversionTask()
    {
        return $this->belongsTo(UserConversionTask::class);
    }

    /**
     * Check if the discount slip is valid (not expired and not used)
     */
    public function isValid(): bool
    {
        return !$this->is_used && $this->expires_at->isFuture();
    }

    /**
     * Check if the discount slip is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Mark the discount slip as used
     */
    public function markAsUsed(): void
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
        ]);
    }

    /**
     * Generate a unique discount code
     */
    public static function generateDiscountCode(): string
    {
        do {
            $code = 'DISC-' . strtoupper(Str::random(8));
        } while (self::where('discount_code', $code)->exists());

        return $code;
    }

    /**
     * Create a discount slip for a user conversion task
     */
    public static function createForTask(UserConversionTask $userTask, string $serviceName, int $discountPercent): self
    {
        return self::create([
            'user_id' => $userTask->user_id,
            'user_conversion_task_id' => $userTask->id,
            'service_name' => $serviceName,
            'discount_percent' => $discountPercent,
            'discount_code' => self::generateDiscountCode(),
            'expires_at' => now()->addDays(7), // 7 days from creation
            'terms_conditions' => "This discount is valid for {$serviceName} services only. Valid for 7 days from issue date.",
        ]);
    }

    /**
     * Get the discount slip by code
     */
    public static function findByCode(string $code): ?self
    {
        return self::where('discount_code', $code)->first();
    }

    /**
     * Get active discount slips for a user
     */
    public static function getActiveForUser(string $userId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('user_id', $userId)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
