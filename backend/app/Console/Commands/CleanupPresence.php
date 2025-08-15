<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UserPresenceService;

class CleanupPresence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'presence:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old presence data and mark inactive users as offline';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting presence cleanup...');
        
        try {
            $presenceService = app(UserPresenceService::class);
            $presenceService->cleanupOldPresence();
            
            $this->info('Presence cleanup completed successfully!');
        } catch (\Exception $e) {
            $this->error('Presence cleanup failed: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
