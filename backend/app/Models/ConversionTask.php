<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ConversionTask extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title',
        'description',
        'task_type_id',
        'min_points',
        'max_points',
        'reward_type_id',
        'product_id',
        'coupon_id',
        'course_id',
        'task_course_id',
        'cash_amount',
        'discount_percent',
        'service_name',
        'referral_target',
        'share_target',
        'enrollment_target',
        'completion_percentage',
    ];

    public function type()
    {
        return $this->belongsTo(ConversionTaskType::class, 'task_type_id');
    }

    public function rewardType()
    {
        return $this->belongsTo(ConversionRewardType::class, 'reward_type_id');
    }

    public function rewards()
    {
        return $this->hasMany(ConversionTaskReward::class, 'conversion_task_id');
    }

    public function userTasks()
    {
        return $this->hasMany(UserConversionTask::class, 'conversion_task_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function taskCourse()
    {
        return $this->belongsTo(Course::class, 'task_course_id');
    }
}
