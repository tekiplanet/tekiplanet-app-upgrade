<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ConversionTaskReward extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'conversion_task_id',
        'amount',
        'coupon_code',
        'course_id',
        'discount_percent',
        'details',
    ];

    public function task()
    {
        return $this->belongsTo(ConversionTask::class, 'conversion_task_id');
    }
}
