<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTeamMember;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Mail\ProjectTeamMemberAdded;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProjectTeamMemberUpdated;

class ProjectTeamMemberController extends Controller
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
                'professional_id' => 'required|exists:professionals,id',
                'role' => 'required|string',
            ]);

            $teamMember = $project->teamMembers()->create([
                'professional_id' => $validated['professional_id'],
                'role' => $validated['role'],
                'status' => 'active',
                'joined_at' => now(),
            ]);

            // Load relationships
            $teamMember->load(['professional.user', 'project']);

            // Send notification to business owner
            $this->notificationService->send([
                'type' => 'project_team_member_added',
                'title' => 'New Team Member Added',
                'message' => "{$teamMember->professional->user->first_name} {$teamMember->professional->user->last_name} has been added as {$teamMember->role} to project '{$project->name}'",
                'icon' => 'users',
                'action_url' => "/projects/{$project->id}",
                'extra_data' => [
                    'project_id' => $project->id,
                    'team_member_id' => $teamMember->id
                ]
            ], $project->businessProfile->user);

            // Send email to the new team member
            Mail::to($teamMember->professional->user->email)
                ->queue(new ProjectTeamMemberAdded($teamMember));

            // Send email to business owner
            Mail::to($project->businessProfile->user->email)
                ->queue(new ProjectTeamMemberAdded($teamMember, true)); // true indicates it's for business owner

            return response()->json([
                'success' => true,
                'message' => 'Team member added successfully',
                'data' => $teamMember
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add team member: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Project $project, ProjectTeamMember $member)
    {
        try {
            $validated = $request->validate([
                'role' => 'required|string',
                'status' => 'required|in:active,inactive',
                'joined_at' => 'required|date',
                'left_at' => 'nullable|date|after:joined_at'
            ]);

            $oldStatus = $member->status;
            $member->update($validated);

            // Load relationships
            $member->load(['professional.user', 'project']);

            // Send notification to business owner
            $this->notificationService->send([
                'type' => 'project_team_member_updated',
                'title' => 'Team Member Status Updated',
                'message' => "{$member->professional->user->first_name}'s status has been updated from {$oldStatus} to {$validated['status']}",
                'icon' => 'users',
                'action_url' => "/projects/{$project->id}",
                'extra_data' => [
                    'project_id' => $project->id,
                    'team_member_id' => $member->id
                ]
            ], $project->businessProfile->user);

            // Send email to the team member
            Mail::to($member->professional->user->email)
                ->queue(new ProjectTeamMemberUpdated($member, $oldStatus));

            return response()->json([
                'success' => true,
                'message' => 'Team member updated successfully',
                'data' => $member
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update team member: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Project $project, ProjectTeamMember $member)
    {
        try {
            // Load relationships before anything else
            $member->load(['professional.user', 'project']);

            // Store email data before deletion
            $userEmail = $member->professional->user->email;
            $businessEmail = $project->businessProfile->user->email;

            // Send notification to business owner
            $this->notificationService->send([
                'type' => 'project_team_member_removed',
                'title' => 'Team Member Removed',
                'message' => "{$member->professional->user->first_name} has been removed from project '{$project->name}'",
                'icon' => 'users',
                'action_url' => "/projects/{$project->id}",
                'extra_data' => [
                    'project_id' => $project->id
                ]
            ], $project->businessProfile->user);

            // Send email to the team member before deletion
            Mail::to($userEmail)
                ->queue(new ProjectTeamMemberUpdated($member, 'removed'));

            // Send email to business owner
            Mail::to($businessEmail)
                ->queue(new ProjectTeamMemberUpdated($member, 'removed', true));

            // Delete after sending notifications and emails
            $member->delete();

            return response()->json([
                'success' => true,
                'message' => 'Team member removed successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Team Member Removal Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove team member: ' . $e->getMessage()
            ], 500);
        }
    }
} 