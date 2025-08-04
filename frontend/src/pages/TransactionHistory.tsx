import React, { useState, useEffect } from "react";
import { ArrowLeft, Download, Upload, Filter, TrendingUp, TrendingDown } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { useNavigate } from "react-router-dom";
import { useAuthStore } from "@/store/useAuthStore";
import TransactionHistoryComponent from "@/components/wallet/TransactionHistory";
import { transactionService, TransactionStats } from "@/services/transactionService";
import { 
  formatAmountInUserCurrencySync
} from "@/lib/currency";

export default function TransactionHistoryPage() {
  const navigate = useNavigate();
  const user = useAuthStore(state => state.user);
  const [transactionStats, setTransactionStats] = useState<TransactionStats | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [currencySymbol, setCurrencySymbol] = useState<string>('$');

  // Fetch user's currency symbol
  useEffect(() => {
    const fetchUserCurrencySymbol = async () => {
      if (!user?.currency_code) {
        setCurrencySymbol('$');
        return;
      }

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
      }
    };

    fetchUserCurrencySymbol();
  }, [user?.currency_code]);

  // Fetch transaction stats
  useEffect(() => {
    const fetchTransactionStats = async () => {
      try {
        setIsLoading(true);
        const response = await transactionService.getTransactionStats();
        setTransactionStats(response.data);
      } catch (error) {
        console.error('Error fetching transaction stats:', error);
      } finally {
        setIsLoading(false);
      }
    };

    fetchTransactionStats();
  }, []);

  return (
    <div className="min-h-screen bg-background">
      {/* Header */}
      <div className="sticky top-0 z-50 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 border-b border-border">
        <div className="flex items-center justify-between p-4 md:p-6">
          <div className="flex items-center space-x-4">
            <div>
              <h1 className="text-xl md:text-2xl font-bold">Transaction History</h1>
              <p className="text-sm text-muted-foreground">
                View and manage your transaction records
              </p>
            </div>
          </div>
          
          <div className="flex items-center space-x-2">
            <Button variant="outline" size="sm" className="hidden sm:flex">
              <Download className="h-4 w-4 mr-2" />
              Export
            </Button>
            <Button variant="outline" size="sm" className="hidden sm:flex">
              <Filter className="h-4 w-4 mr-2" />
              Filter
            </Button>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="p-4 md:p-6 space-y-6">
        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          {isLoading ? (
            // Loading skeleton
            <>
              {[...Array(4)].map((_, index) => (
                <Card key={index} className="border-none shadow-lg rounded-2xl">
                  <CardContent className="p-4 md:p-6">
                    <div className="flex items-center justify-between">
                      <div className="min-w-0">
                        <div className="h-4 bg-muted rounded animate-pulse mb-2"></div>
                        <div className="h-6 bg-muted rounded animate-pulse"></div>
                      </div>
                      <div className="p-2 md:p-3 bg-muted rounded-full shrink-0 animate-pulse">
                        <div className="h-4 w-4 md:h-5 md:w-5"></div>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </>
          ) : (
            <>
              {/* Total Transactions */}
              <Card className="border-none shadow-lg rounded-2xl">
                <CardContent className="p-4 md:p-6">
                  <div className="flex items-center justify-between">
                    <div className="min-w-0">
                      <p className="text-xs md:text-sm font-medium text-muted-foreground truncate">
                        Total Transactions
                      </p>
                      <h3 className="text-lg md:text-2xl font-bold truncate">
                        {transactionStats?.total_transactions || 0}
                      </h3>
                    </div>
                    <div className="p-2 md:p-3 bg-primary/10 rounded-full shrink-0">
                      <TrendingUp className="h-4 w-4 md:h-5 md:w-5 text-primary" />
                    </div>
                  </div>
                </CardContent>
              </Card>

              {/* This Month */}
              <Card className="border-none shadow-lg rounded-2xl">
                <CardContent className="p-4 md:p-6">
                  <div className="flex items-center justify-between">
                    <div className="min-w-0">
                      <p className="text-xs md:text-sm font-medium text-muted-foreground truncate">
                        This Month
                      </p>
                      <h3 className="text-lg md:text-2xl font-bold truncate">
                        {transactionStats?.this_month || 0}
                      </h3>
                    </div>
                    <div className="p-2 md:p-3 bg-green-500/10 rounded-full shrink-0">
                      <TrendingUp className="h-4 w-4 md:h-5 md:w-5 text-green-500" />
                    </div>
                  </div>
                </CardContent>
              </Card>

              {/* Total Received */}
              <Card className="border-none shadow-lg rounded-2xl">
                <CardContent className="p-4 md:p-6">
                  <div className="flex items-center justify-between">
                    <div className="min-w-0">
                      <p className="text-xs md:text-sm font-medium text-muted-foreground truncate">
                        Total Received
                      </p>
                      <h3 className="text-lg md:text-2xl font-bold truncate text-green-600 dark:text-green-400">
                        {formatAmountInUserCurrencySync(
                          transactionStats?.total_received || 0,
                          user?.currency_code,
                          currencySymbol
                        )}
                      </h3>
                    </div>
                    <div className="p-2 md:p-3 bg-green-500/10 rounded-full shrink-0">
                      <Upload className="h-4 w-4 md:h-5 md:w-5 text-green-500" />
                    </div>
                  </div>
                </CardContent>
              </Card>

              {/* Total Spent */}
              <Card className="border-none shadow-lg rounded-2xl">
                <CardContent className="p-4 md:p-6">
                  <div className="flex items-center justify-between">
                    <div className="min-w-0">
                      <p className="text-xs md:text-sm font-medium text-muted-foreground truncate">
                        Total Spent
                      </p>
                      <h3 className="text-lg md:text-2xl font-bold truncate text-red-600 dark:text-red-400">
                        {formatAmountInUserCurrencySync(
                          transactionStats?.total_spent || 0,
                          user?.currency_code,
                          currencySymbol
                        )}
                      </h3>
                    </div>
                    <div className="p-2 md:p-3 bg-red-500/10 rounded-full shrink-0">
                      <Download className="h-4 w-4 md:h-5 md:w-5 text-red-500" />
                    </div>
                  </div>
                </CardContent>
              </Card>
            </>
          )}
        </div>

        {/* Transaction History Component */}
        <TransactionHistoryComponent 
          showHeader={false}
          showFilters={true}
          className=""
        />
      </div>
    </div>
  );
} 