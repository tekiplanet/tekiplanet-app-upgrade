<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\UserCourseExam;

class ExamStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $userExam;
    public $action;
    public $passed;
    public $scorePercentage;

    public function __construct(UserCourseExam $userExam, string $action)
    {
        $this->userExam = $userExam->load(['user', 'courseExam']);
        $this->action = $action;
        
        if ($action === 'score') {
            $this->scorePercentage = ($userExam->score / $userExam->total_score) * 100;
            $this->passed = $this->scorePercentage >= $userExam->courseExam->pass_percentage;
        }
    }

    public function build()
    {
        $subject = $this->action === 'score' 
            ? 'Exam Results Available'
            : 'Exam Status Update';

        return $this->subject($subject)
                    ->view('emails.exam-status-updated');
    }

    public function __sleep()
    {
        return ['userExam', 'action', 'passed', 'scorePercentage'];
    }
} 