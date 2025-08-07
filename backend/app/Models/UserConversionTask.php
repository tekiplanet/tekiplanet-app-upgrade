<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserConversionTask extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'conversion_task_id',
        'status',
        'claimed',
        'claimed_at',
        'assigned_at',
        'completed_at',
        'referral_count',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function task()
    {
        return $this->belongsTo(ConversionTask::class, 'conversion_task_id');
    }

    /**
     * Generate a unique referral link for this user conversion task.
     */
    public function getReferralLink()
    {
        // Use the frontend URL for referral links
        $baseUrl = config('app.frontend_url', 'https://app.tekiplanet.org');
        return $baseUrl . '/register?ref=' . $this->user_id . '&task=' . $this->id;
    }
}
