<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\EnrollmentUpdated;
use Illuminate\Support\Facades\Mail;

class SendEnrollmentEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $enrollment;
    protected $course;
    protected $fieldUpdated;
    protected $oldValue;
    protected $newValue;

    public function __construct($enrollment, $course, $fieldUpdated, $oldValue, $newValue)
    {
        $this->enrollment = $enrollment;
        $this->course = $course;
        $this->fieldUpdated = $fieldUpdated;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    public function handle()
    {
        Mail::to($this->enrollment->user)->send(new EnrollmentUpdated(
            $this->enrollment,
            $this->course,
            $this->fieldUpdated,
            $this->oldValue,
            $this->newValue
        ));
    }
} 