<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnrollmentUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $enrollment;
    public $course;
    public $fieldUpdated;
    public $oldValue;
    public $newValue;

    public function __construct($enrollment, $course, $fieldUpdated, $oldValue, $newValue)
    {
        $this->enrollment = $enrollment;
        $this->course = $course;
        $this->fieldUpdated = $fieldUpdated;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    public function build()
    {
        $subject = "Course Enrollment Update - {$this->course->title}";
        
        return $this->subject($subject)
            ->view('emails.enrollment-updated')
            ->with([
                'fieldLabel' => str_replace('_', ' ', ucfirst($this->fieldUpdated)),
                'oldValue' => $this->oldValue,
                'newValue' => $this->newValue
            ]);
    }
} 