<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectFileUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $project;
    public $file;
    public $action;

    public function __construct(Project $project, ProjectFile $file, string $action)
    {
        $this->project = $project;
        $this->file = $file;
        $this->action = $action;
    }

    public function build()
    {
        return $this->view('emails.projects.file-updated')
            ->subject("Project File {$this->action} - {$this->project->name}")
            ->with([
                'project' => $this->project,
                'file' => $this->file,
                'action' => $this->action
            ]);
    }
} 