<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseExam;
use Illuminate\Http\Request;

class CourseExamController extends Controller
{
    public function index(Course $course, Request $request)
    {
        $exams = $course->exams()
            ->when($request->search, function($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->type, function($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->sort_by, function($query) use ($request) {
                $query->orderBy($request->sort_by, $request->sort_order ?? 'asc');
            }, function($query) {
                $query->orderBy('date', 'desc');
            })
            ->paginate(10)
            ->withQueryString();

        return view('admin.courses.exams.index', compact('course', 'exams'));
    }

    public function create(Course $course)
    {
        return view('admin.courses.exams.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'total_questions' => 'required|integer|min:1',
                'pass_percentage' => 'required|integer|between:1,100',
                'duration_minutes' => 'required|integer|min:1',
                'type' => 'required|in:multiple_choice,true_false,mixed',
                'difficulty' => 'required|in:beginner,intermediate,advanced',
                'is_mandatory' => 'required|boolean',
                'date' => 'required|date|after:today',
                'duration' => 'required|string',
                'topics' => 'nullable|array'
            ]);

            $validated['status'] = 'upcoming';

            $exam = $course->exams()->create($validated);

            return redirect()
                ->route('admin.courses.exams.index', $course)
                ->with('notification', [
                    'message' => 'Exam created successfully',
                    'type' => 'success'
                ]);

        } catch (\Exception $e) {
            \Log::error('Error creating exam: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('notification', [
                    'message' => 'Failed to create exam. Please try again.',
                    'type' => 'error'
                ]);
        }
    }

    public function show(Course $course, CourseExam $exam)
    {
        // Load the relationships and get paginated user exams
        $exam->load(['userExams.user']);
        
        $userExams = $exam->userExams()
            ->with('user')
            ->paginate(10);

        // Calculate statistics
        $totalAttempted = $exam->userExams()->whereNotNull('score')->count();
        $totalPassed = $exam->userExams()
            ->whereNotNull('score')
            ->whereRaw('(score / total_score * 100) >= ?', [$exam->pass_percentage])
            ->count();
        $totalFailed = $exam->userExams()
            ->whereNotNull('score')
            ->whereRaw('(score / total_score * 100) < ?', [$exam->pass_percentage])
            ->count();
        $passRate = $totalAttempted > 0 ? round(($totalPassed / $totalAttempted) * 100) : 0;

        return view('admin.courses.exams.show', compact(
            'course', 
            'exam', 
            'userExams',
            'totalAttempted',
            'totalPassed',
            'totalFailed',
            'passRate'
        ));
    }

    public function edit(Course $course, CourseExam $exam)
    {
        return view('admin.courses.exams.edit', compact('course', 'exam'));
    }

    public function update(Request $request, Course $course, CourseExam $exam)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'total_questions' => 'required|integer|min:1',
                'pass_percentage' => 'required|integer|between:1,100',
                'duration_minutes' => 'required|integer|min:1',
                'type' => 'required|in:multiple_choice,true_false,mixed',
                'difficulty' => 'required|in:beginner,intermediate,advanced',
                'is_mandatory' => 'required|boolean',
                'date' => 'required|date',
                'duration' => 'required|string',
                'topics' => 'nullable|array'
            ]);

            $exam->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Exam updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating exam: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update exam'
            ], 500);
        }
    }

    public function destroy(Course $course, CourseExam $exam)
    {
        $exam->delete();

        return redirect()
            ->route('admin.courses.exams.index', $course)
            ->with('notification', [
                'message' => 'Exam deleted successfully',
                'type' => 'success'
            ]);
    }

    public function updateStatus(Request $request, Course $course, CourseExam $exam)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:upcoming,ongoing,completed'
            ]);

            // Don't allow changing status if exam is already completed
            if ($exam->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot change status of a completed exam'
                ], 422);
            }

            // Add logging to debug
            \Log::info('Updating exam status:', [
                'exam_id' => $exam->id,
                'old_status' => $exam->status,
                'new_status' => $validated['status']
            ]);

            $exam->update($validated);

            // Verify the update
            $exam->refresh();
            \Log::info('Exam status after update:', [
                'exam_id' => $exam->id,
                'current_status' => $exam->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Exam status updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating exam status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update exam status'
            ], 500);
        }
    }
} 