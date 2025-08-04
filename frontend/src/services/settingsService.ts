import { apiClient } from '@/lib/axios';

interface Settings {
  site_name?: string;
  site_description?: string;
  default_currency?: string;
  currency_symbol?: string;
  enrollment_fee?: number;
  primary_color?: string;
  secondary_color?: string;
  [key: string]: any;
}

// Default fallback settings
const DEFAULT_SETTINGS: Settings = {
  site_name: 'TekiPlanet',
  site_description: 'Your learning platform',
  default_currency: 'USD',
  currency_symbol: '$',
  enrollment_fee: 1000,
  primary_color: '#3b82f6',
  secondary_color: '#1e40af'
};

class SettingsService {
  private settings: Settings = {};
  private isFetching = false;
  private fetchPromise: Promise<Settings> | null = null;

  async fetchSettings(): Promise<Settings> {
    // Prevent multiple simultaneous requests
    if (this.isFetching && this.fetchPromise) {
      return this.fetchPromise;
    }

    this.isFetching = true;
    this.fetchPromise = this._fetchSettingsWithRetry();

    try {
      const result = await this.fetchPromise;
      this.settings = result;
      return result;
    } finally {
      this.isFetching = false;
      this.fetchPromise = null;
    }
  }

  private async _fetchSettingsWithRetry(): Promise<Settings> {
    try {
      const response = await apiClient.get('/settings', {
        timeout: 15000, // Specific timeout for settings
      });
      return response.data;
    } catch (error: any) {
      console.error('Failed to fetch settings:', error);
      
      // Handle timeout specifically
      if (error.code === 'ECONNABORTED' || error.message?.includes('timeout')) {
        console.warn('Settings fetch timeout, using cached or default settings');
        
        // Return cached settings if available, otherwise defaults
        if (Object.keys(this.settings).length > 0) {
          return this.settings;
        }
        return DEFAULT_SETTINGS;
      }
      
      // For other errors, return cached settings or defaults
      if (Object.keys(this.settings).length > 0) {
        console.log('Using cached settings due to fetch error');
        return this.settings;
      }
      
      console.log('Using default settings due to fetch error');
      return DEFAULT_SETTINGS;
    }
  }

  async getAllSettings(): Promise<Settings> {
    // If settings are not fetched yet, fetch them first
    if (Object.keys(this.settings).length === 0) {
      const settings = await this.fetchSettings();
      this.settings = settings;
    }
    return this.settings;
  }

  async getSetting(key: string): Promise<any> {
    // If settings are not fetched yet, fetch them first
    if (Object.keys(this.settings).length === 0) {
      const settings = await this.fetchSettings();
      this.settings = settings;
    }
    return this.settings[key] ?? DEFAULT_SETTINGS[key];
  }

  getDefaultCurrency(): string {
    return this.settings.default_currency || DEFAULT_SETTINGS.default_currency!;
  }

  getEnrollmentFee(): number {
    return this.settings.enrollment_fee || DEFAULT_SETTINGS.enrollment_fee!;
  }

  getCurrencyCode(): string {
    return this.settings.default_currency || DEFAULT_SETTINGS.default_currency!;
  }

  // Method to clear cache and force refresh
  clearCache(): void {
    this.settings = {};
    this.isFetching = false;
    this.fetchPromise = null;
  }

  // Method to get settings synchronously (from cache)
  getCachedSettings(): Settings {
    return Object.keys(this.settings).length > 0 ? this.settings : DEFAULT_SETTINGS;
  }
}

export const settingsService = new SettingsService();
