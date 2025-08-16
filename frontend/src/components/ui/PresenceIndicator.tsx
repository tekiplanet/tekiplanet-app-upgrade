import React from 'react';
import { cn } from '@/lib/utils';
import { UserPresence } from '@/services/presenceService';

interface PresenceIndicatorProps {
  presence: UserPresence;
  size?: 'sm' | 'md' | 'lg';
  showStatus?: boolean;
  showLastSeen?: boolean;
  className?: string;
}

export const PresenceIndicator: React.FC<PresenceIndicatorProps> = ({
  presence,
  size = 'md',
  showStatus = false,
  showLastSeen = false,
  className,
}) => {
  const getStatusColor = (status: string) => {
    switch (status) {
      case 'online':
        return 'bg-green-500';
      case 'away':
        return 'bg-yellow-500';
      case 'offline':
        return 'bg-gray-400';
      default:
        return 'bg-gray-400';
    }
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'online':
        return 'Online';
      case 'away':
        return 'Away';
      case 'offline':
        return 'Offline';
      default:
        return 'Unknown';
    }
  };

  const getLastSeenText = (lastSeenAt: string | null) => {
    if (!lastSeenAt) return 'Never';
    
    const lastSeen = new Date(lastSeenAt);
    const now = new Date();
    const diffInMinutes = Math.floor((now.getTime() - lastSeen.getTime()) / (1000 * 60));
    const diffInHours = Math.floor(diffInMinutes / 60);
    const diffInDays = Math.floor(diffInHours / 24);
    
    // Just now (less than 1 minute)
    if (diffInMinutes < 1) return 'Just now';
    
    // Minutes ago (less than 1 hour)
    if (diffInMinutes < 60) return `${diffInMinutes} min ago`;
    
    // Hours ago (less than 24 hours)
    if (diffInHours < 24) return `${diffInHours}h ago`;
    
    // Yesterday
    if (diffInDays === 1) {
      return `Yesterday, ${lastSeen.toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit',
        hour12: true 
      })}`;
    }
    
    // Within same week (2-6 days ago)
    if (diffInDays < 7) {
      const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
      const dayName = dayNames[lastSeen.getDay()];
      return `${dayName}, ${lastSeen.toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit',
        hour12: true 
      })}`;
    }
    
    // Older than a week
    const day = lastSeen.getDate();
    const suffix = ['th', 'st', 'nd', 'rd'][day % 10 > 3 ? 0 : (day % 100 - day % 10 != 10 ? day % 10 : 0)] || 'th';
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const monthName = monthNames[lastSeen.getMonth()];
    
    return `${day}${suffix} ${monthName}, ${lastSeen.toLocaleTimeString('en-US', { 
      hour: 'numeric', 
      minute: '2-digit',
      hour12: true 
    })}`;
  };

  const sizeClasses = {
    sm: 'w-2 h-2',
    md: 'w-3 h-3',
    lg: 'w-4 h-4',
  };

  return (
    <div className={cn('flex items-center gap-2', className)}>
      {/* Status dot */}
      <div
        className={cn(
          'rounded-full border-2 border-white dark:border-gray-800',
          sizeClasses[size],
          getStatusColor(presence.online_status)
        )}
      />
      
      {/* Status text and last seen */}
      {(showStatus || showLastSeen) && (
        <div className="flex flex-col text-xs text-muted-foreground">
          {showStatus && (
            <span className="font-medium">
              {getStatusText(presence.online_status)}
            </span>
          )}
          {showLastSeen && presence.online_status === 'offline' && (
            <span className="text-[10px]">
              Last seen {getLastSeenText(presence.last_seen_at)}
            </span>
          )}
        </div>
      )}
    </div>
  );
};
