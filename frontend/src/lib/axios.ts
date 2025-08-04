import axios from 'axios';
import { useAuthStore } from '@/store/useAuthStore';

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  withCredentials: true,
  timeout: 30000, // Increased from 10s to 30s
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
});

// Retry configuration
const MAX_RETRIES = 3;
const RETRY_DELAY = 1000; // 1 second

// Retry function
const retryRequest = async (error: any, retryCount: number = 0): Promise<any> => {
  const { config } = error;
  
  if (retryCount >= MAX_RETRIES) {
    return Promise.reject(error);
  }

  // Only retry on network errors or timeout errors
  if (!error.response && (error.code === 'ECONNABORTED' || error.message.includes('timeout'))) {
    console.log(`Retrying request (${retryCount + 1}/${MAX_RETRIES})...`);
    
    // Wait before retrying
    await new Promise(resolve => setTimeout(resolve, RETRY_DELAY * (retryCount + 1)));
    
    // Retry the request
    return apiClient.request(config);
  }
  
  return Promise.reject(error);
};

// Global error handler
const handleGlobalError = (error: any) => {
  // Handle timeout errors
  if (error.code === 'ECONNABORTED' || error.message?.includes('timeout')) {
    console.warn('Global timeout error detected:', error.message);
    
    // Dispatch a custom event for timeout errors
    window.dispatchEvent(new CustomEvent('api:timeout-error', {
      detail: {
        message: 'Request timed out. Please check your connection and try again.',
        error: error
      }
    }));
  }
  
  // Handle network errors
  if (!error.response) {
    console.warn('Global network error detected:', error.message);
    
    window.dispatchEvent(new CustomEvent('api:network-error', {
      detail: {
        message: 'Network error. Please check your internet connection.',
        error: error
      }
    }));
  }
};

// Request interceptor
apiClient.interceptors.request.use(
  (config) => {
    // Try getting token from localStorage first
    let token = localStorage.getItem('token');
    
    // If no token in localStorage, try getting from store
    if (!token) {
      const authStore = useAuthStore.getState();
      token = authStore.token;
    }

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
  },
  (error) => {
    handleGlobalError(error);
    return Promise.reject(error);
  }
);

// Response interceptor
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    // Handle timeout and network errors with retry logic
    if (!error.response) {
      // Check if it's a timeout error
      if (error.code === 'ECONNABORTED' || error.message.includes('timeout')) {
        console.warn('Request timeout detected, attempting retry...');
        
        // Try to retry the request
        try {
          const retryCount = error.config?.retryCount || 0;
          error.config.retryCount = retryCount + 1;
          
          return await retryRequest(error, retryCount);
        } catch (retryError) {
          // If retry fails, show connection error
          window.dispatchEvent(new CustomEvent('api:connection-error', {
            detail: {
              message: 'Connection timeout. Please check your internet connection and try again.'
            }
          }));
          return Promise.reject(retryError);
        }
      }
      
      // Handle other connection errors
      window.dispatchEvent(new CustomEvent('api:connection-error', {
        detail: {
          message: 'Unable to connect to server. Please check your internet connection.'
        }
      }));
    }
    
    // Handle authentication errors
    if (error.response?.status === 401) {
      // Clear auth state on 401
      localStorage.removeItem('token');
      useAuthStore.getState().logout();
    }
    
    // Call global error handler
    handleGlobalError(error);
    
    return Promise.reject(error);
  }
);

export { apiClient };
