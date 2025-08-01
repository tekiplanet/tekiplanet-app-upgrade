<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Project;
use Illuminate\Support\Facades\Log;

class ProjectStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    private $data;

    public function __construct(Project $project, array $changes, bool $isProgressUpdate = false)
    {
        try {
            $this->data = [
                'projectName' => $project->name,
                'recipientName' => $project->businessProfile->user->first_name,
                'changes' => $changes,
                'isProgressUpdate' => $isProgressUpdate,
                'progress' => $project->progress,
                'status' => $project->status
            ];

            Log::info('ProjectStatusUpdated Constructor Data:', $this->data);
            
            $this->afterCommit();
        } catch (\Exception $e) {
            Log::error('ProjectStatusUpdated Constructor Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function build()
    {
        try {
            $subject = $this->data['isProgressUpdate'] 
                ? "Project Progress Updated: {$this->data['projectName']}"
                : "Project Details Updated: {$this->data['projectName']}";

            return $this->view('emails.project-status-updated')
                ->subject($subject)
                ->with([
                    'greeting' => 'Hello ' . $this->data['recipientName'] . ',',
                    'closing' => 'Best regards,<br>TekiPlanet Team',
                    'projectName' => $this->data['projectName'],
                    'changes' => $this->data['changes'],
                    'isProgressUpdate' => $this->data['isProgressUpdate'],
                    'progress' => $this->data['progress'],
                    'status' => $this->data['status']
                ]);
        } catch (\Exception $e) {
            Log::error('ProjectStatusUpdated Build Method Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
} 