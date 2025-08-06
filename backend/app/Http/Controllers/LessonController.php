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
            
            // Allow access to preview lessons regardless of enrollment
            if ($lesson->is_preview) {
                return response()->json([
                    'success' => true,
                    'lesson' => $lesson
                ]);
            }
            
            // Check lesson progression first - user can only access lessons in sequence
            $hasAccess = $this->checkLessonAccess($user->id, $course->id, $lesson);
            
            if (!$hasAccess['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $hasAccess['message'],
                    'required_lesson' => $hasAccess['required_lesson'] ?? null
                ], 403);
            }
            
            // Check if user is enrolled in the course (only after progression check)
            if (!$enrollment) {
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
            
            // Get all lessons in the course ordered by module order and lesson order
            $allLessons = CourseLesson::whereHas('module', function($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->with(['module'])
            ->get()
            ->sortBy(function ($lesson) {
                return $lesson->module->order . '.' . str_pad($lesson->order, 5, '0', STR_PAD_LEFT);
            })
            ->values();
            
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
            
            // Get all lessons in the course ordered by module order and lesson order
            $allLessons = CourseLesson::whereHas('module', function($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->with(['module'])
            ->get()
            ->sortBy(function ($lesson) {
                return $lesson->module->order . '.' . str_pad($lesson->order, 5, '0', STR_PAD_LEFT);
            })
            ->values();
            
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
            
            // Preview lessons are always accessible
            if ($lesson->is_preview) {
                return response()->json([
                    'success' => true,
                    'has_access' => true,
                    'reason' => null,
                    'access_type' => 'preview'
                ]);
            }
            
            // Check if it's the first lesson of the course
            $isFirstLesson = $this->isFirstLesson($course->id, $lesson->id);
            
            if ($isFirstLesson) {
                return response()->json([
                    'success' => true,
                    'has_access' => true,
                    'reason' => null,
                    'access_type' => 'first_lesson'
                ]);
            }
            
            // Check if user is enrolled
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
            
            // If not enrolled, deny access
            if (!$enrollment) {
                return response()->json([
                    'success' => true,
                    'has_access' => false,
                    'reason' => 'You must be enrolled in this course to access this lesson',
                    'access_type' => 'enrollment_required'
                ]);
            }
            
            // Check lesson progression
            $progressionCheck = $this->checkLessonAccess($user->id, $course->id, $lesson);
            
            if (!$progressionCheck['allowed']) {
                return response()->json([
                    'success' => true,
                    'has_access' => false,
                    'reason' => $progressionCheck['message'],
                    'access_type' => 'progression_blocked',
                    'required_lesson' => $progressionCheck['required_lesson'] ?? null
                ]);
            }
            
            return response()->json([
                'success' => true,
                'has_access' => true,
                'reason' => null,
                'access_type' => 'progression_allowed'
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
     * Check if user has access to a specific lesson based on progression
     */
    private function checkLessonAccess($userId, $courseId, $lesson)
    {
        // Get all lessons in the course ordered by module order and lesson order
        $allLessons = CourseLesson::whereHas('module', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })
        ->with(['module'])
        ->get()
        ->sortBy(function ($lesson) {
            return $lesson->module->order . '.' . str_pad($lesson->order, 5, '0', STR_PAD_LEFT);
        })
        ->values();

        // Find the current lesson index
        $currentLessonIndex = -1;
        foreach ($allLessons as $index => $l) {
            if ($l->id === $lesson->id) {
                $currentLessonIndex = $index;
                break;
            }
        }

        // If lesson not found, deny access
        if ($currentLessonIndex === -1) {
            return [
                'allowed' => false,
                'message' => 'Lesson not found in course.'
            ];
        }

        // If it's the first lesson of the entire course, allow access
        if ($currentLessonIndex === 0) {
            return ['allowed' => true];
        }
        // Remove: If it's the first lesson of its module, allow access
        // Now, check if all previous non-preview lessons in the course are completed
        $completedLessonIds = LessonProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->pluck('lesson_id')
            ->toArray();
        for ($i = 0; $i < $currentLessonIndex; $i++) {
            $prevLesson = $allLessons[$i];
            if (!$prevLesson->is_preview && !in_array($prevLesson->id, $completedLessonIds)) {
                return [
                    'allowed' => false,
                    'message' => 'You must complete the previous lesson before accessing this one.',
                    'required_lesson' => [
                        'id' => $prevLesson->id,
                        'title' => $prevLesson->title,
                        'module_title' => $prevLesson->module->title
                    ]
                ];
            }
        }
        return ['allowed' => true];
    }

    /**
     * Calculate course progress percentage
     */
    private function calculateCourseProgress($userId, $courseId)
    {
        $totalLessons = CourseLesson::whereHas('module', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->count();
        
        $completedLessons = LessonProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->count();
        
        return $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0;
    }

    /**
     * Get quiz questions for a lesson
     */
    public function getQuizQuestions($lessonId)
    {
        try {
            $user = Auth::user();
            $lesson = CourseLesson::with(['module.course'])->findOrFail($lessonId);
            
            // Check if user has access to this lesson
            $course = $lesson->module->course;
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
            
            if (!$enrollment && !$lesson->is_preview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be enrolled in this course to access this quiz'
                ], 403);
            }
            
            // Get quiz questions with answers
            $questions = \App\Models\QuizQuestion::with('answers')
                ->where('lesson_id', $lessonId)
                ->orderBy('order')
                ->get();
            
            return response()->json([
                'success' => true,
                'questions' => $questions
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load quiz questions'
            ], 500);
        }
    }

    /**
     * Start a quiz attempt
     */
    public function startQuizAttempt($lessonId)
    {
        try {
            $user = Auth::user();
            $lesson = CourseLesson::with(['module.course'])->findOrFail($lessonId);
            
            // Check if user has access
            $course = $lesson->module->course;
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
            
            if (!$enrollment && !$lesson->is_preview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be enrolled in this course to take this quiz'
                ], 403);
            }
            
            // Check if there's already an active attempt
            $activeAttempt = \App\Models\QuizAttempt::where('user_id', $user->id)
                ->where('lesson_id', $lessonId)
                ->whereNull('completed_at')
                ->first();
            
            if ($activeAttempt) {
                return response()->json([
                    'success' => true,
                    'attempt' => $activeAttempt
                ]);
            }
            
            // Create new attempt
            $attempt = \App\Models\QuizAttempt::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'user_id' => $user->id,
                'lesson_id' => $lessonId,
                'started_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'attempt' => $attempt
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start quiz attempt'
            ], 500);
        }
    }

    /**
     * Submit quiz answers
     */
    public function submitQuizAnswers($lessonId, Request $request)
    {
        try {
            $user = Auth::user();
            $lesson = CourseLesson::with(['module.course'])->findOrFail($lessonId);
            
            // Check if user has access
            $course = $lesson->module->course;
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
            
            if (!$enrollment && !$lesson->is_preview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be enrolled in this course to submit quiz answers'
                ], 403);
            }
            
            $validated = $request->validate([
                'attempt_id' => 'required|uuid',
                'answers' => 'required|array',
                'answers.*.question_id' => 'required|uuid',
                'answers.*.user_answer' => 'required|string'
            ]);
            
            // Get the attempt
            $attempt = \App\Models\QuizAttempt::where('id', $validated['attempt_id'])
                ->where('user_id', $user->id)
                ->where('lesson_id', $lessonId)
                ->first();
            
            if (!$attempt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz attempt not found'
                ], 404);
            }
            
            if ($attempt->completed_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz already completed'
                ], 400);
            }
            
            // Get all questions for this lesson
            $questions = \App\Models\QuizQuestion::with('answers')
                ->where('lesson_id', $lessonId)
                ->get()
                ->keyBy('id');
            
            $totalPoints = 0;
            $earnedPoints = 0;
            $responses = [];
            
            // Process each answer
            foreach ($validated['answers'] as $answerData) {
                $question = $questions->get($answerData['question_id']);
                if (!$question) continue;
                
                $totalPoints += $question->points;
                $isCorrect = false;
                $pointsEarned = 0;
                
                // Check if answer is correct based on question type
                switch ($question->question_type) {
                    case 'multiple_choice':
                    case 'true_false':
                        // Check if user's answer matches any correct answer
                        $correctAnswers = $question->answers->where('is_correct', true);
                        foreach ($correctAnswers as $correctAnswer) {
                            if (strtolower(trim($answerData['user_answer'])) === strtolower(trim($correctAnswer->answer_text))) {
                                $isCorrect = true;
                                $pointsEarned = $question->points;
                                $earnedPoints += $question->points;
                                break;
                            }
                        }
                        break;
                        
                    case 'short_answer':
                        // Check if user's answer matches any correct answer (case-insensitive)
                        $correctAnswers = $question->answers->where('is_correct', true);
                        foreach ($correctAnswers as $correctAnswer) {
                            if (strtolower(trim($answerData['user_answer'])) === strtolower(trim($correctAnswer->answer_text))) {
                                $isCorrect = true;
                                $pointsEarned = $question->points;
                                $earnedPoints += $question->points;
                                break;
                            }
                        }
                        break;
                }
                
                // Create response record
                $response = \App\Models\QuizResponse::create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'user_answer' => $answerData['user_answer'],
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned
                ]);
                
                $responses[] = $response;
            }
            
            // Calculate percentage
            $percentage = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
            $passed = $percentage >= $lesson->pass_percentage; // Use lesson's pass percentage
            
            // Update attempt
            $attempt->update([
                'score' => $earnedPoints,
                'total_points' => $totalPoints,
                'percentage' => $percentage,
                'passed' => $passed,
                'completed_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'attempt' => $attempt,
                'responses' => $responses,
                'score' => $earnedPoints,
                'total_points' => $totalPoints,
                'percentage' => $percentage,
                'passed' => $passed
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit quiz answers'
            ], 500);
        }
    }

    /**
     * Get quiz results
     */
    public function getQuizResults($lessonId)
    {
        try {
            $user = Auth::user();
            $lesson = CourseLesson::with(['module.course'])->findOrFail($lessonId);
            
            // Check if user has access
            $course = $lesson->module->course;
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
            
            if (!$enrollment && !$lesson->is_preview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be enrolled in this course to view quiz results'
                ], 403);
            }
            
            // Get the latest completed attempt
            $attempt = \App\Models\QuizAttempt::where('user_id', $user->id)
                ->where('lesson_id', $lessonId)
                ->whereNotNull('completed_at')
                ->orderBy('completed_at', 'desc')
                ->first();
            
            if (!$attempt) {
                return response()->json([
                    'success' => false,
                    'message' => 'No quiz attempts found'
                ], 404);
            }
            
            // Get responses with questions
            $responses = \App\Models\QuizResponse::with('question.answers')
                ->where('attempt_id', $attempt->id)
                ->get();
            
            return response()->json([
                'success' => true,
                'attempt' => $attempt,
                'responses' => $responses
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load quiz results'
            ], 500);
        }
    }

    /**
     * Get quiz attempts for a lesson
     */
    public function getQuizAttempts($lessonId)
    {
        try {
            $user = Auth::user();
            $lesson = CourseLesson::with(['module.course'])->findOrFail($lessonId);
            
            // Check if user has access
            $course = $lesson->module->course;
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
            
            if (!$enrollment && !$lesson->is_preview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be enrolled in this course to view quiz attempts'
                ], 403);
            }
            
            $attempts = \App\Models\QuizAttempt::where('user_id', $user->id)
                ->where('lesson_id', $lessonId)
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'attempts' => $attempts
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load quiz attempts'
            ], 500);
        }
    }

    /**
     * Helper to check if a lesson is the first lesson in a course.
     */
    private function isFirstLesson($courseId, $lessonId)
    {
        $allLessons = CourseLesson::whereHas('module', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })
        ->with(['module'])
        ->get()
        ->sortBy(function ($lesson) {
            return $lesson->module->order . '.' . str_pad($lesson->order, 5, '0', STR_PAD_LEFT);
        })
        ->values();

        $isFirst = $allLessons->count() > 0 && $allLessons->first()->id === $lessonId;
        
        return $isFirst;
    }

    /**
     * Helper to check if a lesson is the first lesson of its module.
     */
    private function isFirstLessonOfModule($courseId, $lessonId)
    {
        $allLessons = CourseLesson::whereHas('module', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })
        ->with(['module'])
        ->get()
        ->sortBy(function ($lesson) {
            return $lesson->module->order . '.' . str_pad($lesson->order, 5, '0', STR_PAD_LEFT);
        })
        ->values();

        $currentLesson = null;
        foreach ($allLessons as $lesson) {
            if ($lesson->id === $lessonId) {
                $currentLesson = $lesson;
                break;
            }
        }

        if (!$currentLesson) {
            return false;
        }

        $moduleLessons = CourseLesson::where('module_id', $currentLesson->module_id)
            ->whereHas('module', function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->with(['module'])
            ->get()
            ->sortBy(function ($lesson) {
                return $lesson->module->order . '.' . str_pad($lesson->order, 5, '0', STR_PAD_LEFT);
            })
            ->values();

        return $moduleLessons->count() > 0 && $moduleLessons->first()->id === $lessonId;
    }
} 