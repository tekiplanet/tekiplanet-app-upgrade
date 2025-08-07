import { apiClient } from '@/lib/api-client';

export const rewardService = {
  getUserTasks: async (params?: {
    page?: number;
    per_page?: number;
    status?: string;
    sort_by?: string;
    sort_order?: string;
  }) => {
    try {
      const response = await apiClient.get('/rewards/tasks', { params });
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
  getTaskInstructions: async (userConversionTaskId: string) => {
    try {
      const response = await apiClient.get(`/rewards/tasks/${userConversionTaskId}/instructions`);
      return response.data.data || response.data;
    } catch (error) {
      console.error('getTaskInstructions error:', error);
      throw error;
    }
  },
  getTaskReward: async (userConversionTaskId: string) => {
    try {
      const response = await apiClient.get(`/rewards/tasks/${userConversionTaskId}/reward`);
      return response.data.data || response.data;
    } catch (error) {
      console.error('getTaskReward error:', error);
      throw error;
    }
  },
  claimCourseAccess: async (userConversionTaskId: string) => {
    try {
      const response = await apiClient.post(`/rewards/tasks/${userConversionTaskId}/claim-course-access`);
      return response.data.data || response.data;
    } catch (error) {
      console.error('claimCourseAccess error:', error);
      throw error;
    }
  },
  claimCashReward: async (userConversionTaskId: string) => {
    try {
      const response = await apiClient.post(`/rewards/tasks/${userConversionTaskId}/claim-cash`);
      return response.data.data || response.data;
    } catch (error) {
      console.error('claimCashReward error:', error);
      throw error;
    }
  },
  claimDiscountReward: async (userConversionTaskId: string) => {
    try {
      const response = await apiClient.post(`/rewards/tasks/${userConversionTaskId}/claim-discount`);
      return response.data.data || response.data;
    } catch (error) {
      console.error('claimDiscountReward error:', error);
      throw error;
    }
  },
  getDiscountSlip: async (userConversionTaskId: string) => {
    try {
      const response = await apiClient.get(`/rewards/tasks/${userConversionTaskId}/discount-slip`);
      return response.data.data || response.data;
    } catch (error) {
      console.error('getDiscountSlip error:', error);
      throw error;
    }
  },
  downloadDiscountSlip: async (userConversionTaskId: string) => {
    try {
      const response = await apiClient.get(`/rewards/tasks/${userConversionTaskId}/download-discount-slip`);
      return response.data.data || response.data;
    } catch (error) {
      console.error('downloadDiscountSlip error:', error);
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
