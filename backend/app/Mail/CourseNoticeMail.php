<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\CourseNotice;

class CourseNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $courseNotice;

    public function __construct(CourseNotice $courseNotice)
    {
        $this->courseNotice = $courseNotice;
    }

    public function build()
    {
        return $this->view('emails.course-notice')
                    ->subject($this->courseNotice->is_important ? 
                        '❗ Important: ' . $this->courseNotice->title : 
                        'Course Notice: ' . $this->courseNotice->title);
    }
} 