<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;

class UserPresenceService
{
    protected $pusher;

    public function __construct()
    {
        try {
            $this->pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                config('broadcasting.connections.pusher.options')
            );
        } catch (\Exception $e) {
            Log::error('Failed to initialize Pusher in UserPresenceService: ' . $e->getMessage());
            $this->pusher = null;
        }
    }

    /**
     * Update user's online status and activity
     */
    public function updateUserActivity(User $user, string $status = 'online'): void
    {
        try {
            // Clear any existing cache first
            Cache::forget("user_presence_{$user->id}");
            
            // Update user model
            $user->update([
                'online_status' => $status,
                'last_activity_at' => now(),
                'last_seen_at' => $status === 'offline' ? now() : $user->last_seen_at,
            ]);

            // Cache user presence for quick access
            $this->cacheUserPresence($user);

            // Broadcast presence update
            $this->broadcastPresenceUpdate($user);

            Log::info("User {$user->id} presence updated to {$status}");
        } catch (\Exception $e) {
            Log::error("Failed to update user presence: " . $e->getMessage());
        }
    }

    /**
     * Mark user as online
     */
    public function markUserOnline(User $user): void
    {
        $this->updateUserActivity($user, 'online');
    }

    /**
     * Mark user as away
     */
    public function markUserAway(User $user): void
    {
        $this->updateUserActivity($user, 'away');
    }

    /**
     * Mark user as offline
     */
    public function markUserOffline(User $user): void
    {
        $this->updateUserActivity($user, 'offline');
        
        // Clear any cached presence data to ensure fresh data
        Cache::forget("user_presence_{$user->id}");
    }

    /**
     * Get user's current presence status
     */
    public function getUserPresence(User $user): array
    {
        // Always calculate current online status based on activity
        $isCurrentlyOnline = $this->isUserOnline($user);
        
        // If user is marked as online but hasn't been active recently, update their status
        if ($user->online_status === 'online' && !$isCurrentlyOnline) {
            $user->update([
                'online_status' => 'offline',
                'last_seen_at' => now(),
            ]);
        }
        
        $presence = [
            'user_id' => $user->id,
            'online_status' => $user->online_status,
            'last_seen_at' => $user->last_seen_at,
            'last_activity_at' => $user->last_activity_at,
            'is_online' => $isCurrentlyOnline,
        ];

        // Cache the updated presence data
        $this->cacheUserPresence($user, $presence);
        return $presence;
    }

    /**
     * Check if user is currently online
     */
    public function isUserOnline(User $user): bool
    {
        // Consider user online if they've been active in the last 5 minutes (matches cleanup timing)
        $lastActivity = $user->last_activity_at;
        if (!$lastActivity) {
            return false;
        }

        return $lastActivity->diffInMinutes(now()) < 5;
    }

    /**
     * Get last seen time in human readable format
     */
    public function getLastSeenText(User $user): string
    {
        if (!$user->last_seen_at) {
            return 'Never';
        }

        $lastSeen = $user->last_seen_at;
        $now = now();
        $diff = $lastSeen->diff($now);

        // Just now (less than 1 minute)
        if ($diff->i < 1) {
            return 'Just now';
        }

        // Minutes ago (less than 1 hour)
        if ($diff->h < 1) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        }

        // Hours ago (less than 24 hours)
        if ($diff->days < 1) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        }

        // Yesterday
        if ($diff->days === 1) {
            return 'Yesterday, ' . $lastSeen->format('g:i A');
        }

        // Within same week (2-6 days ago)
        if ($diff->days < 7) {
            return $lastSeen->format('l, g:i A'); // Monday, 5:45 PM
        }

        // Older than a week
        return $lastSeen->format('jS F, g:i A'); // 7th August, 5:13 PM
    }

    /**
     * Cache user presence data
     */
    protected function cacheUserPresence(User $user, ?array $presence = null): void
    {
        if (!$presence) {
            $presence = $this->getUserPresence($user);
        }

        Cache::put("user_presence_{$user->id}", $presence, now()->addMinutes(5));
    }

    /**
     * Broadcast presence update to Pusher
     */
    protected function broadcastPresenceUpdate(User $user): void
    {
        if (!$this->pusher) {
            Log::warning('Pusher not available, skipping presence broadcast');
            return;
        }
        
        try {
            $presence = $this->getUserPresence($user);
            
            Log::info("Broadcasting presence update for user {$user->id}", [
                'user_id' => $user->id,
                'presence' => $presence,
                'channel' => 'presence',
                'event' => 'user-presence-updated'
            ]);
            
            $this->pusher->trigger('presence', 'user-presence-updated', [
                'user_id' => $user->id,
                'presence' => $presence,
                'timestamp' => now()->toISOString(),
            ]);
            
            Log::info("Successfully broadcasted presence update for user {$user->id}");
        } catch (\Exception $e) {
            Log::error('Failed to broadcast presence update: ' . $e->getMessage());
        }
    }

    /**
     * Clean up old presence data
     */
    public function cleanupOldPresence(): void
    {
        // Get users who should be marked offline
        $inactiveUsers = User::where('last_activity_at', '<', now()->subMinutes(5))
            ->where('online_status', '!=', 'offline')
            ->get();

        foreach ($inactiveUsers as $user) {
            // Mark user as offline and broadcast the update
            $this->markUserOffline($user);
            Log::info("Marked user {$user->id} as offline due to inactivity");
        }
    }
}
