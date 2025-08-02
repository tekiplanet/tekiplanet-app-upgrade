<?php

namespace App\Traits;

use App\Services\CurrencyService;

trait HasCurrencyFormatting
{
    /**
     * Format amount with currency symbol
     * @param float $amount
     * @param string|null $currencyCode
     * @return string
     */
    public function formatCurrency($amount, $currencyCode = null): string
    {
        $currencyService = app(CurrencyService::class);
        return $currencyService->formatCurrency($amount, $currencyCode);
    }

    /**
     * Format amount without currency symbol
     * @param float $amount
     * @param string|null $currencyCode
     * @return string
     */
    public function formatNumber($amount, $currencyCode = null): string
    {
        $currencyService = app(CurrencyService::class);
        return $currencyService->formatNumber($amount, $currencyCode);
    }

    /**
     * Format amount with custom options
     * @param float $amount
     * @param string|null $currencyCode
     * @param array $options
     * @return string
     */
    public function formatAmount($amount, $currencyCode = null, $options = []): string
    {
        $currencyService = app(CurrencyService::class);
        return $currencyService->formatAmount($amount, $currencyCode, $options);
    }

    /**
     * Convert amount from one currency to another
     * @param float $amount
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return float
     */
    public function convertAmount($amount, $fromCurrency, $toCurrency): float
    {
        $currencyService = app(CurrencyService::class);
        return $currencyService->convertAmount($amount, $fromCurrency, $toCurrency);
    }

    /**
     * Convert amount to base currency
     * @param float $amount
     * @param string $fromCurrency
     * @return float
     */
    public function convertToBase($amount, $fromCurrency): float
    {
        $currencyService = app(CurrencyService::class);
        return $currencyService->convertToBase($amount, $fromCurrency);
    }

    /**
     * Convert amount from base currency
     * @param float $amount
     * @param string $toCurrency
     * @return float
     */
    public function convertFromBase($amount, $toCurrency): float
    {
        $currencyService = app(CurrencyService::class);
        return $currencyService->convertFromBase($amount, $toCurrency);
    }

    /**
     * Get currency symbol
     * @param string|null $currencyCode
     * @return string
     */
    public function getCurrencySymbol($currencyCode = null): string
    {
        $currencyService = app(CurrencyService::class);
        return $currencyService->getCurrencySymbol($currencyCode);
    }

    /**
     * Get currency decimal places
     * @param string|null $currencyCode
     * @return int
     */
    public function getCurrencyDecimalPlaces($currencyCode = null): int
    {
        $currencyService = app(CurrencyService::class);
        return $currencyService->getCurrencyDecimalPlaces($currencyCode);
    }

    /**
     * Parse currency string back to number
     * @param string $currencyString
     * @return float
     */
    public function parseCurrency($currencyString): float
    {
        $currencyService = app(CurrencyService::class);
        return $currencyService->parseCurrency($currencyString);
    }

    /**
     * Validate currency code format
     * @param string $currencyCode
     * @return bool
     */
    public function isValidCurrencyCode($currencyCode): bool
    {
        $currencyService = app(CurrencyService::class);
        return $currencyService->isValidCurrencyCode($currencyCode);
    }
} 