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
    }

    /**
     * Get user's current presence status
     */
    public function getUserPresence(User $user): array
    {
        $cached = Cache::get("user_presence_{$user->id}");
        
        if ($cached) {
            return $cached;
        }

        $presence = [
            'user_id' => $user->id,
            'online_status' => $user->online_status,
            'last_seen_at' => $user->last_seen_at,
            'last_activity_at' => $user->last_activity_at,
            'is_online' => $this->isUserOnline($user),
        ];

        $this->cacheUserPresence($user, $presence);
        return $presence;
    }

    /**
     * Check if user is currently online
     */
    public function isUserOnline(User $user): bool
    {
        // Consider user online if they've been active in the last 5 minutes
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

        $diff = $user->last_seen_at->diff(now());

        if ($diff->days > 0) {
            return $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        } elseif ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        } else {
            return 'Just now';
        }
    }

    /**
     * Cache user presence data
     */
    protected function cacheUserPresence(User $user, ?array $presence = null): void
    {
        if (!$presence) {
            $presence = $this->getUserPresence($user);
        }

        Cache::put("user_presence_{$user->id}", $presence, now()->addMinutes(10));
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
        // Mark users as offline if they haven't been active for more than 10 minutes
        User::where('last_activity_at', '<', now()->subMinutes(10))
            ->where('online_status', '!=', 'offline')
            ->update([
                'online_status' => 'offline',
                'last_seen_at' => now(),
            ]);
    }
}
