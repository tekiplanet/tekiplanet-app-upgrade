<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ProjectInvoice;
use App\Models\Project;

class ProjectInvoiceUpdated extends Mailable
{
    use Queueable, SerializesModels;

    protected $invoiceId;
    protected $projectId;
    public $invoice;
    public $project;
    protected $action;

    public function __construct(ProjectInvoice $invoice, string $action = 'created')
    {
        $this->invoiceId = $invoice->id;
        $this->projectId = $invoice->project_id;
        $this->action = $action;
        $this->afterCommit();
    }

    public function build()
    {
        // Fetch fresh data when processing the queue
        $this->invoice = ProjectInvoice::findOrFail($this->invoiceId);
        $this->project = Project::findOrFail($this->projectId);

        $view = $this->action === 'created' 
            ? 'emails.project-invoice-created'
            : 'emails.project-invoice-updated';

        $subject = $this->action === 'created'
            ? 'New Invoice Created: ' . $this->invoice->invoice_number
            : 'Invoice Updated: ' . $this->invoice->invoice_number;

        return $this->view($view)
            ->subject($subject)
            ->with([
                'greeting' => 'Hello,',
                'closing' => 'Best regards,<br>TekiPlanet Team',
                'invoice' => $this->invoice,
                'project' => $this->project
            ]);
    }
} 