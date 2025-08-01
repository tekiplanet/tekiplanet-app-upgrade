import axios from 'axios';
import useAuthStore from '@/store/useAuthStore';

axios.defaults.withCredentials = true;

const apiClient = axios.create({
  baseURL: 'http://192.168.112.55:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  withCredentials: true
});

// Request interceptor to add auth token
apiClient.interceptors.request.use(
  (config) => {
    // Only set Authorization header for explicit token-based API calls, not for session/cookie auth
    if (config.headers && config.headers['X-Use-Token'] !== false) {
      const token = useAuthStore.getState().token;
      if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
      }
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor for error handling
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      useAuthStore.getState().logout();
    }
    return Promise.reject(error);
  }
);

export { apiClient };
export default apiClient;
