<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseLessonController extends Controller
{
    public function store(Request $request, Course $course, CourseModule $module)
    {
        \Log::debug('Lesson store attempt', [
            'course_id' => $course->id,
            'module_id' => $module->id,
            'request_data' => $request->all(),
            'url' => $request->fullUrl(),
            'method' => $request->method()
        ]);

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'content_type' => 'required|in:video,text,quiz,assignment',
                'duration_minutes' => 'required|integer|min:1',
                'resource_url' => 'nullable|url',
                'is_preview' => 'boolean'
            ]);

            \Log::debug('Validation passed', ['validated_data' => $validated]);

            // Get the highest order number and add 1
            $maxOrder = $module->lessons()->max('order') ?? 0;

            $lesson = CourseLesson::create([
                'id' => Str::uuid(),
                'module_id' => $module->id,
                'order' => $maxOrder + 1,
                ...$validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lesson created successfully',
                'lesson' => $lesson
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create lesson',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(Course $course, CourseLesson $lesson)
    {
        return response()->json([
            'success' => true,
            'lesson' => $lesson
        ]);
    }

    public function update(Request $request, Course $course, CourseLesson $lesson)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'content_type' => 'required|in:video,text,quiz,assignment',
                'duration_minutes' => 'required|integer|min:1',
                'resource_url' => 'nullable|url',
                'is_preview' => 'boolean'
            ]);

            $lesson->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Lesson updated successfully',
                'lesson' => $lesson
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lesson',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Course $course, CourseLesson $lesson)
    {
        try {
            $lesson->delete();

            // Reorder remaining lessons
            $module = $lesson->module;
            $module->lessons()
                ->where('order', '>', $lesson->order)
                ->decrement('order');

            return response()->json([
                'success' => true,
                'message' => 'Lesson deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete lesson',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 