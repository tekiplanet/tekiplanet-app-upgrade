import React from 'react';
import { cn } from '@/lib/utils';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { User } from '@/types/user';

interface ReplyMessageProps {
  replyTo: {
    id: string;
    message: string;
    user: User;
    created_at: string;
  };
  className?: string;
}

export const ReplyMessage: React.FC<ReplyMessageProps> = ({ replyTo, className }) => {
  return (
    <div className={cn('mb-2 p-2 bg-muted/50 rounded-lg border-l-2 border-primary/30', className)}>
      <div className="flex items-start gap-2">
        <Avatar className="h-4 w-4 shrink-0">
          <AvatarImage src={replyTo.user?.avatar} />
          <AvatarFallback className="text-xs">
            {replyTo.user ? (replyTo.user.first_name?.[0] || replyTo.user.last_name?.[0] || replyTo.user.username?.[0] || '?') : 'S'}
          </AvatarFallback>
        </Avatar>
        <div className="flex-1 min-w-0">
          <div className="flex items-center gap-2 mb-1">
            <span className="text-xs font-medium text-muted-foreground">
              {replyTo.user?.first_name} {replyTo.user?.last_name}
            </span>
            <span className="text-xs text-muted-foreground">•</span>
            <span className="text-xs text-muted-foreground">
              {new Date(replyTo.created_at).toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
              })}
            </span>
          </div>
          <p className="text-xs text-muted-foreground line-clamp-2">
            {replyTo.message}
          </p>
        </div>
      </div>
    </div>
  );
};
