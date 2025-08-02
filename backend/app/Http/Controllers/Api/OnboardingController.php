<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use League\ISO3166\ISO3166;

class OnboardingController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Get countries list for onboarding
     * @return JsonResponse
     */
    public function getCountries(): JsonResponse
    {
        try {
            $iso3166 = new ISO3166();
            $allCountries = $iso3166->all();
            
            // Map ISO3166 data to our format and add currency codes
            $countries = [];
            foreach ($allCountries as $country) {
                $countries[] = [
                    'code' => $country['alpha2'],
                    'name' => $country['name'],
                    'currency_code' => $this->getCurrencyForCountry($country['alpha2'])
                ];
            }

            // Sort countries by name
            usort($countries, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            return response()->json([
                'success' => true,
                'data' => $countries
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch countries',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get currency code for a country
     */
    private function getCurrencyForCountry(string $countryCode): string
    {
        // Common currency mappings
        $currencyMap = [
            'US' => 'USD', 'GB' => 'GBP', 'CA' => 'CAD', 'AU' => 'AUD',
            'DE' => 'EUR', 'FR' => 'EUR', 'IT' => 'EUR', 'ES' => 'EUR',
            'NL' => 'EUR', 'BE' => 'EUR', 'AT' => 'EUR', 'CH' => 'CHF',
            'SE' => 'SEK', 'NO' => 'NOK', 'DK' => 'DKK', 'FI' => 'EUR',
            'PL' => 'PLN', 'CZ' => 'CZK', 'HU' => 'HUF', 'RO' => 'RON',
            'BG' => 'BGN', 'HR' => 'EUR', 'SI' => 'EUR', 'SK' => 'EUR',
            'LT' => 'EUR', 'LV' => 'EUR', 'EE' => 'EUR', 'IE' => 'EUR',
            'PT' => 'EUR', 'GR' => 'EUR', 'CY' => 'EUR', 'MT' => 'EUR',
            'LU' => 'EUR', 'IS' => 'ISK', 'JP' => 'JPY', 'KR' => 'KRW',
            'CN' => 'CNY', 'IN' => 'INR', 'BR' => 'BRL', 'MX' => 'MXN',
            'AR' => 'ARS', 'CL' => 'CLP', 'CO' => 'COP', 'PE' => 'PEN',
            'VE' => 'VES', 'EC' => 'USD', 'UY' => 'UYU', 'PY' => 'PYG',
            'BO' => 'BOB', 'GY' => 'GYD', 'SR' => 'SRD', 'FK' => 'FKP',
            'GF' => 'EUR', 'ZA' => 'ZAR', 'EG' => 'EGP', 'MA' => 'MAD',
            'TN' => 'TND', 'DZ' => 'DZD', 'LY' => 'LYD', 'SD' => 'SDG',
            'ET' => 'ETB', 'KE' => 'KES', 'TZ' => 'TZS', 'UG' => 'UGX',
            'RW' => 'RWF', 'BI' => 'BIF', 'SS' => 'SSP', 'DJ' => 'DJF',
            'SO' => 'SOS', 'ER' => 'ERN', 'KM' => 'KMF', 'MG' => 'MGA',
            'MU' => 'MUR', 'SC' => 'SCR', 'MV' => 'MVR', 'LK' => 'LKR',
            'BD' => 'BDT', 'PK' => 'PKR', 'AF' => 'AFN', 'IR' => 'IRR',
            'IQ' => 'IQD', 'SA' => 'SAR', 'AE' => 'AED', 'QA' => 'QAR',
            'KW' => 'KWD', 'BH' => 'BHD', 'OM' => 'OMR', 'YE' => 'YER',
            'JO' => 'JOD', 'LB' => 'LBP', 'SY' => 'SYP', 'IL' => 'ILS',
            'PS' => 'ILS', 'TR' => 'TRY', 'GE' => 'GEL', 'AM' => 'AMD',
            'AZ' => 'AZN', 'RU' => 'RUB', 'BY' => 'BYN', 'UA' => 'UAH',
            'MD' => 'MDL', 'RS' => 'RSD', 'ME' => 'EUR', 'BA' => 'BAM',
            'MK' => 'MKD', 'AL' => 'ALL', 'XK' => 'EUR', 'TH' => 'THB',
            'VN' => 'VND', 'LA' => 'LAK', 'KH' => 'KHR', 'MM' => 'MMK',
            'MY' => 'MYR', 'SG' => 'SGD', 'ID' => 'IDR', 'PH' => 'PHP',
            'TW' => 'TWD', 'HK' => 'HKD', 'MO' => 'MOP', 'MN' => 'MNT',
            'KZ' => 'KZT', 'KG' => 'KGS', 'TJ' => 'TJS', 'TM' => 'TMT',
            'UZ' => 'UZS', 'NZ' => 'NZD', 'FJ' => 'FJD', 'PG' => 'PGK',
            'SB' => 'SBD', 'VU' => 'VUV', 'NC' => 'XPF', 'PF' => 'XPF',
            'TO' => 'TOP', 'WS' => 'WST', 'KI' => 'AUD', 'TV' => 'AUD',
            'NR' => 'AUD', 'PW' => 'USD', 'MH' => 'USD', 'FM' => 'USD',
            'CK' => 'NZD', 'NU' => 'NZD', 'TK' => 'NZD', 'AS' => 'USD',
            'GU' => 'USD', 'MP' => 'USD', 'PR' => 'USD', 'VI' => 'USD',
            'AI' => 'XCD', 'AG' => 'XCD', 'AW' => 'AWG', 'BS' => 'BSD',
            'BB' => 'BBD', 'BZ' => 'BZD', 'BM' => 'BMD', 'VG' => 'USD',
            'KY' => 'KYD', 'CR' => 'CRC', 'CU' => 'CUP', 'CW' => 'ANG',
            'DM' => 'XCD', 'DO' => 'DOP', 'SV' => 'USD', 'GD' => 'XCD',
            'GT' => 'GTQ', 'HT' => 'HTG', 'HN' => 'HNL', 'JM' => 'JMD',
            'NI' => 'NIO', 'PA' => 'PAB', 'KN' => 'XCD', 'LC' => 'XCD',
            'VC' => 'XCD', 'TT' => 'TTD', 'TC' => 'USD', 'NG' => 'NGN',
        ];

        return $currencyMap[$countryCode] ?? 'USD'; // Default to USD if not found
    }

    /**
     * Get currencies list for onboarding
     * @return JsonResponse
     */
    public function getCurrencies(): JsonResponse
    {
        try {
            $currencies = $this->currencyService->getActiveCurrencies();
            
            return response()->json([
                'success' => true,
                'data' => $currencies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch currencies',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 