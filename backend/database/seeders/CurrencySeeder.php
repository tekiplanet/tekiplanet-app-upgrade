<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run()
    {
        // Create NGN as the base currency
        Currency::create([
            'name' => 'Nigerian Naira',
            'code' => 'NGN',
            'symbol' => '₦',
            'rate' => 1.000000,
            'is_base' => true,
            'is_active' => true,
            'decimal_places' => 2,
            'position' => 1
        ]);

        // Create USD
        Currency::create([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'rate' => 0.001234, // Example rate: 1 USD = 810 NGN
            'is_base' => false,
            'is_active' => true,
            'decimal_places' => 2,
            'position' => 2
        ]);

        // Create EUR
        Currency::create([
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
            'rate' => 0.001111, // Example rate: 1 EUR = 900 NGN
            'is_base' => false,
            'is_active' => true,
            'decimal_places' => 2,
            'position' => 3
        ]);

        // Create GBP
        Currency::create([
            'name' => 'British Pound',
            'code' => 'GBP',
            'symbol' => '£',
            'rate' => 0.001299, // Example rate: 1 GBP = 770 NGN
            'is_base' => false,
            'is_active' => true,
            'decimal_places' => 2,
            'position' => 4
        ]);

        // Create CAD
        Currency::create([
            'name' => 'Canadian Dollar',
            'code' => 'CAD',
            'symbol' => 'C$',
            'rate' => 0.001667, // Example rate: 1 CAD = 600 NGN
            'is_base' => false,
            'is_active' => true,
            'decimal_places' => 2,
            'position' => 5
        ]);

        // Create AUD
        Currency::create([
            'name' => 'Australian Dollar',
            'code' => 'AUD',
            'symbol' => 'A$',
            'rate' => 0.001818, // Example rate: 1 AUD = 550 NGN
            'is_base' => false,
            'is_active' => true,
            'decimal_places' => 2,
            'position' => 6
        ]);

        // Create JPY
        Currency::create([
            'name' => 'Japanese Yen',
            'code' => 'JPY',
            'symbol' => '¥',
            'rate' => 0.008333, // Example rate: 1 JPY = 120 NGN
            'is_base' => false,
            'is_active' => true,
            'decimal_places' => 0,
            'position' => 7
        ]);

        // Create CHF
        Currency::create([
            'name' => 'Swiss Franc',
            'code' => 'CHF',
            'symbol' => 'CHF',
            'rate' => 0.001111, // Example rate: 1 CHF = 900 NGN
            'is_base' => false,
            'is_active' => true,
            'decimal_places' => 2,
            'position' => 8
        ]);

        // Create CNY
        Currency::create([
            'name' => 'Chinese Yuan',
            'code' => 'CNY',
            'symbol' => '¥',
            'rate' => 0.008889, // Example rate: 1 CNY = 112.5 NGN
            'is_base' => false,
            'is_active' => true,
            'decimal_places' => 2,
            'position' => 9
        ]);

        // Create INR
        Currency::create([
            'name' => 'Indian Rupee',
            'code' => 'INR',
            'symbol' => '₹',
            'rate' => 0.014815, // Example rate: 1 INR = 67.5 NGN
            'is_base' => false,
            'is_active' => true,
            'decimal_places' => 2,
            'position' => 10
        ]);

        // Create ZAR
        Currency::create([
            'name' => 'South African Rand',
            'code' => 'ZAR',
            'symbol' => 'R',
            'rate' => 0.066667, // Example rate: 1 ZAR = 15 NGN
            'is_base' => false,
            'is_active' => true,
            'decimal_places' => 2,
            'position' => 11
        ]);
    }
} 