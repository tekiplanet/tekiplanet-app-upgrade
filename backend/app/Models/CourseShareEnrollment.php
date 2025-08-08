<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CourseShareEnrollment extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_course_share_id',
        'enrollment_id',
        'enrolled_user_id',
        'enrolled_at',
        'enrollment_amount',
        'status',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'enrollment_amount' => 'decimal:2',
    ];

    public function userCourseShare()
    {
        return $this->belongsTo(UserCourseShare::class, 'user_course_share_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function enrolledUser()
    {
        return $this->belongsTo(User::class, 'enrolled_user_id');
    }
}
