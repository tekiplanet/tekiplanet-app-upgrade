<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Carbon\Carbon;

class UserCourseShare extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'user_conversion_task_id',
        'course_id',
        'share_link',
        'shared_at',
        'enrollment_count',
        'status',
        'expires_at',
        'click_count',
        'visitor_session_id',
    ];

    protected $casts = [
        'shared_at' => 'datetime',
        'expires_at' => 'datetime',
        'enrollment_count' => 'integer',
        'click_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userConversionTask()
    {
        return $this->belongsTo(UserConversionTask::class, 'user_conversion_task_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollments()
    {
        return $this->hasMany(CourseShareEnrollment::class, 'user_course_share_id');
    }

    public function visits()
    {
        return $this->hasMany(CourseShareVisit::class, 'user_course_share_id');
    }

    /**
     * Check if the share link has expired (7 days from creation)
     */
    public function hasExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the share link is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->hasExpired();
    }

    /**
     * Get conversion rate (enrollments / clicks)
     */
    public function getConversionRate(): float
    {
        if ($this->click_count === 0) {
            return 0.0;
        }
        return round(($this->enrollment_count / $this->click_count) * 100, 2);
    }

    /**
     * Record a visit to this share link
     */
    public function recordVisit(string $visitorIp = null, string $userAgent = null, string $referrer = null): void
    {
        $this->increment('click_count');
        $this->visits()->create([
            'visitor_ip' => $visitorIp,
            'user_agent' => $userAgent,
            'referrer' => $referrer,
            'visited_at' => now(),
        ]);
    }

    /**
     * Record an enrollment through this share link
     */
    public function recordEnrollment(string $enrollmentId, string $enrolledUserId, float $enrollmentAmount): void
    {
        $this->increment('enrollment_count');
        $this->enrollments()->create([
            'enrollment_id' => $enrollmentId,
            'enrolled_user_id' => $enrolledUserId,
            'enrolled_at' => now(),
            'enrollment_amount' => $enrollmentAmount,
            'status' => 'completed',
        ]);

        // Check if target is reached and mark task as completed
        $this->checkAndCompleteTask();
    }

    /**
     * Check if enrollment target is reached and complete the task
     */
    private function checkAndCompleteTask(): void
    {
        $task = $this->userConversionTask;
        if (!$task) return;

        $conversionTask = $task->task;
        if (!$conversionTask) return;

        $target = $conversionTask->enrollment_target ?? 1;
        
        if ($this->enrollment_count >= $target && $task->status !== 'completed') {
            // Update both the task status and enrollment count
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
                'enrollment_count' => $this->enrollment_count, // Update the enrollment count
            ]);

            \Log::info('Course share task completed', [
                'user_conversion_task_id' => $task->id,
                'user_id' => $this->user_id,
                'course_id' => $this->course_id,
                'enrollment_count' => $this->enrollment_count,
                'target' => $target,
            ]);
        } else {
            // Update enrollment count even if target not reached yet
            $task->update([
                'enrollment_count' => $this->enrollment_count,
            ]);
        }
    }
}
