<?php

namespace App\Services;

use App\Models\User;
use App\Models\Course;
use App\Models\UserConversionTask;
use App\Models\LessonProgress;
use App\Models\CourseLesson;
use Illuminate\Support\Facades\Log;

class CourseCompletionService
{
    /**
     * Calculate course completion percentage for a user
     */
    public function calculateCourseCompletion(User $user, Course $course): float
    {
        // Only count non-preview lessons
        $totalLessons = CourseLesson::whereHas('module', function ($query) use ($course) {
            $query->where('course_id', $course->id);
        })->where('is_preview', false)->count();
        
        if ($totalLessons === 0) {
            return 0.0;
        }
        
        $completedLessons = LessonProgress::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereHas('lesson', function ($query) {
                $query->where('is_preview', false);
            })
            ->count();
        
        return round(($completedLessons / $totalLessons) * 100, 2);
    }
    
    /**
     * Check if a user has completed a course to the required percentage
     */
    public function hasCompletedCourse(User $user, Course $course, int $requiredPercentage): bool
    {
        $completionPercentage = $this->calculateCourseCompletion($user, $course);
        return $completionPercentage >= $requiredPercentage;
    }
    
    /**
     * Check and complete a course completion task
     */
    public function checkAndCompleteTask(UserConversionTask $userTask): bool
    {
        $task = $userTask->task;
        if (!$task || strtolower($task->type->name) !== 'complete course') {
            return false;
        }
        
        if (!$task->taskCourse) {
            Log::warning('Complete course task has no course assigned', [
                'user_conversion_task_id' => $userTask->id,
                'task_id' => $task->id
            ]);
            return false;
        }
        
        $requiredPercentage = $task->completion_percentage ?? 100;
        $user = $userTask->user;
        $course = $task->taskCourse;
        
        // Check if user has completed the course to the required percentage
        if ($this->hasCompletedCourse($user, $course, $requiredPercentage)) {
            // Mark task as completed
            $userTask->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            
            Log::info('Course completion task completed', [
                'user_conversion_task_id' => $userTask->id,
                'user_id' => $user->id,
                'course_id' => $course->id,
                'required_percentage' => $requiredPercentage,
                'actual_percentage' => $this->calculateCourseCompletion($user, $course)
            ]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get course completion progress for a user
     */
    public function getCourseProgress(User $user, Course $course): array
    {
        $completionPercentage = $this->calculateCourseCompletion($user, $course);
        
        // Get total and completed lesson counts
        $totalLessons = CourseLesson::whereHas('module', function ($query) use ($course) {
            $query->where('course_id', $course->id);
        })->where('is_preview', false)->count();
        
        $completedLessons = LessonProgress::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereHas('lesson', function ($query) {
                $query->where('is_preview', false);
            })
            ->count();
        
        return [
            'completion_percentage' => $completionPercentage,
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'remaining_lessons' => $totalLessons - $completedLessons
        ];
    }
    
    /**
     * Check if user is enrolled in a course
     */
    public function isEnrolledInCourse(User $user, Course $course): bool
    {
        return \App\Models\Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();
    }
}
