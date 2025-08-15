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
  currency: string; // Fallback currency code from API
  owner_currency?: string; // Preferred: owner currency code
  professional_currency?: string; // Optional: professional currency code
  budget?: number; // Legacy field support
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

export interface GritApplicationForBusiness {
  id: string;
  professional: {
    id: string;
    name: string;
    email: string;
    category: string;
    completion_rate: number;
    average_rating: number;
    total_projects_completed: number;
    qualifications?: string;
  };
  status: 'pending' | 'approved' | 'rejected' | 'withdrawn';
  applied_at: string;
  created_at: string;
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

  getMyGritApplications: async (params?: { 
    page?: number;
    per_page?: number;
    search?: string;
    status?: string;
    category?: string;
  }) => {
    const { data } = await api.get('/grit-applications', { params });
    return data;
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

  startTyping: async (gritId: string) => {
    const { data } = await api.post(`/grits/${gritId}/messages/typing/start`);
    return data;
  },

  stopTyping: async (gritId: string) => {
    const { data } = await api.post(`/grits/${gritId}/messages/typing/stop`);
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
  },

  // Applications API methods
  getGritApplications: async (gritId: string, page: number = 1, perPage: number = 10) => {
    const { data } = await api.get(`/grits/${gritId}/applications`, {
      params: { page, per_page: perPage }
    });
    return data;
  },

  getApplicationDetails: async (applicationId: string) => {
    const { data } = await api.get(`/applications/${applicationId}`);
    return data;
  },

  updateApplicationStatus: async (applicationId: string, status: 'approved' | 'rejected' | 'withdrawn') => {
    const { data } = await api.patch(`/applications/${applicationId}/status`, { status });
    return data;
  },

  // Professional details methods
  getProfessionalDetails: async (professionalId: string, gritId?: string) => {
    const params = gritId ? { grit_id: gritId } : {};
    const { data } = await api.get(`/professionals/${professionalId}`, { params });
    return data;
  },

  updateApplicationStatusFromDetails: async (applicationId: string, status: 'approved' | 'rejected') => {
    const { data } = await api.patch(`/applications/${applicationId}/status/details`, { status });
    return data;
  }
}; 