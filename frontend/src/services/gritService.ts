import { api } from '@/lib/api';

export interface CreateGritData {
  title: string;
  description: string;
  category_id: string;
  skills_required: string[];
  owner_budget: number;
  deadline: string; // YYYY-MM-DD
}

export interface Grit {
  id: string;
  title: string;
  description: string;
  skills_required: string[];
  requirements?: string; // Added requirements field
  professional_budget: number;
  owner_budget: number;
  currency: string;
  deadline: string;
  status: 'open' | 'negotiation' | 'in_progress' | 'pending_completion_approval' | 'completed' | 'disputed' | 'closed';
  admin_approval_status: 'pending' | 'approved' | 'rejected';
  is_public: boolean;
  created_at?: string;
  user: { // The business owner who created the grit
    id: string;
    name: string;
    avatar?: string;
  };
  category: {
    id: string;
    name: string;
  };
  assigned_professional?: { // The professional assigned to the grit
    id: string;
    name: string;
    avatar?: string;
  } | null;
  // Frontend-specific or aggregated fields
  applications_count?: number;
  has_applied?: boolean;
  can_apply?: boolean;
  unread_messages?: number;
  application_status?: 'pending' | 'approved' | 'rejected' | 'withdrawn';
  assigned_professional_id?: string; // Kept for logic checks
  unread_messages_count?: number;
  payments?: any[];
}

export interface Category {
  id: string;
  name: string;
  description: string;
  icon: string;
}

interface Professional {
  id: string;
  category_id: string;
  status: 'active' | 'inactive' | 'suspended';
  // ... other fields
}

interface ProfileCheckResponse {
  has_profile: boolean;
  profile: Professional | null;
}

export interface GritApplication {
  id: string;
  grit: Partial<Grit>; // Using Partial<Grit> as we might not get the full grit object
  status: 'pending' | 'approved' | 'rejected' | 'withdrawn';
  created_at: string;
  applied_at: string;
}

export const gritService = {
  getGrits: async (params?: { 
    category_id?: string;
    search?: string;
  }) => {
    const { data } = await api.get('/grits', { params });
    return data;
  },

  getCategories: async () => {
    const { data } = await api.get('/professional/categories');
    return data.categories;
  },

  getGritDetails: async (id: string) => {
    const { data } = await api.get(`/grits/${id}`);
    return data;
  },

  applyForGrit: async (gritId: string) => {
    const { data } = await api.post(`/grits/${gritId}/apply`);
    return data;
  },

  withdrawGritApplication: async (applicationId: string) => {
    const { data } = await api.post(`/grit-applications/${applicationId}/withdraw`);
    return data;
  },

  checkProfessionalProfile: async () => {
    const { data } = await api.get('/professional/profile/check');
    return data;
  },

  getMyGritApplications: async () => {
    const { data } = await api.get('/grit-applications');
    return data.applications;
  },

  getGritMessages: async (gritId: string) => {
    const { data } = await api.get(`/grits/${gritId}/messages`);
    return data.messages;
  },

  sendGritMessage: async (gritId: string, message: string) => {
    const { data } = await api.post(`/grits/${gritId}/messages`, { message });
    return data;
  },

  markGritMessagesAsRead: async (gritId: string) => {
    const { data } = await api.post(`/grits/${gritId}/messages/mark-read`);
    return data;
  },

  getMyGrits: async () => {
    const { data } = await api.get('/my-grits');
    // API returns { grits: { data: [...] }}
    return data.grits?.data || [];
  },

  createGrit: async (gritData: CreateGritData) => {
    const { data } = await api.post('/grits', gritData);
    return data;
  },

  updateGrit: async (gritId: string, gritData: any) => {
    const { data } = await api.put(`/grits/${gritId}`, gritData);
    return data;
  }
}; 