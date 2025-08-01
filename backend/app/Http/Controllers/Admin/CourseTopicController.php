<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseTopicController extends Controller
{
    public function store(Request $request, Course $course, CourseModule $module)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'order' => 'required|integer|min:1',
                'learning_outcomes' => 'required|string'
            ]);

            // Convert learning outcomes from textarea to array
            $learningOutcomes = array_filter(
                array_map('trim', explode("\n", $validated['learning_outcomes']))
            );

            $topic = CourseTopic::create([
                'id' => Str::uuid(),
                'module_id' => $module->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'order' => $validated['order'],
                'learning_outcomes' => json_encode($learningOutcomes),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Topic created successfully',
                'topic' => $topic
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create topic',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(Course $course, CourseTopic $topic)
    {
        return response()->json([
            'success' => true,
            'topic' => $topic
        ]);
    }

    public function update(Request $request, Course $course, CourseTopic $topic)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'learning_outcomes' => 'required|string'
            ]);

            // Convert learning outcomes from textarea to array
            $learningOutcomes = array_filter(
                array_map('trim', explode("\n", $validated['learning_outcomes']))
            );

            $topic->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'learning_outcomes' => json_encode($learningOutcomes)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Topic updated successfully',
                'topic' => $topic
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update topic',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Course $course, CourseTopic $topic)
    {
        try {
            $module = $topic->module;
            $topic->delete();

            // Reorder remaining topics
            $module->topics()
                ->where('order', '>', $topic->order)
                ->decrement('order');

            return response()->json([
                'success' => true,
                'message' => 'Topic deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete topic',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 