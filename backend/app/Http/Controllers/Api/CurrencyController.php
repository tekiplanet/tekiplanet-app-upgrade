<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Convert amount from one currency to another
     * @param Request $request
     * @return JsonResponse
     */
    public function convert(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'from_currency' => 'required|string|size:3',
            'to_currency' => 'required|string|size:3',
        ]);

        try {
            $amount = (float) $request->amount;
            $fromCurrency = strtoupper($request->from_currency);
            $toCurrency = strtoupper($request->to_currency);

            // Add debugging
            \Log::info('Currency conversion request', [
                'amount' => $amount,
                'from_currency' => $fromCurrency,
                'to_currency' => $toCurrency
            ]);

            $convertedAmount = $this->currencyService->convertAmount(
                $amount,
                $fromCurrency,
                $toCurrency
            );

            // Add debugging
            \Log::info('Currency conversion result', [
                'original_amount' => $amount,
                'from_currency' => $fromCurrency,
                'to_currency' => $toCurrency,
                'converted_amount' => $convertedAmount
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'original_amount' => $amount,
                    'from_currency' => $fromCurrency,
                    'to_currency' => $toCurrency,
                    'converted_amount' => $convertedAmount,
                    'formatted_amount' => $this->currencyService->formatCurrency($convertedAmount, $toCurrency)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Currency conversion failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Format amount with currency symbol
     * @param Request $request
     * @return JsonResponse
     */
    public function format(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric',
            'currency_code' => 'string|size:3',
            'show_symbol' => 'boolean',
            'show_code' => 'boolean',
            'decimal_places' => 'integer|min:0|max:6',
        ]);

        try {
            $amount = (float) $request->amount;
            $currencyCode = $request->currency_code ? strtoupper($request->currency_code) : null;
            $options = [
                'show_symbol' => $request->boolean('show_symbol', true),
                'show_code' => $request->boolean('show_code', false),
                'decimal_places' => $request->integer('decimal_places'),
            ];

            // Remove null values
            $options = array_filter($options, function ($value) {
                return $value !== null;
            });

            $formattedAmount = $this->currencyService->formatAmount($amount, $currencyCode, $options);

            return response()->json([
                'success' => true,
                'data' => [
                    'amount' => $amount,
                    'currency_code' => $currencyCode,
                    'formatted_amount' => $formattedAmount,
                    'options' => $options
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Currency formatting failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get all available currencies
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $currencies = $this->currencyService->getActiveCurrencies();
            $baseCurrency = $this->currencyService->getBaseCurrency();

            return response()->json([
                'success' => true,
                'data' => [
                    'currencies' => $currencies,
                    'base_currency' => $baseCurrency,
                    'total' => $currencies->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch currencies',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get currency details by code
     * @param string $code
     * @return JsonResponse
     */
    public function show(string $code): JsonResponse
    {
        try {
            $currency = $this->currencyService->getCurrency(strtoupper($code));

            if (!$currency) {
                return response()->json([
                    'success' => false,
                    'message' => 'Currency not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $currency
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch currency',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get currency symbol by code
     * @param string $code
     * @return JsonResponse
     */
    public function symbol(string $code): JsonResponse
    {
        try {
            $symbol = $this->currencyService->getCurrencySymbol(strtoupper($code));

            return response()->json([
                'success' => true,
                'data' => [
                    'currency_code' => strtoupper($code),
                    'symbol' => $symbol
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch currency symbol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update exchange rates
     * @return JsonResponse
     */
    public function updateRates(): JsonResponse
    {
        try {
            $success = $this->currencyService->updateExchangeRates();

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exchange rates updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update exchange rates'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update exchange rates',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 