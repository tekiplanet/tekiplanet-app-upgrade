import { useState, useEffect, useCallback } from 'react';
import { presenceService, type UserPresence } from '@/services/presenceService';
import { useAuthStore } from '@/store/useAuthStore';
import Pusher from 'pusher-js';

export const usePresence = () => {
  const { user: currentUser } = useAuthStore();
  const [myPresence, setMyPresence] = useState<UserPresence | null>(null);
  const [usersPresence, setUsersPresence] = useState<{ [userId: string]: UserPresence }>({});
  const [isLoading, setIsLoading] = useState(false);
  const [pusher, setPusher] = useState<Pusher | null>(null);

  // Initialize Pusher for real-time presence updates
  useEffect(() => {
    if (!currentUser) return;

    const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
    const PUSHER_APP_KEY = import.meta.env.VITE_PUSHER_APP_KEY || '2f14ebc513254579c12a';
    const PUSHER_APP_CLUSTER = import.meta.env.VITE_PUSHER_APP_CLUSTER || 'eu';
    
    const pusherInstance = new Pusher(PUSHER_APP_KEY, {
      cluster: PUSHER_APP_CLUSTER,
      forceTLS: false, // Allow both HTTP and HTTPS
      authEndpoint: `${API_URL}/broadcasting/auth`
    });

    // Monitor connection status
    pusherInstance.connection.bind('connected', () => {
      console.log('Pusher connected for presence updates');
      
      // Test the connection by triggering a test event
      setTimeout(() => {
        console.log('Testing presence channel subscription...');
        // This will help verify if the channel subscription is working
      }, 1000);
    });

    pusherInstance.connection.bind('error', (error: any) => {
      console.error('Pusher connection error for presence:', error);
      console.log('Falling back to periodic presence updates');
    });

    // Subscribe to presence channel for real-time updates
    const channel = pusherInstance.subscribe('presence');
    
    // Listen for presence updates
    channel.bind('user-presence-updated', (data: { user_id: string; presence: UserPresence }) => {
      console.log('Received real-time presence update:', data);
      setUsersPresence(prev => ({
        ...prev,
        [data.user_id]: data.presence
      }));
    });

    // Monitor subscription status
    channel.bind('pusher:subscription_succeeded', () => {
      console.log('Successfully subscribed to presence channel');
    });

    channel.bind('pusher:subscription_error', (error: any) => {
      console.error('Failed to subscribe to presence channel:', error);
      console.log('Falling back to periodic presence updates');
    });

    setPusher(pusherInstance);

    // Cleanup on unmount
    return () => {
      channel.unbind('user-presence-updated');
      channel.unbind('pusher:subscription_succeeded');
      channel.unbind('pusher:subscription_error');
      pusherInstance.unsubscribe('presence');
      pusherInstance.disconnect();
    };
  }, [currentUser]);

  // Get current user's presence
  const getMyPresence = useCallback(async () => {
    if (!currentUser) return;
    
    try {
      setIsLoading(true);
      const response = await presenceService.getMyPresence();
      if (response.success) {
        setMyPresence(response.presence);
      }
    } catch (error) {
      console.error('Failed to get my presence:', error);
    } finally {
      setIsLoading(false);
    }
  }, [currentUser]);

  // Update current user's presence
  const updatePresence = useCallback(async (status: 'online' | 'away' | 'offline') => {
    if (!currentUser) return;
    
    try {
      const response = await presenceService.updatePresence(status);
      if (response.success) {
        setMyPresence(response.presence);
      }
    } catch (error) {
      console.error('Failed to update presence:', error);
    }
  }, [currentUser]);

  // Get presence for specific users
  const getUsersPresence = useCallback(async (userIds: string[]) => {
    if (userIds.length === 0) return;
    
    try {
      const response = await presenceService.getMultipleUsersPresence(userIds);
      if (response.success) {
        setUsersPresence(prev => ({ ...prev, ...response.presence }));
      }
    } catch (error) {
      console.error('Failed to get users presence:', error);
    }
  }, []);

  // Get single user's presence
  const getUserPresence = useCallback(async (userId: string) => {
    try {
      const response = await presenceService.getUserPresence(userId);
      if (response.success) {
        setUsersPresence(prev => ({ ...prev, [userId]: response.presence }));
      }
    } catch (error) {
      console.error('Failed to get user presence:', error);
    }
  }, []);

  // Send heartbeat to keep user online
  const sendHeartbeat = useCallback(async () => {
    if (!currentUser) return;
    
    try {
      await presenceService.sendHeartbeat();
    } catch (error) {
      console.error('Failed to send heartbeat:', error);
    }
  }, [currentUser]);

  // Mark user as offline when leaving the page
  const markUserOffline = useCallback(async () => {
    if (!currentUser) return;
    
    try {
      await presenceService.updatePresence('offline');
    } catch (error) {
      console.error('Failed to mark user as offline:', error);
    }
  }, [currentUser]);

  // Set up heartbeat interval
  useEffect(() => {
    if (!currentUser) return;

    // Send initial heartbeat
    sendHeartbeat();
    
    // Set up interval for heartbeat (every 2 minutes)
    const interval = setInterval(sendHeartbeat, 2 * 60 * 1000);
    
    return () => clearInterval(interval);
  }, [currentUser, sendHeartbeat]);

  // Handle page visibility changes and beforeunload
  useEffect(() => {
    if (!currentUser) return;

    const handleVisibilityChange = () => {
      if (document.hidden) {
        // Page is hidden (user switched tabs or minimized)
        // Don't mark as offline immediately, just log it
        console.log('Page hidden - user may have switched tabs');
      } else {
        // Page is visible again
        sendHeartbeat();
      }
    };

    const handleBeforeUnload = () => {
      // User is leaving the page (closing tab, navigating away)
      // Use synchronous XMLHttpRequest to ensure it completes
      const xhr = new XMLHttpRequest();
      xhr.open('POST', `${import.meta.env.VITE_API_URL || 'http://localhost:8000/api'}/presence/update`, false); // synchronous
      xhr.setRequestHeader('Content-Type', 'application/json');
      xhr.setRequestHeader('Authorization', `Bearer ${localStorage.getItem('token')}`);
      xhr.send(JSON.stringify({ status: 'offline' }));
    };

    const handlePageHide = () => {
      // Page is being unloaded (more reliable than beforeunload)
      // Use synchronous request to ensure it completes
      const xhr = new XMLHttpRequest();
      xhr.open('POST', `${import.meta.env.VITE_API_URL || 'http://localhost:8000/api'}/presence/update`, false); // synchronous
      xhr.setRequestHeader('Content-Type', 'application/json');
      xhr.setRequestHeader('Authorization', `Bearer ${localStorage.getItem('token')}`);
      xhr.send(JSON.stringify({ status: 'offline' }));
    };

    const handleUnload = () => {
      // Additional fallback for page unload
      const xhr = new XMLHttpRequest();
      xhr.open('POST', `${import.meta.env.VITE_API_URL || 'http://localhost:8000/api'}/presence/update`, false); // synchronous
      xhr.setRequestHeader('Content-Type', 'application/json');
      xhr.setRequestHeader('Authorization', `Bearer ${localStorage.getItem('token')}`);
      xhr.send(JSON.stringify({ status: 'offline' }));
    };

    // Listen for page visibility changes
    document.addEventListener('visibilitychange', handleVisibilityChange);
    
    // Listen for page unload (less reliable)
    window.addEventListener('beforeunload', handleBeforeUnload);
    
    // Listen for page hide (more reliable)
    window.addEventListener('pagehide', handlePageHide);
    
    // Additional fallback for unload
    window.addEventListener('unload', handleUnload);

    return () => {
      document.removeEventListener('visibilitychange', handleVisibilityChange);
      window.removeEventListener('beforeunload', handleBeforeUnload);
      window.removeEventListener('pagehide', handlePageHide);
      window.removeEventListener('unload', handleUnload);
    };
  }, [currentUser, markUserOffline, sendHeartbeat]);

  // Get initial presence
  useEffect(() => {
    getMyPresence();
  }, [getMyPresence]);

  // Fallback: Set up periodic refresh of presence data if Pusher fails
  useEffect(() => {
    if (!currentUser) return;

    // Refresh presence data every 15 seconds as a fallback (more frequent for better accuracy)
    const interval = setInterval(() => {
      // Only refresh if we have users in usersPresence
      const userIds = Object.keys(usersPresence);
      if (userIds.length > 0) {
        getUsersPresence(userIds);
      }
    }, 15000); // 15 seconds

    return () => clearInterval(interval);
  }, [currentUser, usersPresence, getUsersPresence]);

  return {
    myPresence,
    usersPresence,
    isLoading,
    getMyPresence,
    updatePresence,
    getUsersPresence,
    getUserPresence,
    sendHeartbeat,
    markUserOffline,
  };
};
