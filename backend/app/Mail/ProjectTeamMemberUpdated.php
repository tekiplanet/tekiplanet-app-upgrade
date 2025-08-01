<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ProjectTeamMember;
use Illuminate\Support\Facades\Log;

class ProjectTeamMemberUpdated extends Mailable
{
    use Queueable, SerializesModels;

    private $data;

    public function __construct(ProjectTeamMember $teamMember, string $oldStatus, bool $isForBusinessOwner = false)
    {
        try {
            // Store only primitive data that can be easily serialized
            $this->data = [
                'projectName' => $teamMember->project->name,
                'memberName' => $teamMember->professional->user->first_name . ' ' . $teamMember->professional->user->last_name,
                'memberRole' => $teamMember->role,
                'oldStatus' => $oldStatus,
                'newStatus' => $teamMember->status,
                'recipientName' => $isForBusinessOwner 
                    ? $teamMember->project->businessProfile->user->first_name
                    : $teamMember->professional->user->first_name,
                'isForBusinessOwner' => $isForBusinessOwner
            ];

            Log::info('ProjectTeamMemberUpdated Constructor Data:', $this->data);
            
            $this->afterCommit();
        } catch (\Exception $e) {
            Log::error('ProjectTeamMemberUpdated Constructor Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function build()
    {
        try {
            Log::info('ProjectTeamMemberUpdated Build Method Data:', $this->data);

            $view = $this->data['isForBusinessOwner']
                ? 'emails.project-team-member-updated-owner'
                : 'emails.project-team-member-updated';

            $subject = $this->data['oldStatus'] === 'removed'
                ? ($this->data['isForBusinessOwner'] ? 'Team Member Removed from Project: ' : 'Removed from Project Team: ') 
                . $this->data['projectName']
                : 'Project Team Status Updated: ' . $this->data['projectName'];

            return $this->view($view)
                ->subject($subject)
                ->with([
                    'greeting' => 'Hello ' . $this->data['recipientName'] . ',',
                    'closing' => 'Best regards,<br>TekiPlanet Team',
                    'projectName' => $this->data['projectName'],
                    'memberName' => $this->data['memberName'],
                    'memberRole' => $this->data['memberRole'],
                    'oldStatus' => $this->data['oldStatus'],
                    'newStatus' => $this->data['newStatus']
                ]);
        } catch (\Exception $e) {
            Log::error('ProjectTeamMemberUpdated Build Method Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $this->data
            ]);
            throw $e;
        }
    }
} 