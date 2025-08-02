<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestEmail extends Command
{
    protected $signature = 'email:test {email}';
    protected $description = 'Test email sending functionality';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Testing email sending to: {$email}");
        
        try {
            // Test SMTP connection
            $this->info("Testing SMTP connection...");
            
            Mail::raw('This is a test email from TekiPlanet', function($message) use ($email) {
                $message->to($email)
                        ->subject('TekiPlanet Email Test')
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            $this->info("✅ Email sent successfully!");
            Log::info('Test email sent successfully', ['email' => $email]);
            
        } catch (\Exception $e) {
            $this->error("❌ Email sending failed: " . $e->getMessage());
            Log::error('Test email failed', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            
            // Show more detailed error information
            $this->error("Error details:");
            $this->error("- Message: " . $e->getMessage());
            $this->error("- File: " . $e->getFile());
            $this->error("- Line: " . $e->getLine());
        }
    }
} 