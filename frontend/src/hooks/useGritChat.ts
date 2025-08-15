import { useEffect, useState } from 'react';
import { useQueryClient } from '@tanstack/react-query';
import { pusher } from '@/lib/pusher';
import { toast } from 'sonner';
import { useAuthStore } from '@/store/useAuthStore';

export const useGritChat = (gritId: string) => {
  const queryClient = useQueryClient();
  const { user: currentUser } = useAuthStore();
  const [typingUsers, setTypingUsers] = useState<{[key: string]: any}>({});

  useEffect(() => {
    const channel = pusher.subscribe(`grit.${gritId}`);

    channel.bind('new-message', (data: any) => {
      console.log('Received new message:', data);
      
      // Update messages in cache
      queryClient.invalidateQueries({ queryKey: ['grit-messages', gritId] });

      // Show notification for non-system messages from other users only
      if (data.message.sender_type !== 'system') {
        // Check if the message is from the current user
        const isFromCurrentUser = String(data.message.user?.id) === String(currentUser?.id);
        
        // Only show notification if the message is NOT from the current user
        if (!isFromCurrentUser) {
          const senderLabel = data.message.sender_type === 'owner' ? 'Business Owner' : 
                             data.message.sender_type === 'professional' ? 'Professional' : 
                             data.message.sender_type === 'admin' ? 'Admin' : 'User';
          
          toast(
            `New message from ${senderLabel}`,
            {
              description: data.message.message,
              action: {
                label: 'View',
                onClick: () => {
                  // Navigate to chat if needed
                }
              }
            }
          );
        }
      }

      // Clear typing indicator when a message is received
      if (data.message.user?.id && data.message.user.id !== currentUser?.id) {
        setTypingUsers(prev => {
          const newState = { ...prev };
          delete newState[data.message.user.id];
          return newState;
        });
      }
    });

    // Listen for typing events
    channel.bind('typing-event', (data: any) => {
      console.log('Received typing event:', data);
      
      if (data.user_id === currentUser?.id) {
        // Don't show typing indicator for current user
        return;
      }

      if (data.is_typing) {
        // Add user to typing list
        setTypingUsers(prev => ({
          ...prev,
          [data.user_id]: {
            user: data.user,
            sender_type: data.sender_type,
            timestamp: Date.now()
          }
        }));
      } else {
        // Remove user from typing list
        setTypingUsers(prev => {
          const newState = { ...prev };
          delete newState[data.user_id];
          return newState;
        });
      }
    });

    // Listen for system events
    channel.bind('system-event', (data: any) => {
      // Update messages in cache to show new system message
      queryClient.invalidateQueries({ queryKey: ['grit-messages', gritId] });
      
      // Show toast for important system events
      if (data.event === 'application_approved' || data.event === 'payment_released' || data.event === 'project_completed') {
        toast(
          'System Update',
          {
            description: data.message,
            action: {
              label: 'View',
              onClick: () => {
                // Navigate to chat if needed
              }
            }
          }
        );
      }
    });

    return () => {
      channel.unbind_all();
      channel.unsubscribe();
    };
  }, [gritId, queryClient, currentUser?.id]);

  // Clean up typing indicators after 5 seconds of inactivity
  useEffect(() => {
    const interval = setInterval(() => {
      const now = Date.now();
      setTypingUsers(prev => {
        const newState = { ...prev };
        Object.keys(newState).forEach(userId => {
          if (now - newState[userId].timestamp > 8000) {
            delete newState[userId];
          }
        });
        return newState;
      });
    }, 2000);

    return () => clearInterval(interval);
  }, []);

  return { typingUsers };
}; 