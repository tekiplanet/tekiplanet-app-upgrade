import { apiClient } from '@/lib/api-client';

export interface OnboardingStatus {
  is_complete: boolean;
  current_step: 'account_type' | 'profile' | 'complete';
  user: any;
}

export interface AccountTypeData {
  account_type: 'student' | 'business' | 'professional';
}

export interface ProfileData {
  first_name: string;
  last_name: string;
  avatar?: File;
}

export const onboardingService = {
  /**
   * Get onboarding status
   */
  async getOnboardingStatus(): Promise<OnboardingStatus> {
    const response = await apiClient.get('/onboarding/status');
    return response.data;
  },

  /**
   * Update account type (Step 1)
   */
  async updateAccountType(data: AccountTypeData): Promise<any> {
    const response = await apiClient.post('/onboarding/account-type', data);
    return response.data;
  },

  /**
   * Update profile (Step 2)
   */
  async updateProfile(data: ProfileData): Promise<any> {
    const formData = new FormData();
    formData.append('first_name', data.first_name);
    formData.append('last_name', data.last_name);
    
    if (data.avatar) {
      formData.append('avatar', data.avatar);
    }

    const response = await apiClient.post('/onboarding/profile', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },
}; 