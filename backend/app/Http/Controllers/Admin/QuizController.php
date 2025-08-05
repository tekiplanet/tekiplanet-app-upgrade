<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    /**
     * Get quiz questions for a lesson
     */
    public function getQuestions(Course $course, CourseLesson $lesson)
    {
        try {
            $questions = $lesson->questions()->with('answers')->orderBy('order')->get();
            
            return response()->json([
                'success' => true,
                'questions' => $questions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch quiz questions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new quiz question
     */
    public function storeQuestion(Request $request, Course $course, CourseLesson $lesson)
    {
        try {
            // Debug: Log the incoming request data
            \Log::info('Quiz Question Creation Request:', [
                'request_data' => $request->all(),
                'lesson_id' => $lesson->id,
                'course_id' => $course->id
            ]);

            $validated = $request->validate([
                'question' => 'required|string',
                'question_type' => 'required|in:multiple_choice,true_false,short_answer',
                'points' => 'required|integer|min:1',
                'answers' => 'required|array|min:1',
                'answers.*.answer_text' => 'required|string',
                'answers.*.is_correct' => 'required|boolean'
            ]);

            // Get the highest order number and add 1
            $maxOrder = $lesson->questions()->max('order') ?? 0;

            $question = QuizQuestion::create([
                'id' => Str::uuid(),
                'lesson_id' => $lesson->id,
                'question' => $validated['question'],
                'question_type' => $validated['question_type'],
                'points' => $validated['points'],
                'order' => $maxOrder + 1
            ]);

            // Create answers for the question
            foreach ($validated['answers'] as $index => $answerData) {
                QuizAnswer::create([
                    'id' => Str::uuid(),
                    'question_id' => $question->id,
                    'answer_text' => $answerData['answer_text'],
                    'is_correct' => $answerData['is_correct'],
                    'order' => $index + 1
                ]);
            }

            $question->load('answers');

            return response()->json([
                'success' => true,
                'message' => 'Question created successfully',
                'question' => $question
            ]);

        } catch (\Exception $e) {
            // Debug: Log the actual error
            \Log::error('Quiz Question Creation Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create question',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a quiz question for editing
     */
    public function edit(Course $course, QuizQuestion $question)
    {
        try {
            $question->load('answers');
            
            return response()->json([
                'success' => true,
                'question' => $question
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load question data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a quiz question
     */
    public function updateQuestion(Request $request, Course $course, QuizQuestion $question)
    {
        try {
            $validated = $request->validate([
                'question' => 'required|string',
                'question_type' => 'required|in:multiple_choice,true_false,short_answer',
                'points' => 'required|integer|min:1',
                'answers' => 'required|array|min:1',
                'answers.*.answer_text' => 'required|string',
                'answers.*.is_correct' => 'required|boolean'
            ]);

            $question->update([
                'question' => $validated['question'],
                'question_type' => $validated['question_type'],
                'points' => $validated['points']
            ]);

            // Delete existing answers and create new ones
            $question->answers()->delete();

            foreach ($validated['answers'] as $index => $answerData) {
                QuizAnswer::create([
                    'id' => Str::uuid(),
                    'question_id' => $question->id,
                    'answer_text' => $answerData['answer_text'],
                    'is_correct' => $answerData['is_correct'],
                    'order' => $index + 1
                ]);
            }

            $question->load('answers');

            return response()->json([
                'success' => true,
                'message' => 'Question updated successfully',
                'question' => $question
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update question',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a quiz question
     */
    public function destroyQuestion(Course $course, QuizQuestion $question)
    {
        try {
            $question->delete();

            return response()->json([
                'success' => true,
                'message' => 'Question deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete question',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder questions
     */
    public function reorderQuestions(Request $request, Course $course, CourseLesson $lesson)
    {
        try {
            $validated = $request->validate([
                'question_ids' => 'required|array',
                'question_ids.*' => 'required|string|exists:quiz_questions,id'
            ]);

            foreach ($validated['question_ids'] as $index => $questionId) {
                QuizQuestion::where('id', $questionId)
                    ->where('lesson_id', $lesson->id)
                    ->update(['order' => $index + 1]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Questions reordered successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder questions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 