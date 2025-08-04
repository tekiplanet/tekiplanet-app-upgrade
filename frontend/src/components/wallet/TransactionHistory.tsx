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
  async getUserTransactions(page: number = 1, limit: number = 20, filters: any = {}) {
    const token = localStorage.getItem('token');
    if (!token) {
      throw new Error('No authentication token');
    }

    // Build query parameters
    const params = new URLSearchParams({
      page: page.toString(),
      limit: limit.toString()
    });

    // Add filter parameters
    if (filters.search) params.append('search', filters.search);
    if (filters.type && filters.type !== 'all') params.append('type', filters.type);
    if (filters.status && filters.status !== 'all') params.append('status', filters.status);
    if (filters.startDate) params.append('start_date', filters.startDate);
    if (filters.endDate) params.append('end_date', filters.endDate);

    const response = await fetch(`${import.meta.env.VITE_API_URL}/transactions?${params.toString()}`, {
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
    return {
      transactions: data.transactions || [],
      pagination: data.pagination || {},
      filters: data.filters || {},
      stats: data.stats || {}
    };
  }
};

interface TransactionHistoryProps {
  showHeader?: boolean;
  showFilters?: boolean;
  className?: string;
}

export default function TransactionHistory({ 
  showHeader = true, 
  showFilters = true,
  className = ""
}: TransactionHistoryProps) {
  const user = useAuthStore(state => state.user);
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const [error, setError] = useState<string | null>(null);
  
  // Pagination state
  const [currentPage, setCurrentPage] = useState(1);
  const [hasMorePages, setHasMorePages] = useState(true);
  const [paginationInfo, setPaginationInfo] = useState<any>({});
  
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
  const [statusFilter, setStatusFilter] = useState("all");
  const [debouncedSearchQuery, setDebouncedSearchQuery] = useState("");
  const [isSearching, setIsSearching] = useState(false);

  const navigate = useNavigate();

  // Debounce search query
  useEffect(() => {
    const timer = setTimeout(() => {
      setDebouncedSearchQuery(searchQuery);
    }, 500);

    return () => clearTimeout(timer);
  }, [searchQuery]);

  // Fetch transactions on component mount and when filters change
  useEffect(() => {
    const fetchTransactions = async () => {
      if (!user) {
        return;
      }

      try {
        // Only show loading spinner for filter changes, not initial load
        if (debouncedSearchQuery || typeFilter !== 'all' || statusFilter !== 'all') {
          setIsSearching(true);
        } else {
          setIsLoading(true);
        }
        
        const filters = {
          search: debouncedSearchQuery,
          type: typeFilter,
          status: statusFilter
        };
        
        const result = await transactionService.getUserTransactions(1, 20, filters);
        
        setTransactions(result.transactions);
        setPaginationInfo(result.pagination);
        setHasMorePages(result.pagination.has_more_pages || false);
        setCurrentPage(1); // Reset to first page when filters change
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
        setIsSearching(false);
      }
    };

    fetchTransactions();
  }, [user, debouncedSearchQuery, typeFilter, statusFilter]);

  // Load more transactions function
  const loadMoreTransactions = async () => {
    if (!user || isLoadingMore || !hasMorePages || isSearching) return;

    try {
      setIsLoadingMore(true);
      const nextPage = currentPage + 1;
      const filters = {
        search: debouncedSearchQuery,
        type: typeFilter,
        status: statusFilter
      };
      
      const result = await transactionService.getUserTransactions(nextPage, 20, filters);
      
      setTransactions(prev => [...prev, ...result.transactions]);
      setPaginationInfo(result.pagination);
      setHasMorePages(result.pagination.has_more_pages || false);
      setCurrentPage(nextPage);
    } catch (err) {
      console.error('Failed to load more transactions:', err);
      const errorMessage = err instanceof Error ? err.message : 'An unknown error occurred';
      toast.error('Failed to load more transactions', {
        description: errorMessage
      });
    } finally {
      setIsLoadingMore(false);
    }
  };

  // Clear all filters
  const clearFilters = () => {
    setSearchQuery("");
    setTypeFilter("all");
    setStatusFilter("all");
  };

  // Check if any filters are active
  const hasActiveFilters = searchQuery || typeFilter !== 'all' || statusFilter !== 'all';

  // Use transactions directly since we're now using server-side filtering
  const displayedTransactions = transactions;

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
            </div>
          </CardHeader>
        </Card>
      )}

      {/* Search and Filter Section */}
      {showFilters && (
        <Card className="border-none shadow-lg rounded-2xl mb-6 bg-gradient-to-r from-primary/5 to-primary/10">
          <CardContent className="p-6">
            <div className="space-y-4">
              {/* Section Title */}
              <div className="flex items-center gap-2 mb-4">
                <div className="p-2 bg-primary/10 rounded-lg">
                  <Search className="w-4 h-4 text-primary" />
                </div>
                <h3 className="text-lg font-semibold text-foreground">Search & Filter</h3>
              </div>
              
              {/* Search and Filters Grid */}
              <div className="grid grid-cols-1 lg:grid-cols-4 gap-4">
                {/* Search Input */}
                <div className="lg:col-span-2">
                  <label className="text-sm font-medium text-muted-foreground mb-2 block">
                    Search Transactions
                  </label>
                  <div className="relative">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground w-4 h-4" />
                    <Input 
                      placeholder="Search by description or transaction ID..." 
                      value={searchQuery}
                      onChange={(e) => setSearchQuery(e.target.value)}
                      className={`pl-10 w-full bg-background/50 border border-border/50 rounded-xl focus:ring-2 focus:ring-primary/20 transition-all duration-200 ${searchQuery ? 'border-primary/30 bg-primary/5' : ''}`}
                    />
                    {isSearching && (
                      <div className="absolute right-3 top-1/2 -translate-y-1/2">
                        <div className="animate-spin rounded-full h-4 w-4 border-t-2 border-primary"></div>
                      </div>
                    )}
                  </div>
                </div>
                
                {/* Type Filter */}
                <div>
                  <label className="text-sm font-medium text-muted-foreground mb-2 block">
                    Transaction Type
                  </label>
                  <Select value={typeFilter} onValueChange={setTypeFilter}>
                    <SelectTrigger className={`w-full bg-background/50 border border-border/50 rounded-xl focus:ring-2 focus:ring-primary/20 transition-all duration-200 ${typeFilter !== 'all' ? 'ring-2 ring-primary/20 border-primary/30' : ''}`}>
                      <div className="flex items-center gap-2">
                        {typeFilter === 'credit' && <ArrowDownRight className="w-4 h-4 text-green-500" />}
                        {typeFilter === 'debit' && <ArrowUpRight className="w-4 h-4 text-red-500" />}
                        {typeFilter === 'all' && <Filter className="w-4 h-4 text-muted-foreground" />}
                        <span className="text-sm">
                          {typeFilter === 'all' ? 'All Types' : 
                           typeFilter === 'credit' ? 'Credits' : 
                           typeFilter === 'debit' ? 'Debits' : 'Select type'}
                        </span>
                      </div>
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">
                        <div className="flex items-center gap-2">
                          <Filter className="w-4 h-4" />
                          All Types
                        </div>
                      </SelectItem>
                      <SelectItem value="credit">
                        <div className="flex items-center gap-2">
                          <ArrowDownRight className="w-4 h-4 text-green-500" />
                          Credits
                        </div>
                      </SelectItem>
                      <SelectItem value="debit">
                        <div className="flex items-center gap-2">
                          <ArrowUpRight className="w-4 h-4 text-red-500" />
                          Debits
                        </div>
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                
                {/* Status Filter */}
                <div>
                  <label className="text-sm font-medium text-muted-foreground mb-2 block">
                    Status
                  </label>
                  <Select value={statusFilter} onValueChange={setStatusFilter}>
                    <SelectTrigger className={`w-full bg-background/50 border border-border/50 rounded-xl focus:ring-2 focus:ring-primary/20 transition-all duration-200 ${statusFilter !== 'all' ? 'ring-2 ring-primary/20 border-primary/30' : ''}`}>
                      <div className="flex items-center gap-2">
                        {statusFilter === 'completed' && <div className="w-2 h-2 bg-green-500 rounded-full" />}
                        {statusFilter === 'pending' && <div className="w-2 h-2 bg-yellow-500 rounded-full" />}
                        {statusFilter === 'cancelled' && <div className="w-2 h-2 bg-red-500 rounded-full" />}
                        {statusFilter === 'all' && <Filter className="w-4 h-4 text-muted-foreground" />}
                        <span className="text-sm">
                          {statusFilter === 'all' ? 'All Status' : 
                           statusFilter === 'completed' ? 'Completed' : 
                           statusFilter === 'pending' ? 'Pending' : 
                           statusFilter === 'cancelled' ? 'Cancelled' : 'Select status'}
                        </span>
                      </div>
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">
                        <div className="flex items-center gap-2">
                          <Filter className="w-4 h-4" />
                          All Status
                        </div>
                      </SelectItem>
                      <SelectItem value="completed">
                        <div className="flex items-center gap-2">
                          <div className="w-2 h-2 bg-green-500 rounded-full" />
                          Completed
                        </div>
                      </SelectItem>
                      <SelectItem value="pending">
                        <div className="flex items-center gap-2">
                          <div className="w-2 h-2 bg-yellow-500 rounded-full" />
                          Pending
                        </div>
                      </SelectItem>
                      <SelectItem value="cancelled">
                        <div className="flex items-center gap-2">
                          <div className="w-2 h-2 bg-red-500 rounded-full" />
                          Cancelled
                        </div>
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
              
              {/* Active Filters and Clear Button */}
              {hasActiveFilters && (
                <div className="pt-4 border-t border-border/30">
                  <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    {/* Active Filters Display */}
                    <div className="flex-1">
                      <div className="flex items-center gap-2 mb-2">
                        <span className="text-sm font-medium text-muted-foreground">Active filters:</span>
                        <span className="text-xs text-muted-foreground">
                          ({[searchQuery, typeFilter !== 'all', statusFilter !== 'all'].filter(Boolean).length} applied)
                        </span>
                      </div>
                      <div className="flex flex-wrap gap-2">
                        {searchQuery && (
                          <Badge variant="secondary" className="text-xs px-2 py-1 bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/20 dark:text-blue-400 dark:border-blue-500/30">
                            <Search className="w-3 h-3 mr-1" />
                            Search: "{searchQuery.length > 20 ? searchQuery.substring(0, 20) + '...' : searchQuery}"
                          </Badge>
                        )}
                        {typeFilter !== 'all' && (
                          <Badge variant="secondary" className="text-xs px-2 py-1 bg-green-50 text-green-700 border-green-200 dark:bg-green-500/20 dark:text-green-400 dark:border-green-500/30">
                            {typeFilter === 'credit' ? (
                              <ArrowDownRight className="w-3 h-3 mr-1 text-green-500" />
                            ) : (
                              <ArrowUpRight className="w-3 h-3 mr-1 text-red-500" />
                            )}
                            Type: {typeFilter === 'credit' ? 'Credits' : 'Debits'}
                          </Badge>
                        )}
                        {statusFilter !== 'all' && (
                          <Badge variant="secondary" className="text-xs px-2 py-1 bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-500/20 dark:text-purple-400 dark:border-purple-500/30">
                            <div className={`w-2 h-2 rounded-full mr-1 ${
                              statusFilter === 'completed' ? 'bg-green-500' :
                              statusFilter === 'pending' ? 'bg-yellow-500' : 'bg-red-500'
                            }`} />
                            Status: {statusFilter.charAt(0).toUpperCase() + statusFilter.slice(1)}
                          </Badge>
                        )}
                      </div>
                    </div>
                    
                    {/* Clear Button */}
                    <div className="flex-shrink-0">
                      <Button 
                        variant="outline" 
                        size="sm"
                        onClick={clearFilters}
                        className="h-8 px-3 bg-background/50 border border-border/50 rounded-lg hover:bg-background/70 transition-all duration-200 text-xs"
                      >
                        <Filter className="w-3 h-3 mr-1" />
                        Clear All
                      </Button>
                    </div>
                  </div>
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      )}

      <Card className="border-none shadow-lg rounded-2xl">
        <CardContent className="p-0">
          {/* Search Loading Indicator */}
          {isSearching && (
            <div className="flex items-center justify-center py-4 border-b border-border/30">
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <div className="animate-spin rounded-full h-4 w-4 border-t-2 border-primary"></div>
                Searching transactions...
              </div>
            </div>
          )}
          
          {displayedTransactions.length > 0 ? (
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
                            amount={typeof transaction.amount === 'string' ? parseFloat(transaction.amount) : transaction.amount} 
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
                        #{String(transaction.id).slice(-8)}
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
                {isSearching ? 'Searching...' : 'No transactions found'}
              </p>
              <p className="text-sm text-muted-foreground">
                {isSearching ? 'Please wait while we search for your transactions' :
                 searchQuery || typeFilter !== 'all' || statusFilter !== 'all' 
                  ? 'Try adjusting your search or filter criteria'
                  : 'Your transactions will appear here'
                }
              </p>
            </div>
          )}
          
          {/* Load More Button */}
          {hasMorePages && (
            <div className="p-4 border-t border-border/50">
              <Button 
                variant="outline" 
                onClick={loadMoreTransactions}
                disabled={isLoadingMore}
                className="w-full rounded-full text-sm hover:bg-primary hover:text-primary-foreground transition-colors"
              >
                {isLoadingMore ? (
                  <div className="flex items-center gap-2">
                    <div className="animate-spin rounded-full h-4 w-4 border-t-2 border-primary"></div>
                    Loading...
                  </div>
                ) : (
                  `Load More (${paginationInfo.total - transactions.length} more)`
                )}
              </Button>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
} 