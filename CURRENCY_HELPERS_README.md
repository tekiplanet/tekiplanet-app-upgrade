# Currency Formatting and Conversion Helpers

This document describes the comprehensive currency formatting and conversion system implemented for the TekiPlanet project.

## Overview

The system provides both frontend (TypeScript/JavaScript) and backend (PHP/Laravel) helpers for:
- Formatting amounts with proper currency symbols and decimal places
- Converting amounts between different currencies
- Managing currency settings and exchange rates

## Frontend Usage (TypeScript/JavaScript)

### Import the helpers

```typescript
import { 
  formatCurrency, 
  formatCurrencySync, 
  formatAmount, 
  convertAmount,
  convertToBase,
  convertFromBase,
  getCurrencySymbol,
  parseCurrency 
} from '@/lib/currency';
```

### Basic Formatting

```typescript
// Synchronous formatting (immediate use)
const formatted = formatCurrencySync(1234.56, 'USD', '$');
// Result: "$1,234.56"

// Asynchronous formatting (with settings from server)
const formatted = await formatCurrency(1234.56, 'USD');
// Result: "$1,234.56" (uses settings from server)

// Format without symbol
const numberOnly = await formatNumber(1234.56, 'USD');
// Result: "1,234.56"
```

### Advanced Formatting

```typescript
// Custom formatting options
const formatted = await formatAmount(1234.56, 'USD', {
  showSymbol: true,
  showCode: true,
  decimalPlaces: 2,
  locale: 'en-US'
});
// Result: "$1,234.56 USD"
```

### Currency Conversion

```typescript
// Convert between currencies
const converted = await convertAmount(100, 'USD', 'EUR');
// Result: 85.50 (example rate)

// Convert to base currency
const baseAmount = await convertToBase(100, 'EUR');
// Result: 117.65 (converted to base currency)

// Convert from base currency
const targetAmount = await convertFromBase(100, 'EUR');
// Result: 85.50 (converted from base currency)
```

### Utility Functions

```typescript
// Get currency symbol
const symbol = await getCurrencySymbol('USD');
// Result: "$"

// Parse formatted currency back to number
const amount = parseCurrency('$1,234.56');
// Result: 1234.56

// Validate currency code
const isValid = isValidCurrencyCode('USD');
// Result: true
```

## Backend Usage (PHP/Laravel)

### Using the CurrencyService

```php
use App\Services\CurrencyService;

class SomeController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function someMethod()
    {
        // Format currency
        $formatted = $this->currencyService->formatCurrency(1234.56, 'USD');
        // Result: "$1,234.56"

        // Convert currency
        $converted = $this->currencyService->convertAmount(100, 'USD', 'EUR');
        // Result: 85.50

        // Get currency symbol
        $symbol = $this->currencyService->getCurrencySymbol('USD');
        // Result: "$"
    }
}
```

### Using the Trait

```php
use App\Traits\HasCurrencyFormatting;

class Transaction extends Model
{
    use HasCurrencyFormatting;

    public function getFormattedAmountAttribute()
    {
        return $this->formatCurrency($this->amount, $this->currency_code);
    }

    public function convertToBase()
    {
        return $this->convertToBase($this->amount, $this->currency_code);
    }
}
```

### API Endpoints

The system provides several API endpoints for currency operations:

#### Convert Currency
```http
POST /api/currency/convert
Content-Type: application/json

{
    "amount": 100,
    "from_currency": "USD",
    "to_currency": "EUR"
}
```

Response:
```json
{
    "success": true,
    "data": {
        "original_amount": 100,
        "from_currency": "USD",
        "to_currency": "EUR",
        "converted_amount": 85.50,
        "formatted_amount": "€85.50"
    }
}
```

#### Format Amount
```http
GET /api/currency/format?amount=1234.56&currency_code=USD&show_symbol=true
```

Response:
```json
{
    "success": true,
    "data": {
        "amount": 1234.56,
        "currency_code": "USD",
        "formatted_amount": "$1,234.56",
        "options": {
            "show_symbol": true
        }
    }
}
```

#### Get All Currencies
```http
GET /api/currencies
```

Response:
```json
{
    "success": true,
    "data": {
        "currencies": [
            {
                "id": "1",
                "name": "US Dollar",
                "code": "USD",
                "symbol": "$",
                "rate": 1.0,
                "is_base": true,
                "is_active": true,
                "decimal_places": 2,
                "position": 1
            }
        ],
        "base_currency": {
            "id": "1",
            "name": "US Dollar",
            "code": "USD",
            "symbol": "$",
            "rate": 1.0,
            "is_base": true,
            "is_active": true,
            "decimal_places": 2,
            "position": 1
        },
        "total": 1
    }
}
```

## Configuration

### Environment Variables

```env
# Exchange rate API key (optional)
EXCHANGE_RATE_API_KEY=your_api_key_here
```

### Database Migration

The system uses the existing `currencies` table with the following structure:

```sql
CREATE TABLE currencies (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(3) NOT NULL UNIQUE,
    symbol VARCHAR(10) NOT NULL,
    rate DECIMAL(10,6) NOT NULL DEFAULT 1.000000,
    is_base BOOLEAN NOT NULL DEFAULT FALSE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    decimal_places INTEGER NOT NULL DEFAULT 2,
    position INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);
```

## Features

### 1. Proper Decimal Places
Each currency can have its own decimal places (e.g., JPY typically has 0, USD has 2).

### 2. Currency Symbols
Proper currency symbols are used (e.g., $, €, ₦, ¥).

### 3. Exchange Rate Management
- Admin can manage exchange rates through the admin panel
- Automatic exchange rate updates from external API (optional)
- Caching of exchange rates for performance

### 4. Base Currency System
- One currency is designated as the base currency
- All conversions go through the base currency
- Base currency cannot be deleted or deactivated

### 5. Fallback Handling
- Graceful fallbacks when currency data is unavailable
- Error logging for debugging
- Returns original amount if conversion fails

## Migration Guide

### From Old Formatting

Replace old formatting calls:

```typescript
// Old way
const formatted = new Intl.NumberFormat('en-US', {
  style: 'currency',
  currency: 'USD',
}).format(amount);

// New way
const formatted = await formatCurrency(amount, 'USD');
```

### From Old Conversion

Replace old conversion calls:

```php
// Old way
$converted = $this->currencyService->convertToNGN($amount, $fromCurrency);

// New way
$converted = $this->currencyService->convertAmount($amount, $fromCurrency, 'NGN');
```

## Best Practices

1. **Use synchronous functions for immediate display** when you already have the currency symbol
2. **Use asynchronous functions** when you need to fetch settings from the server
3. **Always handle errors gracefully** - the helpers will return fallback values
4. **Cache currency data** on the frontend to avoid repeated API calls
5. **Use the trait** in models that deal with currency amounts
6. **Validate currency codes** before processing

## Troubleshooting

### Common Issues

1. **Currency not found**: Check if the currency is active in the admin panel
2. **Conversion fails**: Verify exchange rates are set correctly
3. **Formatting issues**: Ensure decimal places are configured properly
4. **API errors**: Check if the exchange rate API key is configured (optional)

### Debugging

Enable logging to see detailed error messages:

```php
// In your .env file
LOG_LEVEL=debug
```

The system logs all currency-related errors for debugging purposes. 