<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserReferral extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'referrer_user_id',
        'referred_user_id',
        'user_conversion_task_id',
        'registered_at',
        'status',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function userConversionTask()
    {
        return $this->belongsTo(UserConversionTask::class, 'user_conversion_task_id');
    }
}
