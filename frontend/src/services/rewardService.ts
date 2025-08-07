import { apiClient } from '@/lib/api-client';

export const rewardService = {
  getUserTasks: async () => {
    const response = await apiClient.get('/rewards/tasks');
    return response.data;
  },
  initiateConversion: async () => {
    const response = await apiClient.post('/rewards/convert');
    return response.data;
  }
};
