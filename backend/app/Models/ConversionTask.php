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
}
