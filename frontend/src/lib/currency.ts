import { settingsService } from '@/services/settingsService';
import { useState, useEffect } from 'react';

export interface Currency {
  id: string;
  name: string;
  code: string;
  symbol: string;
  rate: number;
  is_base: boolean;
  is_active: boolean;
  decimal_places: number;
  position: number;
}

export interface CurrencySettings {
  default_currency?: string;
  currency_symbol?: string;
}

// Cache for currency symbols to avoid repeated API calls
// Note: We no longer convert amounts for display purposes
// Amounts are displayed with the user's preferred currency symbol but keep the original value
const symbolCache = new Map<string, string>();

/**
 * Format amount with proper currency symbol and decimal places (synchronous version)
 * @param amount - The amount to format
 * @param currencyCode - The currency code (e.g., 'USD', 'NGN')
 * @param options - Additional formatting options
 * @returns Formatted currency string
 */
export const formatAmountSync = (
  amount: number | string | null | undefined,
  currencyCode?: string,
  options: {
    showSymbol?: boolean;
    showCode?: boolean;
    decimalPlaces?: number;
    locale?: string;
    currencySymbol?: string;
  } = {}
): string => {
  if (amount === null || amount === undefined) {
    return '0';
  }

  const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
  
  if (isNaN(numAmount)) {
    return '0';
  }

  try {
    const targetCurrency = currencyCode || 'USD';
    const currencySymbol = options.currencySymbol || '$';
    const decimalPlaces = options.decimalPlaces ?? 2;
    const locale = options.locale || 'en-US';

    // Format the number with proper decimal places
    const formattedNumber = new Intl.NumberFormat(locale, {
      minimumFractionDigits: decimalPlaces,
      maximumFractionDigits: decimalPlaces,
    }).format(numAmount);

    // Build the result based on options
    let result = formattedNumber;
    
    if (options.showSymbol !== false) {
      result = `${currencySymbol}${result}`;
    }
    
    if (options.showCode) {
      result = `${result} ${targetCurrency}`;
    }

    return result;
  } catch (error) {
    console.error('Error formatting amount:', error);
    return `${amount}`;
  }
};

/**
 * Format amount with proper currency symbol and decimal places
 * @param amount - The amount to format
 * @param currencyCode - The currency code (e.g., 'USD', 'NGN')
 * @param options - Additional formatting options
 * @returns Formatted currency string
 */
export const formatAmount = async (
  amount: number | string | null | undefined,
  currencyCode?: string,
  options: {
    showSymbol?: boolean;
    showCode?: boolean;
    decimalPlaces?: number;
    locale?: string;
  } = {}
): Promise<string> => {
  if (amount === null || amount === undefined) {
    return '0';
  }

  const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
  
  if (isNaN(numAmount)) {
    return '0';
  }

  try {
    // Get currency settings
    const settings = await settingsService.getAllSettings();
    const targetCurrency = currencyCode || settings?.default_currency || 'USD';
    const currencySymbol = settings?.currency_symbol || '$';
    const decimalPlaces = options.decimalPlaces ?? 2;
    const locale = options.locale || 'en-US';

    return formatAmountSync(numAmount, targetCurrency, {
      ...options,
      currencySymbol,
      decimalPlaces,
      locale,
    });
  } catch (error) {
    console.error('Error formatting amount:', error);
    return formatAmountSync(numAmount, currencyCode, options);
  }
};

/**
 * Format amount with currency symbol (shorthand for common use) - synchronous
 * @param amount - The amount to format
 * @param currencyCode - The currency code
 * @param currencySymbol - The currency symbol
 * @returns Formatted currency string with symbol
 */
export const formatCurrencySync = (
  amount: number | string | null | undefined,
  currencyCode?: string,
  currencySymbol?: string
): string => {
  return formatAmountSync(amount, currencyCode, { 
    showSymbol: true, 
    currencySymbol 
  });
};

/**
 * Format amount with currency symbol (shorthand for common use)
 * @param amount - The amount to format
 * @param currencyCode - The currency code
 * @returns Formatted currency string with symbol
 */
export const formatCurrency = async (
  amount: number | string | null | undefined,
  currencyCode?: string
): Promise<string> => {
  return formatAmount(amount, currencyCode, { showSymbol: true });
};

/**
 * Format amount without currency symbol
 * @param amount - The amount to format
 * @param currencyCode - The currency code
 * @returns Formatted number string
 */
export const formatNumber = async (
  amount: number | string | null | undefined,
  currencyCode?: string
): Promise<string> => {
  return formatAmount(amount, currencyCode, { showSymbol: false });
};

/**
 * Convert amount from one currency to another using exchange rates
 * @param amount - The amount to convert
 * @param fromCurrency - Source currency code
 * @param toCurrency - Target currency code
 * @returns Converted amount
 */
export const convertAmount = async (
  amount: number | string,
  fromCurrency: string,
  toCurrency: string
): Promise<number> => {
  if (fromCurrency === toCurrency) {
    return typeof amount === 'string' ? parseFloat(amount) : amount;
  }

  try {
    const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
    const token = localStorage.getItem('token');
    const response = await fetch(`${import.meta.env.VITE_API_URL}/currency/convert`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        amount: numAmount,
        from_currency: fromCurrency,
        to_currency: toCurrency,
      }),
    });

    if (!response.ok) {
      throw new Error('Currency conversion failed');
    }

    const data = await response.json();
    return data.data?.converted_amount || data.converted_amount;
  } catch (error) {
    console.error('Error converting currency:', error);
    // Return original amount if conversion fails
    return typeof amount === 'string' ? parseFloat(amount) : amount;
  }
};



/**
 * Convert amount to base currency
 * @param amount - The amount to convert
 * @param fromCurrency - Source currency code
 * @returns Amount in base currency
 */
export const convertToBase = async (
  amount: number | string,
  fromCurrency: string
): Promise<number> => {
  const settings = await settingsService.getAllSettings();
  const baseCurrency = settings?.default_currency || 'USD';
  
  return convertAmount(amount, fromCurrency, baseCurrency);
};

/**
 * Convert amount from base currency
 * @param amount - The amount to convert
 * @param toCurrency - Target currency code
 * @returns Converted amount
 */
export const convertFromBase = async (
  amount: number | string,
  toCurrency: string
): Promise<number> => {
  const settings = await settingsService.getAllSettings();
  const baseCurrency = settings?.default_currency || 'USD';
  
  return convertAmount(amount, baseCurrency, toCurrency);
};

/**
 * Get currency symbol for a given currency code
 * @param currencyCode - The currency code
 * @returns Currency symbol
 */
export const getCurrencySymbol = async (currencyCode?: string): Promise<string> => {
  try {
    const targetCurrency = currencyCode || 'USD';
    
    // Check cache first
    if (symbolCache.has(targetCurrency)) {
      return symbolCache.get(targetCurrency)!;
    }
    
    // Fetch currency symbol from API
    const token = localStorage.getItem('token');
    const response = await fetch(`${import.meta.env.VITE_API_URL}/currency/${targetCurrency}/symbol`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });

    if (!response.ok) {
      console.error('Currency symbol API error:', response.status, response.statusText);
      const errorText = await response.text();
      console.error('Error response:', errorText);
      throw new Error(`Failed to fetch currency symbol: ${response.status} ${response.statusText}`);
    }

    const data = await response.json();
    const symbol = data.data?.symbol || data.symbol || '$';
    
    // Cache the result
    symbolCache.set(targetCurrency, symbol);
    
    return symbol;
  } catch (error) {
    console.error('Error getting currency symbol:', error);
    return '$';
  }
};

/**
 * Get decimal places for a given currency code
 * @param currencyCode - The currency code
 * @returns Number of decimal places
 */
export const getCurrencyDecimalPlaces = async (currencyCode?: string): Promise<number> => {
  try {
    // For now, return default decimal places
    // In a full implementation, you'd fetch this from the currency database
    return 2;
  } catch (error) {
    console.error('Error getting currency decimal places:', error);
    return 2;
  }
};

/**
 * Parse currency string back to number
 * @param currencyString - The formatted currency string
 * @returns Parsed number
 */
export const parseCurrency = (currencyString: string): number => {
  if (!currencyString) return 0;
  
  // Remove currency symbols and codes, keep only numbers and decimal points
  const cleaned = currencyString.replace(/[^\d.,]/g, '');
  
  // Handle different decimal separators
  const normalized = cleaned.replace(',', '.');
  
  const parsed = parseFloat(normalized);
  return isNaN(parsed) ? 0 : parsed;
};

/**
 * Validate currency code format
 * @param currencyCode - The currency code to validate
 * @returns True if valid, false otherwise
 */
export const isValidCurrencyCode = (currencyCode: string): boolean => {
  return /^[A-Z]{3}$/.test(currencyCode);
};

/**
 * Get all available currencies (for dropdowns, etc.)
 * @returns Array of available currencies
 */
export const getAvailableCurrencies = async (): Promise<Currency[]> => {
  try {
    const response = await fetch(`${import.meta.env.VITE_API_URL}/currencies`);
    if (!response.ok) {
      throw new Error('Failed to fetch currencies');
    }
    
    const data = await response.json();
    return data.data?.currencies || data.currencies || [];
  } catch (error) {
    console.error('Error fetching currencies:', error);
    return [];
  }
}; 

/**
 * Format amount in user's currency (no conversion, just display formatting)
 * This function formats the amount using the user's preferred currency symbol
 * @param amount - The amount from database (stored in base currency)
 * @param userCurrencyCode - The user's preferred currency code
 * @param options - Additional formatting options
 * @returns Formatted currency string in user's currency
 */
export const formatAmountInUserCurrency = async (
  amount: number | string | null | undefined,
  userCurrencyCode?: string,
  options: {
    showSymbol?: boolean;
    showCode?: boolean;
    decimalPlaces?: number;
    locale?: string;
  } = {}
): Promise<string> => {
  if (amount === null || amount === undefined) {
    return '0';
  }

  const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
  
  if (isNaN(numAmount)) {
    return '0';
  }

  try {
    const targetCurrency = userCurrencyCode || 'NGN';
    
    // Get currency symbol for the target currency from database
    const currencySymbol = await getCurrencySymbol(targetCurrency);
    
    // Format the amount with the user's currency symbol (no conversion)
    return formatAmountSync(numAmount, targetCurrency, {
      ...options,
      currencySymbol
    });
  } catch (error) {
    console.error('Error formatting amount in user currency:', error);
    // Fallback to base currency formatting
    return formatAmountSync(numAmount, 'NGN', {
      ...options,
      currencySymbol: '₦'
    });
  }
};

/**
 * Format amount in user's currency (synchronous version with fallback)
 * @param amount - The amount from database (stored in base currency)
 * @param userCurrencyCode - The user's preferred currency code
 * @param currencySymbol - The currency symbol to use
 * @returns Formatted currency string in user's currency
 */
export const formatAmountInUserCurrencySync = (
  amount: number | string | null | undefined,
  userCurrencyCode?: string,
  currencySymbol?: string
): string => {
  if (amount === null || amount === undefined) {
    return '0';
  }

  const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
  
  if (isNaN(numAmount)) {
    return '0';
  }

  const targetCurrency = userCurrencyCode || 'NGN';
  const symbol = currencySymbol || (targetCurrency === 'NGN' ? '₦' : '$');
  
  return formatAmountSync(numAmount, targetCurrency, {
    showSymbol: true,
    currencySymbol: symbol
  });
};

/**
 * React hook for formatting amounts in user's currency
 * @param amount - The amount in base currency (NGN)
 * @param userCurrencyCode - The user's preferred currency code
 * @returns Formatted currency string and loading state
 */
export const useCurrencyFormat = (amount: number | string | null | undefined, userCurrencyCode?: string) => {
  const [formattedAmount, setFormattedAmount] = useState<string>('0');
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const formatAmount = async () => {
      try {
        setIsLoading(true);
        const formatted = await formatAmountInUserCurrency(amount, userCurrencyCode);
        setFormattedAmount(formatted);
      } catch (error) {
        console.error('Error formatting currency:', error);
        setFormattedAmount('0');
      } finally {
        setIsLoading(false);
      }
    };

    formatAmount();
  }, [amount, userCurrencyCode]);

  return { formattedAmount, isLoading };
};

/**
 * React hook for formatting amounts in user's currency with symbol caching
 * @param amount - The amount in base currency (NGN)
 * @param userCurrencyCode - The user's preferred currency code
 * @param currencySymbol - Optional currency symbol to avoid API call
 * @returns Formatted currency string and loading state
 */
export const useCurrencyFormatWithSymbol = (
  amount: number | string | null | undefined, 
  userCurrencyCode?: string,
  currencySymbol?: string
) => {
  const [formattedAmount, setFormattedAmount] = useState<string>('0');
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const formatAmount = async () => {
      try {
        setIsLoading(true);
        
        if (currencySymbol) {
          // Use provided symbol to avoid API call
          const formatted = formatAmountInUserCurrencySync(amount, userCurrencyCode, currencySymbol);
          setFormattedAmount(formatted);
        } else {
          // Do full conversion with API call
          const formatted = await formatAmountInUserCurrency(amount, userCurrencyCode);
          setFormattedAmount(formatted);
        }
      } catch (error) {
        console.error('Error formatting currency:', error);
        setFormattedAmount('0');
      } finally {
        setIsLoading(false);
      }
    };

    formatAmount();
  }, [amount, userCurrencyCode, currencySymbol]);

  return { formattedAmount, isLoading };
};

/**
 * Test currency conversion API
 * @returns Promise<boolean>
 */
export const testCurrencyConversion = async (): Promise<boolean> => {
  try {
    const token = localStorage.getItem('token');
    const response = await fetch(`${import.meta.env.VITE_API_URL}/currency/convert`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        amount: 1000,
        from_currency: 'NGN',
        to_currency: 'USD',
      }),
    });

    if (!response.ok) {
      console.error('Currency conversion API test failed:', response.status, response.statusText);
      return false;
    }

    const data = await response.json();
    console.log('Currency conversion API test successful:', data);
    console.log('Conversion API Response structure:', JSON.stringify(data, null, 2));
    return true;
  } catch (error) {
    console.error('Currency conversion API test error:', error);
    return false;
  }
};

/**
 * Test currency symbol API
 * @returns Promise<boolean>
 */
export const testCurrencySymbol = async (): Promise<boolean> => {
  try {
    const token = localStorage.getItem('token');
    const response = await fetch(`${import.meta.env.VITE_API_URL}/currency/USD/symbol`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });

    if (!response.ok) {
      console.error('Currency symbol API test failed:', response.status, response.statusText);
      return false;
    }

    const data = await response.json();
    console.log('Currency symbol API test successful:', data);
    console.log('API Response structure:', JSON.stringify(data, null, 2));
    return true;
  } catch (error) {
    console.error('Currency symbol API test error:', error);
    return false;
  }
}; 