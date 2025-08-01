<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ProjectTeamMember;

class ProjectTeamMemberAdded extends Mailable
{
    use Queueable, SerializesModels;

    protected $teamMemberId;
    public $teamMember;
    protected $isForBusinessOwner;

    public function __construct(ProjectTeamMember $teamMember, bool $isForBusinessOwner = false)
    {
        $this->teamMemberId = $teamMember->id;
        $this->isForBusinessOwner = $isForBusinessOwner;
        $this->afterCommit();
    }

    public function build()
    {
        // Fetch fresh data when processing the queue
        $this->teamMember = ProjectTeamMember::with(['project', 'professional.user'])
            ->findOrFail($this->teamMemberId);

        $view = $this->isForBusinessOwner 
            ? 'emails.project-team-member-added-owner'
            : 'emails.project-team-member-added';

        $greeting = $this->isForBusinessOwner
            ? 'Hello ' . $this->teamMember->project->businessProfile->user->first_name . ','
            : 'Hello ' . $this->teamMember->professional->user->first_name . ',';

        return $this->view($view)
            ->subject('Team Member Added to Project: ' . $this->teamMember->project->name)
            ->with([
                'greeting' => $greeting,
                'closing' => 'Best regards,<br>TekiPlanet Team',
                'teamMember' => $this->teamMember
            ]);
    }
} 