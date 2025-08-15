import { useEffect } from 'react';
import { useQueryClient } from '@tanstack/react-query';
import { pusher } from '@/lib/pusher';
import { toast } from 'sonner';

export const useGritChat = (gritId: string) => {
  const queryClient = useQueryClient();

  useEffect(() => {
    const channel = pusher.subscribe(`grit.${gritId}`);

    channel.bind('new-message', (data: any) => {
      console.log('Received new message:', data);
      
      // Update messages in cache
      queryClient.invalidateQueries({ queryKey: ['grit-messages', gritId] });

      // Show notification for non-system messages from other users
      if (data.message.sender_type !== 'system') {
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
  }, [gritId, queryClient]);
}; 