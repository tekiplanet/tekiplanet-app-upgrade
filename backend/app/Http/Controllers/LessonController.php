<?php

namespace App\Http\Controllers;

use App\Models\CourseLesson;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    /**
     * Get lesson details
     */
    public function show($lessonId)
    {
        try {
            $lesson = CourseLesson::with(['module.course'])->findOrFail($lessonId);
            
            // Check if user has access to this lesson
            $user = Auth::user();
            $course = $lesson->module->course;
            
            // Check if user is enrolled in the course
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
            
            if (!$enrollment && !$lesson->is_preview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be enrolled in this course to access this lesson'
                ], 403);
            }
            
            return response()->json([
                'success' => true,
                'lesson' => $lesson
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson not found'
            ], 404);
        }
    }

    /**
     * Mark lesson as complete
     */
    public function markComplete($lessonId)
    {
        try {
            $user = Auth::user();
            $lesson = CourseLesson::with(['module.course'])->findOrFail($lessonId);
            $course = $lesson->module->course;
            
            // Check if user is enrolled
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
            
            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be enrolled in this course to mark lessons as complete'
                ], 403);
            }
            
            // Check if lesson is already completed
            $existingProgress = LessonProgress::where('user_id', $user->id)
                ->where('lesson_id', $lessonId)
                ->first();
            
            if ($existingProgress) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lesson already marked as complete',
                    'data' => [
                        'lesson_id' => $lessonId,
                        'completed_at' => $existingProgress->completed_at,
                        'progress_percentage' => $this->calculateCourseProgress($user->id, $course->id)
                    ]
                ]);
            }
            
            // Mark lesson as complete
            LessonProgress::create([
                'user_id' => $user->id,
                'lesson_id' => $lessonId,
                'course_id' => $course->id,
                'completed_at' => now()
            ]);
            
            // Calculate updated progress
            $progressPercentage = $this->calculateCourseProgress($user->id, $course->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Lesson marked as complete',
                'data' => [
                    'lesson_id' => $lessonId,
                    'completed_at' => now()->toISOString(),
                    'progress_percentage' => $progressPercentage
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark lesson as complete',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get lesson progress for a course
     */
    public function getCourseProgress($courseId)
    {
        try {
            $user = Auth::user();
            
            // Check if user is enrolled
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->first();
            
            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be enrolled in this course to view progress'
                ], 403);
            }
            
            $completedLessons = LessonProgress::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->pluck('lesson_id')
                ->toArray();
            
            $totalLessons = CourseLesson::whereHas('module', function($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })->count();
            
            $progressPercentage = $totalLessons > 0 ? (count($completedLessons) / $totalLessons) * 100 : 0;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'completed_lessons' => $completedLessons,
                    'total_lessons' => $totalLessons,
                    'progress_percentage' => round($progressPercentage, 2)
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch lesson progress',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get next lesson in sequence
     */
    public function getNextLesson($currentLessonId)
    {
        try {
            $user = Auth::user();
            $currentLesson = CourseLesson::with(['module.course'])->findOrFail($currentLessonId);
            $course = $currentLesson->module->course;
            
            // Check if user is enrolled
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
            
            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be enrolled in this course'
                ], 403);
            }
            
            // Get all lessons in the course ordered by module and lesson order
            $allLessons = CourseLesson::whereHas('module', function($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->with(['module'])
            ->orderBy('order')
            ->get();
            
            $currentIndex = -1;
            foreach ($allLessons as $index => $lesson) {
                if ($lesson->id === $currentLessonId) {
                    $currentIndex = $index;
                    break;
                }
            }
            
            if ($currentIndex === -1 || $currentIndex >= $allLessons->count() - 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'No next lesson available'
                ], 404);
            }
            
            $nextLesson = $allLessons[$currentIndex + 1];
            
            return response()->json([
                'success' => true,
                'lesson' => $nextLesson
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get next lesson',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get previous lesson in sequence
     */
    public function getPreviousLesson($currentLessonId)
    {
        try {
            $user = Auth::user();
            $currentLesson = CourseLesson::with(['module.course'])->findOrFail($currentLessonId);
            $course = $currentLesson->module->course;
            
            // Check if user is enrolled
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
            
            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be enrolled in this course'
                ], 403);
            }
            
            // Get all lessons in the course ordered by module and lesson order
            $allLessons = CourseLesson::whereHas('module', function($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->with(['module'])
            ->orderBy('order')
            ->get();
            
            $currentIndex = -1;
            foreach ($allLessons as $index => $lesson) {
                if ($lesson->id === $currentLessonId) {
                    $currentIndex = $index;
                    break;
                }
            }
            
            if ($currentIndex === -1 || $currentIndex <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No previous lesson available'
                ], 404);
            }
            
            $previousLesson = $allLessons[$currentIndex - 1];
            
            return response()->json([
                'success' => true,
                'lesson' => $previousLesson
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get previous lesson',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user has access to a lesson
     */
    public function checkAccess($lessonId)
    {
        try {
            $user = Auth::user();
            $lesson = CourseLesson::with(['module.course'])->findOrFail($lessonId);
            $course = $lesson->module->course;
            
            // Check if user is enrolled
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
            
            $hasAccess = $enrollment || $lesson->is_preview;
            $reason = null;
            
            if (!$hasAccess) {
                $reason = 'You must be enrolled in this course to access this lesson';
            }
            
            return response()->json([
                'success' => true,
                'has_access' => $hasAccess,
                'reason' => $reason
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'has_access' => false,
                'reason' => 'Lesson not found'
            ], 404);
        }
    }

    /**
     * Calculate course progress percentage
     */
    private function calculateCourseProgress($userId, $courseId)
    {
        $completedLessons = LessonProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->count();
        
        $totalLessons = CourseLesson::whereHas('module', function($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->count();
        
        return $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0;
    }
} 