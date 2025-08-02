import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { 
  Globe, 
  Currency, 
  Search, 
  Check, 
  Sparkles, 
  MapPin, 
  DollarSign,
  ChevronDown,
  ChevronUp
} from 'lucide-react';
import { onboardingService } from '@/services/onboardingService';
import { formatCurrencySync } from '@/lib/currency';

interface CountryCurrencyStepProps {
  onComplete: (data: { country_code: string; country_name: string; currency_code: string }) => void;
}

interface Country {
  code: string;
  name: string;
  currency_code: string;
}

interface Currency {
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

const CountryCurrencyStep: React.FC<CountryCurrencyStepProps> = ({ onComplete }) => {
  const [countries, setCountries] = useState<Country[]>([]);
  const [currencies, setCurrencies] = useState<Currency[]>([]);
  const [selectedCountry, setSelectedCountry] = useState<Country | null>(null);
  const [selectedCurrency, setSelectedCurrency] = useState<Currency | null>(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [showCountryDropdown, setShowCountryDropdown] = useState(false);
  const [showCurrencyDropdown, setShowCurrencyDropdown] = useState(false);
  const [loading, setLoading] = useState(false);
  const [dataLoading, setDataLoading] = useState(true);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setDataLoading(true);
      const [countriesData, currenciesData] = await Promise.all([
        onboardingService.getCountries(),
        onboardingService.getCurrencies()
      ]);
      setCountries(countriesData);
      setCurrencies(currenciesData);
    } catch (error) {
      console.error('Failed to load countries/currencies:', error);
    } finally {
      setDataLoading(false);
    }
  };

  const filteredCountries = countries.filter(country =>
    country.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    country.code.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const handleCountrySelect = (country: Country) => {
    setSelectedCountry(country);
    setShowCountryDropdown(false);
    setSearchTerm('');
    
    // Auto-select the default currency for the country if available
    const defaultCurrency = currencies.find(c => c.code === country.currency_code);
    if (defaultCurrency) {
      setSelectedCurrency(defaultCurrency);
    }
  };

  const handleCurrencySelect = (currency: Currency) => {
    setSelectedCurrency(currency);
    setShowCurrencyDropdown(false);
  };

  const handleContinue = async () => {
    if (!selectedCountry || !selectedCurrency) return;
    
    setLoading(true);
    try {
      await onComplete({
        country_code: selectedCountry.code,
        country_name: selectedCountry.name,
        currency_code: selectedCurrency.code
      });
    } catch (error) {
      setLoading(false);
    }
  };

  if (dataLoading) {
    return (
      <div className="max-w-md mx-auto px-2">
        <div className="text-center">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-4" />
          <p className="text-muted-foreground">Loading countries and currencies...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-md mx-auto px-2">
      {/* Enhanced Header */}
      <div className="text-center mb-8">
        <motion.div
          initial={{ opacity: 0, scale: 0.9 }}
          animate={{ opacity: 1, scale: 1 }}
          className="mb-4"
        >
          <div className="w-12 h-12 mx-auto mb-3 rounded-full bg-gradient-to-r from-primary to-primary/80 flex items-center justify-center">
            <Globe className="h-6 w-6 text-white" />
          </div>
        </motion.div>
        
        <motion.h1
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="text-2xl font-bold text-foreground mb-2 bg-gradient-to-r from-foreground to-foreground/80 bg-clip-text"
        >
          Set Your Location
        </motion.h1>
        <motion.p
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.1 }}
          className="text-base text-muted-foreground leading-relaxed"
        >
          Choose your country and preferred currency for a personalized experience
        </motion.p>
      </div>

      {/* Country Selection */}
      <motion.div
        initial={{ opacity: 0, y: 30 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.2 }}
        className="mb-6"
      >
        <label className="block text-sm font-medium text-foreground mb-3">
          <MapPin className="h-4 w-4 inline mr-2 text-primary" />
          Select Your Country
        </label>
        
        <div className="relative">
          <div
            className="w-full p-4 border border-input bg-background rounded-lg cursor-pointer hover:border-primary/50 transition-colors"
            onClick={() => setShowCountryDropdown(!showCountryDropdown)}
          >
            <div className="flex items-center justify-between">
              <div className="flex items-center space-x-3">
                {selectedCountry ? (
                  <>
                    <div className="w-8 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded flex items-center justify-center text-white text-xs font-bold">
                      {selectedCountry.code}
                    </div>
                    <span className="font-medium">{selectedCountry.name}</span>
                  </>
                ) : (
                  <>
                    <Globe className="h-5 w-5 text-muted-foreground" />
                    <span className="text-muted-foreground">Choose your country</span>
                  </>
                )}
              </div>
              {showCountryDropdown ? (
                <ChevronUp className="h-4 w-4 text-muted-foreground" />
              ) : (
                <ChevronDown className="h-4 w-4 text-muted-foreground" />
              )}
            </div>
          </div>

          <AnimatePresence>
            {showCountryDropdown && (
              <motion.div
                initial={{ opacity: 0, y: -10, scale: 0.95 }}
                animate={{ opacity: 1, y: 0, scale: 1 }}
                exit={{ opacity: 0, y: -10, scale: 0.95 }}
                className="absolute top-full left-0 right-0 mt-2 bg-background border border-input rounded-lg shadow-lg z-50 max-h-64 overflow-hidden"
              >
                <div className="p-3 border-b border-input">
                  <div className="relative">
                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                      placeholder="Search countries..."
                      value={searchTerm}
                      onChange={(e) => setSearchTerm(e.target.value)}
                      className="pl-10"
                    />
                  </div>
                </div>
                
                <div className="max-h-48 overflow-y-auto">
                  {filteredCountries.map((country) => (
                    <div
                      key={country.code}
                      className="p-3 hover:bg-muted/50 cursor-pointer transition-colors border-b border-input/50 last:border-b-0"
                      onClick={() => handleCountrySelect(country)}
                    >
                      <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-3">
                          <div className="w-8 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded flex items-center justify-center text-white text-xs font-bold">
                            {country.code}
                          </div>
                          <div>
                            <div className="font-medium">{country.name}</div>
                            <div className="text-xs text-muted-foreground">
                              Default: {country.currency_code}
                            </div>
                          </div>
                        </div>
                        {selectedCountry?.code === country.code && (
                          <Check className="h-4 w-4 text-primary" />
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              </motion.div>
            )}
          </AnimatePresence>
        </div>
      </motion.div>

      {/* Currency Selection */}
      <motion.div
        initial={{ opacity: 0, y: 30 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.3 }}
        className="mb-8"
      >
        <label className="block text-sm font-medium text-foreground mb-3">
          <DollarSign className="h-4 w-4 inline mr-2 text-primary" />
          Select Your Currency
        </label>
        
        <div className="relative">
          <div
            className="w-full p-4 border border-input bg-background rounded-lg cursor-pointer hover:border-primary/50 transition-colors"
            onClick={() => setShowCurrencyDropdown(!showCurrencyDropdown)}
          >
            <div className="flex items-center justify-between">
              <div className="flex items-center space-x-3">
                {selectedCurrency ? (
                  <>
                    <div className="w-10 h-6 bg-gradient-to-r from-green-500 to-green-600 rounded flex items-center justify-center text-white text-xs font-bold">
                      {selectedCurrency.symbol}
                    </div>
                    <div>
                      <div className="font-medium">{selectedCurrency.name}</div>
                      <div className="text-xs text-muted-foreground">
                        {selectedCurrency.code} • {formatCurrencySync(1000, selectedCurrency.code, selectedCurrency.symbol)}
                      </div>
                    </div>
                  </>
                ) : (
                  <>
                    <Currency className="h-5 w-5 text-muted-foreground" />
                    <span className="text-muted-foreground">Choose your currency</span>
                  </>
                )}
              </div>
              {showCurrencyDropdown ? (
                <ChevronUp className="h-4 w-4 text-muted-foreground" />
              ) : (
                <ChevronDown className="h-4 w-4 text-muted-foreground" />
              )}
            </div>
          </div>

          <AnimatePresence>
            {showCurrencyDropdown && (
              <motion.div
                initial={{ opacity: 0, y: -10, scale: 0.95 }}
                animate={{ opacity: 1, y: 0, scale: 1 }}
                exit={{ opacity: 0, y: -10, scale: 0.95 }}
                className="absolute top-full left-0 right-0 mt-2 bg-background border border-input rounded-lg shadow-lg z-50 max-h-64 overflow-y-auto"
              >
                {currencies.map((currency) => (
                  <div
                    key={currency.id}
                    className="p-3 hover:bg-muted/50 cursor-pointer transition-colors border-b border-input/50 last:border-b-0"
                    onClick={() => handleCurrencySelect(currency)}
                  >
                    <div className="flex items-center justify-between">
                      <div className="flex items-center space-x-3">
                        <div className="w-10 h-6 bg-gradient-to-r from-green-500 to-green-600 rounded flex items-center justify-center text-white text-xs font-bold">
                          {currency.symbol}
                        </div>
                        <div>
                          <div className="font-medium">{currency.name}</div>
                          <div className="text-xs text-muted-foreground">
                            {currency.code} • {formatCurrencySync(1000, currency.code, currency.symbol)}
                          </div>
                        </div>
                      </div>
                      <div className="flex items-center space-x-2">
                        {currency.is_base && (
                          <Badge variant="secondary" className="text-xs">
                            Base
                          </Badge>
                        )}
                        {selectedCurrency?.id === currency.id && (
                          <Check className="h-4 w-4 text-primary" />
                        )}
                      </div>
                    </div>
                  </div>
                ))}
              </motion.div>
            )}
          </AnimatePresence>
        </div>
      </motion.div>

      {/* Enhanced Continue Button */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.4 }}
        className="space-y-3"
      >
        <Button
          onClick={handleContinue}
          disabled={!selectedCountry || !selectedCurrency || loading}
          className="w-full h-12 text-base font-semibold bg-gradient-to-r from-primary to-primary/90 hover:from-primary/90 hover:to-primary shadow-lg hover:shadow-xl transition-all duration-300"
          size="lg"
        >
          {loading ? (
            <div className="flex items-center">
              <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2" />
              Setting up your preferences...
            </div>
          ) : (
            <div className="flex items-center">
              <span>Complete Setup</span>
              <motion.div
                initial={{ x: 0 }}
                animate={{ x: [0, 5, 0] }}
                transition={{ duration: 1.5, repeat: Infinity }}
                className="ml-2"
              >
                →
              </motion.div>
            </div>
          )}
        </Button>

        {/* Enhanced Note */}
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.5 }}
          className="text-center"
        >
          <p className="text-xs text-muted-foreground">
            💡 You can change your location and currency anytime in your settings
          </p>
        </motion.div>
      </motion.div>
    </div>
  );
};

export default CountryCurrencyStep; 