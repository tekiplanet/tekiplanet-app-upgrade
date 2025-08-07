import { apiClient } from '@/lib/api-client';

export const rewardService = {
  getUserTasks: async () => {
    try {
      const response = await apiClient.get('/rewards/tasks');
      console.log('getUserTasks response:', response.data);
      // Return the data property from the response
      return response.data.data || response.data;
    } catch (error) {
      console.error('getUserTasks error:', error);
      throw error;
    }
  },
  initiateConversion: async () => {
    try {
      const response = await apiClient.post('/rewards/convert');
      console.log('initiateConversion response:', response.data);
      // Return the data property from the response
      return response.data.data || response.data;
    } catch (error) {
      console.error('initiateConversion error:', error);
      throw error;
    }
  },
  debug: async () => {
    try {
      const response = await apiClient.get('/rewards/debug');
      console.log('debug response:', response.data);
      return response.data.data || response.data;
    } catch (error) {
      console.error('debug error:', error);
      throw error;
    }
  }
};
