<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseModuleController extends Controller
{
    public function store(Request $request, Course $course)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'duration_hours' => 'required|integer|min:1',
                'order' => 'required|integer|min:1'
            ]);

            $module = CourseModule::create([
                'id' => Str::uuid(),
                'course_id' => $course->id,
                ...$validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Module created successfully',
                'module' => $module
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create module',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(Course $course, CourseModule $module)
    {
        return response()->json([
            'success' => true,
            'module' => $module
        ]);
    }

    public function update(Request $request, Course $course, CourseModule $module)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'duration_hours' => 'required|integer|min:1',
                'order' => 'required|integer|min:1'
            ]);

            $module->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Module updated successfully',
                'module' => $module
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update module',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Course $course, CourseModule $module)
    {
        try {
            $module->delete();

            return response()->json([
                'success' => true,
                'message' => 'Module deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete module',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 