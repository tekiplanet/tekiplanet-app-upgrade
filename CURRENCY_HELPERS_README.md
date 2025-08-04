# Currency Helpers & User Currency System

This document outlines the comprehensive currency formatting, conversion, and user currency management system implemented in our project.

## 🎯 Current Implementation Status

### ✅ **Completed Features:**
- [x] Backend currency service with formatting and symbol management
- [x] Frontend currency helpers (async and sync)
- [x] User currency selection during onboarding
- [x] Database structure for user currency preferences
- [x] Mobile-responsive onboarding flow
- [x] ISO3166 country integration
- [x] API endpoints for currency operations
- [x] Currency symbol caching for performance
- [x] **User-specific currency symbol display** ← **NEW**
- [x] **Efficient currency symbol fetching** ← **NEW**

### 🔄 **Pending Implementation:**
- [ ] Exchange rate API integration for actual conversions
- [ ] Currency conversion for payments and purchases
- [ ] Admin currency management interface
- [ ] Real-time rate updates
- [ ] Advanced currency conversion features

---

## 🚀 Quick Start

### Frontend Usage

```typescript
import { formatAmount, formatAmountInUserCurrency, getCurrencySymbol } from '@/lib/currency';

// Format amount with user's currency symbol (no conversion)
const formatted = await formatAmountInUserCurrency(1000, 'GBP');
// Result: "£1,000.00"

// Get currency symbol
const symbol = await getCurrencySymbol('USD');
// Result: "$"

// Quick currency formatting
const display = await formatAmount(1000, 'EUR');
// Result: "€1,000.00"
```

### Backend Usage

```php
use App\Traits\HasCurrencyFormatting;

class YourController extends Controller
{
    use HasCurrencyFormatting;
    
    public function example()
    {
        // Format amount with currency symbol
        $formatted = $this->formatAmount(1000, 'USD');
        
        // Get currency symbol
        $symbol = $this->getCurrencySymbol('GBP');
        
        // Convert between currencies (for payments/purchases)
        $converted = $this->convertAmount(1000, 'USD', 'EUR');
    }
}
```

---

## 📁 File Structure

```
backend/
├── app/
│   ├── Services/CurrencyService.php          # Core currency logic
│   ├── Traits/HasCurrencyFormatting.php      # Reusable trait
│   ├── Http/Controllers/
│   │   ├── OnboardingController.php          # User currency selection
│   │   └── Api/CurrencyController.php        # Currency API endpoints
│   └── Models/User.php                       # User currency fields
├── database/migrations/
│   └── add_country_currency_to_users_table.php
└── routes/api.php                            # Currency routes

frontend/
├── src/
│   ├── lib/currency.ts                       # Frontend currency helpers
│   ├── services/onboardingService.ts         # Onboarding API calls
│   ├── components/onboarding/
│   │   └── CountryCurrencyStep.tsx          # Currency selection UI
│   ├── components/wallet/
│   │   ├── WalletDashboard.tsx              # Main wallet dashboard
│   │   └── TransactionHistory.tsx           # Transaction history component
│   └── pages/
│       ├── Onboarding.tsx                   # Mobile-responsive flow
│       └── TransactionHistory.tsx           # Dedicated transaction page
```

---

## 🎯 Current Approach

### Display Strategy
Our currency system uses a **symbol-based display approach** where:
- Amounts are displayed with the user's preferred currency symbol
- No conversion is performed for display purposes
- Users see consistent values regardless of exchange rate fluctuations
- Conversion is only used when actual currency exchange is needed (payments, purchases)

### User-Specific Currency Implementation
**NEW:** We now fetch and display the user's specific currency symbol instead of using general site settings:

```typescript
// ✅ CORRECT: User-specific currency symbol
const CurrencyDisplay = ({ amount, userCurrencyCode, currencySymbol }) => {
  const formattedAmount = formatAmountInUserCurrencySync(
    amount, 
    userCurrencyCode, 
    currencySymbol // User's actual currency symbol
  );
  return <span>{formattedAmount}</span>;
};

// ❌ WRONG: Using general site settings
const formattedAmount = formatAmountInUserCurrencySync(
  amount, 
  userCurrencyCode, 
  settings?.currency_symbol // General site currency, not user's
);
```

### Benefits
- **Consistent User Experience**: Amounts don't change unexpectedly
- **Performance**: No conversion API calls for display
- **Simplicity**: Clear and predictable behavior
- **Flexibility**: Easy to implement conversion when needed
- **User-Specific**: Each user sees their own currency symbol

---

## 🔧 Backend Implementation

### CurrencyService

The core service providing currency formatting, symbol management, and conversion capabilities:

```php
use App\Services\CurrencyService;

$currencyService = new CurrencyService();

// Format amount with currency symbol
$formatted = $currencyService->formatAmount(1000, 'USD');
// Result: "$1,000.00"

// Convert between currencies
$converted = $currencyService->convertAmount(1000, 'USD', 'EUR');
// Result: 850.00

// Get currency details
$currency = $currencyService->getCurrency('USD');
$symbol = $currencyService->getCurrencySymbol('USD');
```

### HasCurrencyFormatting Trait

Add to any model or controller for easy currency access:

```php
use App\Traits\HasCurrencyFormatting;

class YourModel extends Model
{
    use HasCurrencyFormatting;
    
    // Now you have access to all currency methods
    public function getFormattedPrice()
    {
        return $this->formatAmount($this->price, $this->currency_code);
    }
}
```

### API Endpoints

```bash
# Currency conversion (for payments/purchases)
POST /api/currency/convert
{
    "amount": 1000,
    "from_currency": "USD",
    "to_currency": "EUR"
}

# Batch currency conversion
POST /api/currency/batch-convert
{
    "amounts": [1000, 2000, 3000],
    "from_currency": "USD",
    "to_currency": "EUR"
}

# Amount formatting
POST /api/currency/format
{
    "amount": 1000,
    "currency_code": "USD"
}

# Get currency symbol - USER SPECIFIC
GET /api/currency/{userCurrencyCode}/symbol

# Get all active currencies
GET /api/currencies

# Get specific currency
GET /api/currencies/USD
```

---

## 🎨 Frontend Implementation

### Currency Helpers

```typescript
import { 
    formatAmount, 
    formatAmountInUserCurrency,
    formatAmountInUserCurrencySync,
    convertAmount,
    getCurrencySymbol 
} from '@/lib/currency';

// Format amount with user's currency symbol (no conversion)
const formatted = await formatAmountInUserCurrency(1000, 'GBP');
// Result: "£1,000.00"

// Sync formatting (fallback)
const formattedSync = formatAmountInUserCurrencySync(1000, 'GBP', '£');

// Currency conversion (for payments/purchases only)
const converted = await convertAmount(1000, 'USD', 'EUR');

// Get currency symbol
const symbol = await getCurrencySymbol('USD');
// Result: "$"
```

### User Currency Integration

The system automatically uses the user's selected currency for display:

```typescript
import { useAuthStore } from '@/store/useAuthStore';

const { user } = useAuthStore();

// Format amount with user's currency symbol (no conversion)
const formatted = await formatAmountInUserCurrency(1000, user.currency_code);

// Display user's currency info
console.log(`User currency: ${user.currency_code} (${user.country_name})`);
```

### User-Specific Currency Display Component

**NEW:** Efficient currency symbol fetching and caching:

```typescript
// ✅ RECOMMENDED: Component-level currency symbol caching
const WalletDashboard = () => {
  const [currencySymbol, setCurrencySymbol] = useState<string>('$');
  
  // Fetch user's currency symbol once
  useEffect(() => {
    const fetchUserCurrencySymbol = async () => {
      if (!user?.currency_code) return;
      
      try {
        const response = await fetch(`/api/currency/${user.currency_code}/symbol`);
        const data = await response.json();
        setCurrencySymbol(data.data?.symbol || '$');
      } catch (error) {
        console.error('Error fetching currency symbol:', error);
        setCurrencySymbol('$');
      }
    };
    
    fetchUserCurrencySymbol();
  }, [user?.currency_code]);

  // Use cached symbol for all transactions
  return (
    <CurrencyDisplay 
      amount={transaction.amount}
      userCurrencyCode={user?.currency_code}
      currencySymbol={currencySymbol} // User's specific symbol
    />
  );
};
```

---

## ⚡ Performance Optimizations

### Symbol Caching
- **Component-level caching**: Currency symbols are cached to avoid repeated API calls
- **Single API call**: Fetch user's currency symbol once per component
- **Automatic cache management**: Symbols are fetched once and reused
- **Fallback handling**: Graceful degradation when cache is unavailable

### Display Optimization
- **No conversion overhead**: Display formatting doesn't require conversion API calls
- **Immediate rendering**: Amounts are displayed instantly with cached symbols
- **Reduced API load**: Significantly fewer API calls for currency operations

---

## 👤 User Currency Selection

### Onboarding Flow

Users select their country and currency during onboarding:

1. **Step 1:** Account Type Selection
2. **Step 2:** Profile Information
3. **Step 3:** **Country & Currency Selection** ← New Step

### Country Data

- Uses ISO3166 package for complete country list
- 249+ countries and territories
- Auto-suggests default currency for each country
- Mobile-responsive selection interface

### Currency Data

- Fetches active currencies from database
- Shows currency symbols and examples
- Validates against available currencies

---

## 🗄️ Database Schema

### Users Table
```sql
ALTER TABLE users ADD COLUMN country_code VARCHAR(2) NULL;
ALTER TABLE users ADD COLUMN country_name VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN currency_code VARCHAR(3) NULL;
```

### Currencies Table
```sql
-- Already exists in your system
-- Contains: code, name, symbol, rate, is_base, is_active, etc.
```

---

## 🔄 Future Implementation Roadmap

### Phase 1: Exchange Rate Integration
- [ ] Integrate with exchange rate API (e.g., Fixer.io, ExchangeRate-API)
- [ ] Implement rate caching and updates
- [ ] Add rate update scheduling

### Phase 2: Application Integration
- [ ] Update product pricing display
- [ ] Implement currency in transactions
- [ ] Add currency to invoices and receipts
- [ ] Update wallet balance display

### Phase 3: Admin Management
- [ ] Admin interface for currency management
- [ ] Exchange rate monitoring
- [ ] Currency activation/deactivation

### Phase 4: Advanced Features
- [ ] Real-time rate updates
- [ ] Currency conversion in cart
- [ ] Multi-currency support for businesses
- [ ] Currency preference per transaction

---

## 🛠️ Configuration

### Environment Variables
```env
# Exchange Rate API (for future implementation)
EXCHANGE_RATE_API_KEY=your_api_key
EXCHANGE_RATE_API_URL=https://api.exchangerate-api.com/v4/latest/

# Default currency
DEFAULT_CURRENCY=USD
```

### Currency Settings
```php
// In config/currency.php (create if needed)
return [
    'default' => env('DEFAULT_CURRENCY', 'USD'),
    'api_key' => env('EXCHANGE_RATE_API_KEY'),
    'cache_duration' => 3600, // 1 hour
];
```

---

## 🧪 Testing

### Backend Tests
```bash
# Test currency service
php artisan test --filter=CurrencyServiceTest

# Test onboarding flow
php artisan test --filter=OnboardingTest
```

### Frontend Tests
```bash
# Test currency helpers
npm test -- --testPathPattern=currency

# Test onboarding components
npm test -- --testPathPattern=onboarding
```

---

## 📚 Best Practices

### 1. Always Use User's Currency
```typescript
// ✅ Good
const formatted = await formatAmount(price, user.currency_code);

// ❌ Bad
const formatted = formatAmount(price, 'USD'); // Hardcoded
```

### 2. Use User-Specific Currency Symbol
```typescript
// ✅ Good: Fetch user's currency symbol
const symbol = await getCurrencySymbol(user.currency_code);
const formatted = formatAmountInUserCurrencySync(amount, user.currency_code, symbol);

// ❌ Bad: Use general site settings
const formatted = formatAmountInUserCurrencySync(amount, user.currency_code, settings?.currency_symbol);
```

### 3. Cache Currency Symbols Efficiently
```typescript
// ✅ Good: Component-level caching
const [currencySymbol, setCurrencySymbol] = useState('$');
useEffect(() => {
  fetchUserCurrencySymbol();
}, [user?.currency_code]);

// ❌ Bad: Individual API calls per transaction
const CurrencyDisplay = ({ amount }) => {
  const [symbol, setSymbol] = useState('$');
  useEffect(() => {
    fetchCurrencySymbol(); // Called for each transaction
  }, []);
};
```

### 4. Handle Currency Errors
```typescript
try {
    const formatted = await formatAmount(price, currency);
} catch (error) {
    // Fallback to default currency
    const formatted = formatAmountSync(price, 'USD', '$');
}
```

### 5. Cache Exchange Rates
```php
// Use caching for exchange rates
$rate = Cache::remember("exchange_rate_{$from}_{$to}", 3600, function() {
    return $this->fetchExchangeRate($from, $to);
});
```

### 6. Validate Currency Codes
```php
// Always validate currency codes
if (!$this->isValidCurrencyCode($currency)) {
    throw new InvalidArgumentException("Invalid currency code: {$currency}");
}
```

---

## 🐛 Troubleshooting

### Common Issues

1. **Currency not found**
   - Check if currency is active in database
   - Verify currency code format (3 characters)

2. **Exchange rate errors**
   - Check API key configuration
   - Verify API endpoint availability

3. **Formatting issues**
   - Ensure proper decimal places for currency
   - Check currency symbol configuration

4. **User currency symbol not displaying correctly**
   - Verify user has `currency_code` set in database
   - Check API endpoint `/currency/{code}/symbol` is working
   - Ensure component is using user's currency symbol, not site settings

### Debug Commands
```bash
# Check currency data
php artisan tinker
>>> app(App\Services\CurrencyService::class)->getActiveCurrencies()

# Test conversion
php artisan tinker
>>> app(App\Services\CurrencyService::class)->convertAmount(100, 'USD', 'EUR')

# Check user currency
php artisan tinker
>>> App\Models\User::find(1)->currency_code
```

### User Currency Debugging
```typescript
// Check user's currency in browser console
console.log('User currency:', user?.currency_code);

// Test currency symbol API
const response = await fetch('/api/currency/USD/symbol');
const data = await response.json();
console.log('Currency symbol response:', data);
```

---

## 📞 Support

For currency-related issues:
1. Check this README first
2. Review the implementation examples
3. Check the logs for detailed error messages
4. Test with the provided debugging commands
5. **For user currency issues**: Verify user has correct `currency_code` and API endpoint is working

---

*Last updated: August 2024*
*Version: 2.0.0* 