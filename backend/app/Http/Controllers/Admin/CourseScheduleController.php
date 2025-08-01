<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSchedule;
use Illuminate\Http\Request;

class CourseScheduleController extends Controller
{
    public function store(Course $course, Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'days_of_week' => 'required|string',
            'location' => 'nullable|string|max:255',
        ]);

        try {
            $schedule = $course->schedules()->create([
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'days_of_week' => $request->days_of_week,
                'location' => $request->location,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Schedule created successfully',
                'schedule' => $schedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Course $course, CourseSchedule $schedule)
    {
        return response()->json([
            'success' => true,
            'schedule' => $schedule
        ]);
    }

    public function update(Course $course, CourseSchedule $schedule, Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'days_of_week' => 'required|string',
            'location' => 'nullable|string|max:255',
        ]);

        try {
            $schedule->update([
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'days_of_week' => $request->days_of_week,
                'location' => $request->location,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully',
                'schedule' => $schedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Course $course, CourseSchedule $schedule)
    {
        try {
            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete schedule: ' . $e->getMessage()
            ], 500);
        }
    }
} 