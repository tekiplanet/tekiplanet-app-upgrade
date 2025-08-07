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
        // You may want to use route() if you have a named route for registration
        $baseUrl = config('app.url', 'https://yourdomain.com');
        return $baseUrl . '/register?ref=' . $this->user_id . '&task=' . $this->id;
    }
}
