<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\BusinessProfile;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Mail\ProjectCreated;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Mail\ProjectStatusUpdated;

class ProjectController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $projects = Project::with(['businessProfile', 'stages', 'teamMembers', 'files'])
            ->latest()
            ->paginate(10);

        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        $businesses = BusinessProfile::with('user')->get();
        return view('admin.projects.create', compact('businesses'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'business_profile_id' => 'required|exists:business_profiles,id',
                'client_name' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'status' => 'required|in:pending,in_progress,completed,on_hold,cancelled',
                'progress' => 'required|integer|min:0|max:100',
                'budget' => 'required|numeric|min:0'
            ]);

            $validated['business_id'] = $validated['business_profile_id'];
            unset($validated['business_profile_id']);

            $project = Project::create($validated);

            // Send notification
            $this->notificationService->send([
                'type' => 'project_created',
                'title' => 'New Project Created',
                'message' => "Project '{$project->name}' has been created",
                'icon' => 'folder-plus',
                'action_url' => "/projects/{$project->id}",
                'extra_data' => [
                    'project_id' => $project->id
                ]
            ], $project->businessProfile->user);

            // Queue email
            Mail::to($project->businessProfile->user->email)
                ->queue(new ProjectCreated($project));

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully',
                'redirect' => route('admin.projects.show', $project)
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create project: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Project $project)
    {
        $project->load([
            'businessProfile.user', 
            'stages', 
            'teamMembers.professional.user',
            'files.uploadedBy', 
            'invoices'
        ]);

        return view('admin.projects.show', compact('project'));
    }

    public function updateStatus(Request $request, Project $project)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,in_progress,completed,on_hold,cancelled',
                'notes' => 'nullable|string'
            ]);

            $oldStatus = $project->status;
            $project->update($validated);

            // Send notification
            $this->notificationService->send([
                'type' => 'project_status_updated',
                'title' => 'Project Status Updated',
                'message' => "Project '{$project->name}' status changed from {$oldStatus} to {$validated['status']}",
                'icon' => 'refresh',
                'action_url' => "/projects/{$project->id}",
                'extra_data' => [
                    'project_id' => $project->id,
                    'old_status' => $oldStatus,
                    'new_status' => $validated['status']
                ]
            ], $project->businessProfile->user);

            // Queue email
            Mail::to($project->businessProfile->user->email)
                ->queue(new ProjectStatusUpdated($project, [
                    'old_status' => ucwords(str_replace('_', ' ', $oldStatus)),
                    'new_status' => ucwords(str_replace('_', ' ', $validated['status'])),
                    'notes' => $validated['notes'] ?? null
                ]));

            return response()->json([
                'success' => true,
                'message' => 'Project status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Project $project)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'client_name' => 'sometimes|required|string|max:255',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date|after:start_date',
                'budget' => 'sometimes|required|numeric|min:0',
                'status' => 'sometimes|required|in:pending,in_progress,completed,cancelled'
            ]);

            $changes = array_intersect_key($validated, $project->getDirty());
            $project->update($validated);

            // Send notification
            $this->notificationService->send([
                'type' => 'project_updated',
                'title' => 'Project Details Updated',
                'message' => "Project '{$project->name}' details have been updated",
                'icon' => 'pencil',
                'action_url' => "/projects/{$project->id}",
                'extra_data' => [
                    'project_id' => $project->id,
                    'changes' => $changes
                ]
            ], $project->businessProfile->user);

            // Send email
            Mail::to($project->businessProfile->user->email)
                ->queue(new ProjectStatusUpdated($project, $changes));

            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully',
                'data' => $project
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProgress(Request $request, Project $project)
    {
        try {
            $validated = $request->validate([
                'progress' => 'required|integer|min:0|max:100'
            ]);

            $oldProgress = $project->progress;
            $project->update($validated);

            // Send notification
            $this->notificationService->send([
                'type' => 'project_progress_updated',
                'title' => 'Project Progress Updated',
                'message' => "Project '{$project->name}' progress has been updated from {$oldProgress}% to {$project->progress}%",
                'icon' => 'chart-bar',
                'action_url' => "/projects/{$project->id}",
                'extra_data' => [
                    'project_id' => $project->id,
                    'old_progress' => "{$oldProgress}%",
                    'new_progress' => "{$project->progress}%"
                ]
            ], $project->businessProfile->user);

            // Send email
            Mail::to($project->businessProfile->user->email)
                ->queue(new ProjectStatusUpdated($project, [
                    'progress' => $project->progress . '%',
                    'old_progress' => $oldProgress . '%',
                    'type' => 'progress'
                ]));

            return response()->json([
                'success' => true,
                'message' => 'Project progress updated successfully',
                'data' => $project
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project progress: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadFile(Request $request, Project $project)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:10240' // 10MB max
            ]);

            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            
            // Generate a unique name for storage
            $uniqueName = uniqid() . '-' . $fileName;
            
            // Store the file
            $path = $file->storeAs('project-files', $uniqueName, 'public');

            // Create file record
            $projectFile = $project->files()->create([
                'name' => $fileName,
                'file_path' => $path,
                'uploaded_by' => auth()->id(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => $projectFile
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file: ' . $e->getMessage()
            ], 500);
        }
    }
} 