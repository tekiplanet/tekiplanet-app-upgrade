import { settingsService } from '@/services/settingsService';

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
    
    // For now, we'll use a simple conversion based on stored rates
    // In a real implementation, you'd fetch current exchange rates
    const response = await fetch(`/api/currency/convert`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
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
    return data.converted_amount;
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
    const settings = await settingsService.getAllSettings();
    const targetCurrency = currencyCode || settings?.default_currency || 'USD';
    
    // For now, return the default symbol from settings
    // In a full implementation, you'd fetch this from the currency database
    return settings?.currency_symbol || '$';
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
    const response = await fetch('/api/currencies');
    if (!response.ok) {
      throw new Error('Failed to fetch currencies');
    }
    
    const data = await response.json();
    return data.currencies || [];
  } catch (error) {
    console.error('Error fetching currencies:', error);
    return [];
  }
}; 