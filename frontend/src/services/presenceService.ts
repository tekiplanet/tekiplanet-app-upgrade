import { api } from '@/lib/api';

export interface UserPresence {
  user_id: string;
  online_status: 'online' | 'away' | 'offline';
  last_seen_at: string | null;
  last_activity_at: string | null;
  is_online: boolean;
}

export interface PresenceResponse {
  success: boolean;
  presence: UserPresence;
  message?: string;
}

export interface MultipleUsersPresenceResponse {
  success: boolean;
  presence: { [userId: string]: UserPresence };
}

export const presenceService = {
  // Get current user's presence
  getMyPresence: async (): Promise<PresenceResponse> => {
    const { data } = await api.get('/presence/my-presence');
    return data;
  },

  // Update current user's presence status
  updatePresence: async (status: 'online' | 'away' | 'offline'): Promise<PresenceResponse> => {
    const { data } = await api.post('/presence/update', { status });
    return data;
  },

  // Send heartbeat to keep user online
  sendHeartbeat: async (): Promise<{ success: boolean; message: string }> => {
    const { data } = await api.post('/presence/heartbeat');
    return data;
  },

  // Get another user's presence
  getUserPresence: async (userId: string): Promise<PresenceResponse> => {
    const { data } = await api.get(`/presence/user/${userId}`);
    return data;
  },

  // Get multiple users' presence
  getMultipleUsersPresence: async (userIds: string[]): Promise<MultipleUsersPresenceResponse> => {
    // Validate that all IDs are valid UUIDs
    const validUserIds = userIds.filter(id => {
      const isValid = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(id);
      return isValid;
    });
    
    if (validUserIds.length === 0) {
      return { success: false, presence: {} };
    }
    
    const { data } = await api.post('/presence/multiple-users', { user_ids: validUserIds });
    return data;
  },
};
