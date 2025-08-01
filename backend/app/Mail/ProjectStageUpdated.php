<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\ProjectStage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectStageUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $project;
    public $stage;
    public $action;

    public function __construct(Project $project, ProjectStage $stage, string $action)
    {
        $this->project = $project;
        $this->stage = $stage;
        $this->action = $action;
    }

    public function build()
    {
        return $this->view('emails.projects.stage-updated')
            ->subject("Project Stage {$this->action} - {$this->project->name}")
            ->with([
                'project' => $this->project,
                'stage' => $this->stage,
                'action' => $this->action
            ]);
    }
} 