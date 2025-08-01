<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\UserCourseExam;
use App\Mail\ExamStatusUpdated;
use Illuminate\Support\Facades\Mail;
use App\Services\NotificationService;

class SendExamNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userExam;
    protected $action;

    public function __construct(UserCourseExam $userExam, string $action)
    {
        $this->userExam = $userExam;
        $this->action = $action;
    }

    public function handle(NotificationService $notificationService)
    {
        try {
            \Log::info('SendExamNotification Job Starting:', [
                'action' => $this->action,
                'user_exam_id' => $this->userExam->id
            ]);

            if (!$this->userExam->relationLoaded('courseExam')) {
                $this->userExam->load(['courseExam', 'user']);
            }

            $this->userExam->action = $this->action;

            \Log::info('Before sending email:', [
                'user_exam_action' => $this->userExam->action,
                'original_action' => $this->action
            ]);

            // Send email
            Mail::to($this->userExam->user->email)
                ->queue(new ExamStatusUpdated($this->userExam, $this->action));

            // Create notification data based on action type
            $notificationData = $this->getNotificationData();

            // Add more debugging
            \Log::info('Sending notification:', [
                'type' => $notificationData['type'],
                'title' => $notificationData['title'],
                'message' => $notificationData['message']
            ]);

            // Send notification
            $notificationService->send($notificationData, $this->userExam->user);

        } catch (\Exception $e) {
            \Log::error('Error in SendExamNotification job: ' . $e->getMessage(), [
                'user_exam_id' => $this->userExam->id,
                'exam_id' => $this->userExam->course_exam_id,
                'user_id' => $this->userExam->user_id,
                'action' => $this->action
            ]);
            throw $e;
        }
    }

    protected function getNotificationData(): array
    {
        // Add debug logging
        \Log::info('Getting notification data:', [
            'action' => $this->action,
            'score' => $this->userExam->score,
            'total_score' => $this->userExam->total_score
        ]);

        if ($this->action === 'score') {
            $scorePercentage = ($this->userExam->score / $this->userExam->total_score) * 100;
            $passed = $scorePercentage >= $this->userExam->courseExam->pass_percentage;
            
            return [
                'type' => 'exam_result',
                'title' => 'Exam Results Available',
                'message' => "Results for {$this->userExam->courseExam->title} are out. You " . 
                            ($passed ? "passed" : "failed") . " with " . 
                            round($scorePercentage) . "% (Required: {$this->userExam->courseExam->pass_percentage}%)",
                'icon' => $passed ? 'check-circle' : 'x-circle',
                'action_url' => null,
                'extra_data' => [
                    'exam_id' => $this->userExam->course_exam_id,
                    'status' => $this->userExam->status,
                    'score' => $this->userExam->score,
                    'total_score' => $this->userExam->total_score,
                    'passed' => $passed,
                    'score_percentage' => round($scorePercentage, 2),
                    'required_percentage' => $this->userExam->courseExam->pass_percentage
                ]
            ];
        }

        return [
            'type' => 'exam_status',
            'title' => 'Exam Status Update',
            'message' => "Your exam status for {$this->userExam->courseExam->title} has been updated to " . 
                        str_replace('_', ' ', ucfirst($this->userExam->status)),
            'icon' => 'clipboard-check',
            'action_url' => null,
            'extra_data' => [
                'exam_id' => $this->userExam->course_exam_id,
                'status' => $this->userExam->status
            ]
        ];
    }
} 