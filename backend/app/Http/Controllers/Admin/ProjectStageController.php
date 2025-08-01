<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectStage;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Mail\ProjectStageUpdated;
use Illuminate\Support\Facades\Mail;

class ProjectStageController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function store(Request $request, Project $project)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:pending,in_progress,completed',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
            ]);

            // Get max order and increment
            $maxOrder = $project->stages()->max('order') ?? 0;
            $validated['order'] = $maxOrder + 1;

            $stage = $project->stages()->create($validated);

            // Send notification to business owner
            $this->notificationService->send([
                'type' => 'project_stage_created',
                'title' => 'New Project Stage Added',
                'message' => "A new stage '{$stage->name}' has been added to your project '{$project->name}'",
                'icon' => 'clipboard-list',
                'action_url' => "/projects/{$project->id}",
                'extra_data' => [
                    'project_id' => $project->id,
                    'stage_id' => $stage->id
                ]
            ], $project->businessProfile->user);

            // Queue email
            Mail::to($project->businessProfile->user->email)
                ->queue(new ProjectStageUpdated($project, $stage, 'created'));

            return response()->json([
                'success' => true,
                'message' => 'Stage created successfully',
                'stage' => $stage
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create stage: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Project $project, ProjectStage $stage)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:pending,in_progress,completed',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
            ]);

            $oldStatus = $stage->status;
            $stage->update($validated);

            // Send notification
            $this->notificationService->send([
                'type' => 'project_stage_updated',
                'title' => 'Project Stage Updated',
                'message' => "Stage '{$stage->name}' in project '{$project->name}' has been updated",
                'icon' => 'clipboard-check',
                'action_url' => "/projects/{$project->id}",
                'extra_data' => [
                    'project_id' => $project->id,
                    'stage_id' => $stage->id,
                    'old_status' => $oldStatus,
                    'new_status' => $validated['status']
                ]
            ], $project->businessProfile->user);

            // Queue email
            Mail::to($project->businessProfile->user->email)
                ->queue(new ProjectStageUpdated($project, $stage, 'updated'));

            return response()->json([
                'success' => true,
                'message' => 'Stage updated successfully',
                'stage' => $stage
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stage: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Project $project, ProjectStage $stage)
    {
        try {
            $stageName = $stage->name;
            $stage->delete();

            // Reorder remaining stages
            $project->stages()
                ->where('order', '>', $stage->order)
                ->decrement('order');

            // Send notification
            $this->notificationService->send([
                'type' => 'project_stage_deleted',
                'title' => 'Project Stage Deleted',
                'message' => "Stage '{$stageName}' has been removed from project '{$project->name}'",
                'icon' => 'trash',
                'action_url' => "/projects/{$project->id}",
                'extra_data' => [
                    'project_id' => $project->id
                ]
            ], $project->businessProfile->user);

            // Queue email
            Mail::to($project->businessProfile->user->email)
                ->queue(new ProjectStageUpdated($project, $stage, 'deleted'));

            return response()->json([
                'success' => true,
                'message' => 'Stage deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete stage: ' . $e->getMessage()
            ], 500);
        }
    }
} 