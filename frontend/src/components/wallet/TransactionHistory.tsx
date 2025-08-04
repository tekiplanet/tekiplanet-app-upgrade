import React, { useState, useEffect } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { 
  formatAmountInUserCurrencySync
} from "@/lib/currency";
import { toast } from "sonner";
import { useAuthStore } from "@/store/useAuthStore";
import { 
  ArrowDownRight, 
  ArrowUpRight, 
  Search, 
  Filter, 
  ChevronDown, 
  ChevronUp,
  CalendarIcon,
  Download,
  Upload,
  Loader2
} from "lucide-react";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue
} from "@/components/ui/select";
import { useNavigate } from "react-router-dom";

// Type definition for transactions
type Transaction = {
  id: string;
  user_id: string;
  type: 'credit' | 'debit';
  amount: string;
  status: string;
  description: string;
  created_at: string;
};

// Currency display component that formats amounts with user's currency symbol (no conversion)
const CurrencyDisplay = ({ 
  amount, 
  userCurrencyCode,
  currencySymbol 
}: { 
  amount: number, 
  userCurrencyCode?: string,
  currencySymbol?: string 
}) => {
  // Use synchronous formatting with the provided currency symbol
  const formattedAmount = formatAmountInUserCurrencySync(
    amount, 
    userCurrencyCode, 
    currencySymbol
  );
  
  return <span className="text-sm md:text-2xl font-bold truncate mt-1">{formattedAmount}</span>;
};

// Transaction service
const transactionService = {
  async getUserTransactions() {
    const token = localStorage.getItem('token');
    if (!token) {
      throw new Error('No authentication token');
    }

    const response = await fetch(`${import.meta.env.VITE_API_URL}/transactions`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    });

    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(errorText || 'Failed to fetch transactions');
    }

    const data = await response.json();
    return data.transactions?.data || [];
  }
};

interface TransactionHistoryProps {
  showHeader?: boolean;
  maxTransactions?: number;
  showFilters?: boolean;
  className?: string;
}

export default function TransactionHistory({ 
  showHeader = true, 
  maxTransactions = 50,
  showFilters = true,
  className = ""
}: TransactionHistoryProps) {
  const user = useAuthStore(state => state.user);
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  
  // Currency symbol cache
  const [currencySymbol, setCurrencySymbol] = useState<string>('$');
  const [isLoadingCurrency, setIsLoadingCurrency] = useState(false);
  
  // Fetch user's currency symbol
  useEffect(() => {
    const fetchUserCurrencySymbol = async () => {
      if (!user?.currency_code) {
        setCurrencySymbol('$');
        return;
      }

      if (isLoadingCurrency) return; // Prevent multiple requests

      setIsLoadingCurrency(true);
      try {
        const token = localStorage.getItem('token');
        const response = await fetch(`${import.meta.env.VITE_API_URL}/currency/${user.currency_code}/symbol`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });

        if (response.ok) {
          const data = await response.json();
          const symbol = data.data?.symbol || data.symbol || '$';
          setCurrencySymbol(symbol);
        } else {
          console.warn(`Failed to fetch currency symbol for ${user.currency_code}, using default`);
          setCurrencySymbol('$');
        }
      } catch (error) {
        console.error('Error fetching currency symbol:', error);
        setCurrencySymbol('$');
      } finally {
        setIsLoadingCurrency(false);
      }
    };

    fetchUserCurrencySymbol();
  }, [user?.currency_code]);
  
  const [searchQuery, setSearchQuery] = useState("");
  const [typeFilter, setTypeFilter] = useState("all");
  const [visibleTransactions, setVisibleTransactions] = useState(10);

  const navigate = useNavigate();

  // Fetch transactions on component mount
  useEffect(() => {
    const fetchTransactions = async () => {
      if (!user) {
        return;
      }

      try {
        setIsLoading(true);
        const fetchedTransactions = await transactionService.getUserTransactions();
        
        const transactionArray = Array.isArray(fetchedTransactions) 
          ? fetchedTransactions 
          : fetchedTransactions.data || [];
        
        setTransactions(transactionArray);
        setError(null);
      } catch (err) {
        console.error('Failed to fetch transactions:', err);
        const errorMessage = err instanceof Error ? err.message : 'An unknown error occurred';
        setError(errorMessage);
        toast.error('Failed to load transactions', {
          description: errorMessage
        });
      } finally {
        setIsLoading(false);
      }
    };

    fetchTransactions();
  }, [user]);

  // Filter transactions
  const filteredTransactions = transactions
    .map(t => ({
      ...t,
      amount: parseFloat(t.amount),
      date: t.created_at || new Date().toISOString(),
      type: t.type === 'credit' || t.type === 'debit' ? t.type : 'debit'
    }))
    .filter(t => {
      const matchesSearch = t.description.toLowerCase().includes(searchQuery.toLowerCase());
      const matchesType = typeFilter === "all" || t.type === typeFilter;
      return matchesSearch && matchesType;
    })
    .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
    .slice(0, maxTransactions);

  // Pagination logic
  const displayedTransactions = filteredTransactions.slice(0, visibleTransactions);

  // Toggle transactions visibility
  const toggleTransactionsVisibility = () => {
    setVisibleTransactions(prev => 
      prev === 10 ? filteredTransactions.length : 10
    );
  };

  // Status label component
  const StatusLabel = ({ status }: { status: string }) => {
    const statusConfig = {
      'completed': {
        className: 'bg-green-50 text-green-700 dark:bg-green-500/20 dark:text-green-400 border-green-200 dark:border-green-500/30',
        icon: '✓'
      },
      'pending': {
        className: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400 border-yellow-200 dark:border-yellow-500/30',
        icon: '⏳'
      },
      'cancelled': {
        className: 'bg-red-50 text-red-700 dark:bg-red-500/20 dark:text-red-400 border-red-200 dark:border-red-500/30',
        icon: '✕'
      }
    };

    const config = statusConfig[status] || {
      className: 'bg-gray-50 text-gray-700 dark:bg-gray-500/20 dark:text-gray-400 border-gray-200 dark:border-gray-500/30',
      icon: '?'
    };

    return (
      <span 
        className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border ${config.className}`}
      >
        <span className="mr-1 text-[10px]">{config.icon}</span>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </span>
    );
  };

  // Render loading or error states
  if (isLoading) {
    return (
      <div className="flex justify-center items-center min-h-[200px]">
        <div className="animate-spin rounded-full h-8 w-8 border-t-2 border-primary"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="text-center text-destructive p-4">
        <p>Error loading transactions: {error}</p>
        <Button onClick={() => window.location.reload()} className="mt-4">
          Try Again
        </Button>
      </div>
    );
  }

  return (
    <div className={className}>
      {showHeader && (
        <Card className="border-none shadow-lg rounded-2xl mb-6">
          <CardHeader className="pb-2">
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center space-y-2 md:space-y-0">
              <CardTitle className="text-lg md:text-2xl font-bold text-foreground">
                Transaction History
              </CardTitle>
              {showFilters && (
                <div className="flex items-center space-x-2 w-full md:w-auto">
                  <div className="relative flex-grow">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground w-4 h-4" />
                    <Input 
                      placeholder="Search transactions" 
                      value={searchQuery}
                      onChange={(e) => setSearchQuery(e.target.value)}
                      className="pl-10 w-full bg-secondary/50 border-none rounded-full"
                    />
                  </div>
                  <Select value={typeFilter} onValueChange={setTypeFilter}>
                    <SelectTrigger className="w-[120px] bg-secondary/50 border-none rounded-full">
                      <Filter className="w-4 h-4 mr-2 text-muted-foreground" />
                      <SelectValue placeholder="Filter" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">All Transactions</SelectItem>
                      <SelectItem value="credit">Credits</SelectItem>
                      <SelectItem value="debit">Debits</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              )}
            </div>
          </CardHeader>
        </Card>
      )}

      <Card className="border-none shadow-lg rounded-2xl">
        <CardContent className="p-0">
          {filteredTransactions.length > 0 ? (
            <div className="space-y-0">
              {displayedTransactions.map((transaction) => (
                <div 
                  key={transaction.id} 
                  className="group relative overflow-hidden transition-all duration-200 hover:bg-secondary/20 active:bg-secondary/30"
                  onClick={() => navigate(`/dashboard/wallet/transactions/${transaction.id}`)}
                >
                  {/* New compact transaction design */}
                  <div className="p-4 cursor-pointer">
                    {/* Top row: Icon, Amount, Status */}
                    <div className="flex items-start justify-between mb-3">
                      {/* Left: Icon and Type */}
                      <div className="flex items-center space-x-3">
                        <div className={`
                          relative w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                          ${transaction.type === 'credit' 
                            ? 'bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400' 
                            : 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400'}
                        `}>
                          {transaction.type === 'credit' ? (
                            <ArrowDownRight className="w-4 h-4" />
                          ) : (
                            <ArrowUpRight className="w-4 h-4" />
                          )}
                          {/* Status indicator */}
                          <div className={`
                            absolute -top-0.5 -right-0.5 w-2 h-2 rounded-full border border-white dark:border-gray-900
                            ${transaction.status === 'completed' ? 'bg-green-500' : 
                              transaction.status === 'pending' ? 'bg-yellow-500' : 'bg-red-500'}
                          `} />
                        </div>
                        
                        {/* Transaction type label */}
                        <div className="flex flex-col">
                          <span className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                            {transaction.type === 'credit' ? 'Received' : 'Sent'}
                          </span>
                          <StatusLabel status={transaction.status} />
                        </div>
                      </div>
                      
                      {/* Right: Amount */}
                      <div className="text-right">
                        <div className={`
                          text-lg font-bold
                          ${transaction.type === 'credit' 
                            ? 'text-green-600 dark:text-green-400' 
                            : 'text-red-600 dark:text-red-400'}
                        `}>
                          {transaction.type === 'credit' ? '+' : '-'}
                          <CurrencyDisplay 
                            amount={parseFloat(transaction.amount)} 
                            userCurrencyCode={user?.currency_code}
                            currencySymbol={currencySymbol}
                          />
                        </div>
                      </div>
                    </div>
                    
                    {/* Description - Full width */}
                    <div className="mb-3">
                      <p className="text-sm font-medium text-foreground leading-relaxed break-words">
                        {transaction.description}
                      </p>
                    </div>
                    
                    {/* Bottom row: Date and Time */}
                    <div className="flex items-center justify-between">
                      <div className="flex items-center space-x-2 text-xs text-muted-foreground">
                        <CalendarIcon className="w-3 h-3" />
                        <span>
                          {new Date(transaction.created_at).toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                          })}
                        </span>
                        <span className="hidden sm:inline">•</span>
                        <span className="hidden sm:inline">
                          {new Date(transaction.created_at).toLocaleTimeString('en-US', {
                            hour: '2-digit',
                            minute: '2-digit'
                          })}
                        </span>
                      </div>
                      
                      {/* Transaction ID (truncated) */}
                      <div className="text-xs text-muted-foreground font-mono">
                        #{transaction.id.slice(-8)}
                      </div>
                    </div>
                  </div>
                  
                  {/* Subtle divider */}
                  <div className="absolute bottom-0 left-4 right-4 h-px bg-border/30" />
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center py-12 space-y-4">
              <div className="flex justify-center">
                <div className="bg-secondary/50 p-4 rounded-full">
                  <Filter className="w-8 h-8 text-muted-foreground" />
                </div>
              </div>
              <p className="text-base font-medium text-muted-foreground">
                No transactions found
              </p>
              <p className="text-sm text-muted-foreground">
                Your recent transactions will appear here
              </p>
            </div>
          )}
          
          {/* Load More/View Less Button */}
          {filteredTransactions.length > 10 && (
            <div className="p-4 border-t border-border/50">
              <Button 
                variant="outline" 
                onClick={toggleTransactionsVisibility}
                className="w-full rounded-full text-sm hover:bg-primary hover:text-primary-foreground transition-colors"
              >
                {visibleTransactions === 10 
                  ? `View All (${filteredTransactions.length})` 
                  : 'View Less'}
              </Button>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
} 