<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CurrencyService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('EXCHANGE_RATE_API_KEY');
        $this->baseUrl = "https://v6.exchangerate-api.com/v6/{$this->apiKey}/pair/";
    }

    /**
     * Format amount with proper currency symbol and decimal places
     * @param float $amount
     * @param string $currencyCode
     * @param array $options
     * @return string
     */
    public function formatAmount($amount, $currencyCode = null, $options = [])
    {
        if ($amount === null || $amount === '') {
            return '0';
        }

        $amount = (float) $amount;
        
        if (is_nan($amount)) {
            return '0';
        }

        try {
            // Get currency details
            $currency = $this->getCurrency($currencyCode);
            
            if (!$currency) {
                // Fallback to default formatting
                return number_format($amount, 2) . ' ' . ($currencyCode ?: 'USD');
            }

            $decimalPlaces = $options['decimal_places'] ?? $currency->decimal_places ?? 2;
            $showSymbol = $options['show_symbol'] ?? true;
            $showCode = $options['show_code'] ?? false;
            $locale = $options['locale'] ?? 'en_US';

            // Format the number
            $formattedNumber = number_format($amount, $decimalPlaces);

            // Build result
            $result = $formattedNumber;
            
            if ($showSymbol) {
                $result = $currency->symbol . $result;
            }
            
            if ($showCode) {
                $result = $result . ' ' . $currency->code;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Error formatting amount:', [
                'error' => $e->getMessage(),
                'amount' => $amount,
                'currency_code' => $currencyCode
            ]);
            return (string) $amount;
        }
    }

    /**
     * Format amount with currency symbol (shorthand)
     * @param float $amount
     * @param string $currencyCode
     * @return string
     */
    public function formatCurrency($amount, $currencyCode = null)
    {
        return $this->formatAmount($amount, $currencyCode, ['show_symbol' => true]);
    }

    /**
     * Format amount without currency symbol
     * @param float $amount
     * @param string $currencyCode
     * @return string
     */
    public function formatNumber($amount, $currencyCode = null)
    {
        return $this->formatAmount($amount, $currencyCode, ['show_symbol' => false]);
    }

    /**
     * Convert amount from one currency to another
     * @param float $amount
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return float
     */
    public function convertAmount($amount, $fromCurrency, $toCurrency)
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        try {
            // Get currency objects
            $fromCurrencyObj = $this->getCurrency($fromCurrency);
            $toCurrencyObj = $this->getCurrency($toCurrency);

            if (!$fromCurrencyObj || !$toCurrencyObj) {
                Log::warning('Currency conversion failed: Invalid currency codes', [
                    'from_currency' => $fromCurrency,
                    'to_currency' => $toCurrency
                ]);
                return $amount;
            }

            // Convert using stored rates
            // If converting from base currency to another currency
            if ($fromCurrencyObj->is_base) {
                $convertedAmount = $amount * $toCurrencyObj->rate;
            }
            // If converting from another currency to base currency
            elseif ($toCurrencyObj->is_base) {
                $convertedAmount = $amount / $fromCurrencyObj->rate;
            }
            // If converting between two non-base currencies
            else {
                // Convert from currency A to base, then from base to currency B
                $amountInBase = $amount / $fromCurrencyObj->rate;
                $convertedAmount = $amountInBase * $toCurrencyObj->rate;
            }
            
            return round($convertedAmount, $toCurrencyObj->decimal_places);
        } catch (\Exception $e) {
            Log::error('Currency conversion failed:', [
                'error' => $e->getMessage(),
                'from_currency' => $fromCurrency,
                'to_currency' => $toCurrency,
                'amount' => $amount
            ]);
            return $amount;
        }
    }

    /**
     * Convert amount to base currency
     * @param float $amount
     * @param string $fromCurrency
     * @return float
     */
    public function convertToBase($amount, $fromCurrency)
    {
        $baseCurrency = $this->getBaseCurrency();
        return $this->convertAmount($amount, $fromCurrency, $baseCurrency->code);
    }

    /**
     * Convert amount from base currency
     * @param float $amount
     * @param string $toCurrency
     * @return float
     */
    public function convertFromBase($amount, $toCurrency)
    {
        $baseCurrency = $this->getBaseCurrency();
        return $this->convertAmount($amount, $baseCurrency->code, $toCurrency);
    }

    /**
     * Get currency object by code
     * @param string $currencyCode
     * @return Currency|null
     */
    public function getCurrency($currencyCode = null)
    {
        if (!$currencyCode) {
            return $this->getBaseCurrency();
        }

        return Currency::where('code', strtoupper($currencyCode))
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get base currency
     * @return Currency|null
     */
    public function getBaseCurrency()
    {
        return Currency::where('is_base', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all active currencies
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveCurrencies()
    {
        return Currency::where('is_active', true)
            ->orderBy('position')
            ->get();
    }

    /**
     * Get currency symbol
     * @param string $currencyCode
     * @return string
     */
    public function getCurrencySymbol($currencyCode = null)
    {
        $currency = $this->getCurrency($currencyCode);
        return $currency ? $currency->symbol : '$';
    }

    /**
     * Get currency decimal places
     * @param string $currencyCode
     * @return int
     */
    public function getCurrencyDecimalPlaces($currencyCode = null)
    {
        $currency = $this->getCurrency($currencyCode);
        return $currency ? $currency->decimal_places : 2;
    }

    /**
     * Parse currency string back to number
     * @param string $currencyString
     * @return float
     */
    public function parseCurrency($currencyString)
    {
        if (empty($currencyString)) {
            return 0;
        }

        // Remove currency symbols and codes, keep only numbers and decimal points
        $cleaned = preg_replace('/[^\d.,]/', '', $currencyString);
        
        // Handle different decimal separators
        $normalized = str_replace(',', '.', $cleaned);
        
        $parsed = (float) $normalized;
        return is_nan($parsed) ? 0 : $parsed;
    }

    /**
     * Validate currency code format
     * @param string $currencyCode
     * @return bool
     */
    public function isValidCurrencyCode($currencyCode)
    {
        return preg_match('/^[A-Z]{3}$/', $currencyCode);
    }

    /**
     * Update exchange rates from external API
     * @return bool
     */
    public function updateExchangeRates()
    {
        try {
            $baseCurrency = $this->getBaseCurrency();
            if (!$baseCurrency) {
                throw new \Exception('No base currency found');
            }

            $currencies = Currency::where('is_active', true)
                ->where('id', '!=', $baseCurrency->id)
                ->get();

            foreach ($currencies as $currency) {
                $rate = $this->fetchExchangeRate($baseCurrency->code, $currency->code);
                if ($rate !== null) {
                    $currency->update(['rate' => $rate]);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update exchange rates:', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Fetch exchange rate from external API
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return float|null
     */
    protected function fetchExchangeRate($fromCurrency, $toCurrency)
    {
        if (!$this->apiKey) {
            Log::warning('Exchange rate API key not configured');
            return null;
        }

        try {
            $cacheKey = "exchange_rate_{$fromCurrency}_{$toCurrency}";
            
            // Check cache first
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $response = Http::get("{$this->baseUrl}{$fromCurrency}/{$toCurrency}");
            
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch exchange rate');
            }

            $data = $response->json();
            
            if (!isset($data['conversion_rate'])) {
                throw new \Exception('Invalid response from exchange rate API');
            }

            $rate = $data['conversion_rate'];
            
            // Cache for 1 hour
            Cache::put($cacheKey, $rate, 3600);
            
            return $rate;
        } catch (\Exception $e) {
            Log::error('Failed to fetch exchange rate:', [
                'error' => $e->getMessage(),
                'from_currency' => $fromCurrency,
                'to_currency' => $toCurrency
            ]);
            return null;
        }
    }

    /**
     * Convert amount to NGN (legacy method for backward compatibility)
     * @param float $amount
     * @param string $fromCurrency
     * @return float
     */
    public function convertToNGN($amount, $fromCurrency)
    {
        return $this->convertAmount($amount, $fromCurrency, 'NGN');
    }
}
