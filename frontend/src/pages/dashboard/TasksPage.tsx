import { useQuery } from '@tanstack/react-query';
import { rewardService } from '@/services/rewardService';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { motion } from 'framer-motion';
import { 
  Award, Clock, CheckCircle, AlertCircle, PlayCircle, 
  Calendar, Filter, ChevronLeft, Users, Target,
  TrendingUp, BookOpen, Sparkles, ChevronRight, ChevronsLeft, ChevronsRight
} from 'lucide-react';
import { useState, useEffect } from 'react';
import { cn } from '@/lib/utils';
import { Skeleton } from '@/components/ui/skeleton';
import { useNavigate } from 'react-router-dom';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Dialog, DialogContent, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { toast } from 'sonner';
import { useAuthStore } from '@/store/useAuthStore';
import { formatAmountInUserCurrencySync } from '@/lib/currency';
import { Filesystem, Directory } from '@capacitor/filesystem';

export default function TasksPage() {
  const navigate = useNavigate();
  const { user } = useAuthStore();
  const [statusFilter, setStatusFilter] = useState('all');
  const [sortBy, setSortBy] = useState('created_at');
  const [sortOrder, setSortOrder] = useState('desc');
  const [currentPage, setCurrentPage] = useState(1);
  const [perPage, setPerPage] = useState(10);
  const [showTaskDialog, setShowTaskDialog] = useState(false);
  const [activeTask, setActiveTask] = useState<any>(null);
  const [taskInstructions, setTaskInstructions] = useState<any>(null);
  const [loadingInstructions, setLoadingInstructions] = useState(false);
  const [showRewardDialog, setShowRewardDialog] = useState(false);
  const [taskReward, setTaskReward] = useState<any>(null);
  const [loadingReward, setLoadingReward] = useState(false);
  const [claimingCourseAccess, setClaimingCourseAccess] = useState(false);
  const [claimingCashReward, setClaimingCashReward] = useState(false);
  const [claimingDiscountReward, setClaimingDiscountReward] = useState(false);
  const [downloadingSlip, setDownloadingSlip] = useState(false);
  const [currencySymbol, setCurrencySymbol] = useState<string>('₦');

  // Fetch user currency symbol
  useEffect(() => {
    const fetchUserCurrencySymbol = async () => {
      if (!user?.currency_code) {
        setCurrencySymbol('₦'); // Default to NGN
        return;
      }

      try {
        const token = localStorage.getItem('token');
        const response = await fetch(`${import.meta.env.VITE_API_URL}/currency/${user.currency_code}/symbol`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
        });

        if (response.ok) {
          const data = await response.json();
          const symbol = data.data?.symbol || data.symbol || '$';
          setCurrencySymbol(symbol);
        } else {
          console.warn(`Failed to fetch currency symbol for ${user.currency_code}, using default`);
          setCurrencySymbol(user.currency_code === 'NGN' ? '₦' : '$');
        }
      } catch (error) {
        console.warn(`Failed to fetch currency symbol for ${user.currency_code}, using default`);
        setCurrencySymbol(user.currency_code === 'NGN' ? '₦' : '$');
      }
    };

    fetchUserCurrencySymbol();
  }, [user?.currency_code]);

  // CurrencyDisplay component that handles conversion properly
  const CurrencyDisplay = ({ 
    amount, 
    userCurrencyCode,
    currencySymbol 
  }: { 
    amount: number, 
    userCurrencyCode?: string,
    currencySymbol?: string 
  }) => {
    const [formattedAmount, setFormattedAmount] = useState<string>('0');
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
      const formatAmount = async () => {
        try {
          setIsLoading(true);
          if (currencySymbol && currencySymbol !== '₦') {
            // Use provided symbol to avoid API call
            const formatted = formatAmountInUserCurrencySync(amount, userCurrencyCode, currencySymbol);
            setFormattedAmount(formatted);
          } else {
            // Do full conversion with API call
            const { formatAmountInUserCurrency } = await import('@/lib/currency');
            const formatted = await formatAmountInUserCurrency(amount, userCurrencyCode);
            setFormattedAmount(formatted);
          }
        } catch (error) {
          console.error('Error formatting currency:', error);
          // Fallback to sync version with default symbol
          const fallbackSymbol = userCurrencyCode === 'NGN' ? '₦' : '$';
          setFormattedAmount(formatAmountInUserCurrencySync(amount, userCurrencyCode, fallbackSymbol));
        } finally {
          setIsLoading(false);
        }
      };

      formatAmount();
    }, [amount, userCurrencyCode, currencySymbol]);

    if (isLoading) {
      return <span>...</span>;
    }

    return <span>{formattedAmount}</span>;
  };

  // Fetch user tasks with pagination and filtering
  const { data, isLoading, refetch } = useQuery({
    queryKey: ['rewards-tasks', statusFilter, sortBy, sortOrder, currentPage, perPage],
    queryFn: () => rewardService.getUserTasks({
      page: currentPage,
      per_page: perPage,
      status: statusFilter,
      sort_by: sortBy,
      sort_order: sortOrder
    }),
    retry: 2
  });

  const tasks = (data as any)?.tasks || [];
  const pagination = (data as any)?.pagination || {};
  const stats = (data as any)?.stats || {
    total_tasks: 0,
    assigned_tasks: 0,
    in_progress_tasks: 0,
    completed_tasks: 0,
    failed_tasks: 0,
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'completed': return 'bg-green-500/10 text-green-500 border-green-500/20';
      case 'in_progress': return 'bg-blue-500/10 text-blue-500 border-blue-500/20';
      case 'assigned': return 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20';
      case 'failed': return 'bg-red-500/10 text-red-500 border-red-500/20';
      default: return 'bg-gray-500/10 text-gray-500 border-gray-500/20';
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'completed': return <CheckCircle className="h-4 w-4" />;
      case 'in_progress': return <PlayCircle className="h-4 w-4" />;
      case 'assigned': return <Clock className="h-4 w-4" />;
      case 'failed': return <AlertCircle className="h-4 w-4" />;
      default: return <Clock className="h-4 w-4" />;
    }
  };

  const handlePageChange = (page: number) => {
    setCurrentPage(page);
  };

  const handleFilterChange = (newStatus: string) => {
    setStatusFilter(newStatus);
    setCurrentPage(1); // Reset to first page when filtering
  };

  const handleSortChange = (newSortBy: string) => {
    setSortBy(newSortBy);
    setCurrentPage(1); // Reset to first page when sorting
  };

  const handlePerPageChange = (newPerPage: string) => {
    setPerPage(parseInt(newPerPage));
    setCurrentPage(1); // Reset to first page when changing per page
  };

  const handleStartTask = async (task: any) => {
    setActiveTask(task);
    setShowTaskDialog(true);
    setLoadingInstructions(true);
    try {
      const instructions = await rewardService.getTaskInstructions(task.id);
      setTaskInstructions(instructions);
    } catch (e) {
      toast.error('Failed to load task instructions.');
      setTaskInstructions(null);
    } finally {
      setLoadingInstructions(false);
    }
  };

  const handleCopyReferralLink = () => {
    if (taskInstructions?.referral_link) {
      navigator.clipboard.writeText(taskInstructions.referral_link);
      toast.success('Referral link copied!');
    }
  };

  const handleCopyShareLink = () => {
    if (taskInstructions?.share_link) {
      navigator.clipboard.writeText(taskInstructions.share_link);
      toast.success('Share link copied!');
    }
  };

  const handleViewReward = async (task: any) => {
    setActiveTask(task);
    setShowRewardDialog(true);
    setLoadingReward(true);
    try {
      const reward = await rewardService.getTaskReward(task.id);
      setTaskReward(reward);
    } catch (e) {
      toast.error('Failed to load reward details.');
      setTaskReward(null);
    } finally {
      setLoadingReward(false);
    }
  };

  // Generate pagination buttons
  const generatePaginationButtons = () => {
    const buttons = [];
    const totalPages = pagination.last_page || 1;
    const currentPageNum = pagination.current_page || 1;

    // Previous button
    buttons.push(
      <Button
        key="prev"
        variant="outline"
        size="sm"
        onClick={() => handlePageChange(currentPageNum - 1)}
        disabled={currentPageNum <= 1}
        className="flex items-center gap-1"
      >
        <ChevronLeft className="h-4 w-4" />
        Previous
      </Button>
    );

    // Page numbers
    const startPage = Math.max(1, currentPageNum - 2);
    const endPage = Math.min(totalPages, currentPageNum + 2);

    for (let i = startPage; i <= endPage; i++) {
      buttons.push(
        <Button
          key={i}
          variant={i === currentPageNum ? "default" : "outline"}
          size="sm"
          onClick={() => handlePageChange(i)}
          className="min-w-[40px]"
        >
          {i}
        </Button>
      );
    }

    // Next button
    buttons.push(
      <Button
        key="next"
        variant="outline"
        size="sm"
        onClick={() => handlePageChange(currentPageNum + 1)}
        disabled={currentPageNum >= totalPages}
        className="flex items-center gap-1"
      >
        Next
        <ChevronRight className="h-4 w-4" />
      </Button>
    );

    return buttons;
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-gradient-to-b from-background to-primary/5 p-2 sm:p-4 md:p-6 space-y-6">
        <Skeleton className="h-[200px] rounded-xl" />
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
          {[1, 2, 3, 4, 5].map((i) => (
            <Skeleton key={i} className="h-[120px] rounded-xl" />
          ))}
        </div>
        <div className="space-y-4">
          <Skeleton className="h-8 w-48" />
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {[1, 2, 3, 4, 5, 6].map((i) => (
              <Skeleton key={i} className="h-[200px] rounded-xl" />
            ))}
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-b from-background to-primary/5 p-2 sm:p-4 md:p-6">
      {/* Header */}
      <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="relative mb-6"
      >
        <Card className={cn(
          "relative overflow-hidden border-none bg-gradient-to-br backdrop-blur-xl",
          "hover:shadow-lg transition-all dark:shadow-none",
          "dark:bg-background/50",
          "from-blue-500/10 via-blue-500/5 to-transparent",
          "dark:from-blue-500/20 dark:via-blue-500/10 dark:to-transparent"
        )}>
          <CardContent className="p-6">
            <div className="flex flex-col gap-4">
              {/* Header */}
              <div className="flex items-start justify-between">
                <div className="space-y-2">
                  <motion.h1 
                    initial={{ opacity: 0, x: -20 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: 0.2 }}
                    className="text-base sm:text-3xl font-bold flex items-center gap-3"
                  >
                    <div className="p-2 rounded-xl bg-blue-500/20">
                      <Award className="h-6 w-6 text-blue-500" />
                    </div>
                    My Conversion Tasks
                  </motion.h1>
                  <motion.p 
                    initial={{ opacity: 0, x: -20 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: 0.3 }}
                    className="text-xs sm:text-base text-muted-foreground"
                  >
                    Track and manage all your conversion tasks
                  </motion.p>
                </div>
                <motion.div
                  initial={{ opacity: 0, scale: 0.5 }}
                  animate={{ opacity: 1, scale: 1 }}
                  transition={{ delay: 0.4 }}
                  className="hidden sm:flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500/20 to-purple-500/20"
                >
                  <Sparkles className="h-8 w-8 text-blue-500" />
                </motion.div>
              </div>

              {/* Back Button */}
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.5 }}
              >
                <Button 
                  variant="outline" 
                  onClick={() => navigate('/dashboard/rewards-tasks')}
                  className="flex items-center gap-2"
                >
                  <ChevronLeft className="h-4 w-4" />
                  Back to Rewards & Tasks
                </Button>
              </motion.div>
            </div>
          </CardContent>
        </Card>
      </motion.div>

      {/* Filters and Controls */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.6 }}
        className="flex flex-col sm:flex-row gap-4 mb-6"
      >
        <div className="flex items-center gap-2">
          <Filter className="h-4 w-4 text-muted-foreground" />
          <span className="text-sm font-medium">Filter:</span>
        </div>
        <Select value={statusFilter} onValueChange={handleFilterChange}>
          <SelectTrigger className="w-full sm:w-[180px]">
            <SelectValue placeholder="Filter by status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Tasks</SelectItem>
            <SelectItem value="assigned">Assigned</SelectItem>
            <SelectItem value="in_progress">In Progress</SelectItem>
            <SelectItem value="completed">Completed</SelectItem>
            <SelectItem value="failed">Failed</SelectItem>
          </SelectContent>
        </Select>
        <Select value={sortBy} onValueChange={handleSortChange}>
          <SelectTrigger className="w-full sm:w-[180px]">
            <SelectValue placeholder="Sort by" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="created_at">Date Created</SelectItem>
            <SelectItem value="assigned_at">Date Assigned</SelectItem>
            <SelectItem value="status">Status</SelectItem>
          </SelectContent>
        </Select>
        <Select value={perPage.toString()} onValueChange={handlePerPageChange}>
          <SelectTrigger className="w-full sm:w-[120px]">
            <SelectValue placeholder="Per page" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="5">5 per page</SelectItem>
            <SelectItem value="10">10 per page</SelectItem>
            <SelectItem value="20">20 per page</SelectItem>
            <SelectItem value="50">50 per page</SelectItem>
          </SelectContent>
        </Select>
      </motion.div>

      {/* Tasks List */}
      <div className="space-y-4">
        {tasks.length === 0 ? (
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="text-center py-12"
          >
            <div className="flex flex-col items-center gap-4">
              <div className="p-4 rounded-full bg-muted/50">
                <Award className="h-8 w-8 text-muted-foreground" />
              </div>
              <div className="space-y-2">
                <h3 className="text-lg font-semibold">No tasks found</h3>
                <p className="text-muted-foreground">
                  {statusFilter === 'all' 
                    ? "You don't have any conversion tasks yet. Go back to convert your rewards!"
                    : `No tasks with status "${statusFilter}" found.`
                  }
                </p>
              </div>
            </div>
          </motion.div>
        ) : (
          <>
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
              {tasks.map((task: any, index: number) => (
                <motion.div
                  key={task.id}
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: index * 0.1 }}
                  whileHover={{ y: -2 }}
                  className="group"
                >
                  <Card className="overflow-hidden border-none bg-background/50 backdrop-blur-xl hover:shadow-xl hover:shadow-primary/10 transition-all rounded-2xl">
                    <CardContent className="p-6">
                      <div className="space-y-4">
                        {/* Header */}
                        <div className="flex items-start justify-between">
                          <div className="flex-1">
                            <h3 className="font-semibold text-lg line-clamp-1">
                              {task.task?.title || 'Conversion Task'}
                            </h3>
                            <p className="text-sm text-muted-foreground mt-1 line-clamp-2">
                              {task.task?.description || 'Complete this task to earn your reward'}
                            </p>
                          </div>
                          <Badge className={cn("ml-2", getStatusColor(task.status))}>
                            <div className="flex items-center gap-1">
                              {getStatusIcon(task.status)}
                              <span className="capitalize">{task.status.replace('_', ' ')}</span>
                            </div>
                          </Badge>
                        </div>

                        {/* Progress */}
                        {task.status === 'in_progress' && (
                          <div className="space-y-2">
                            <div className="flex justify-between text-sm">
                              <span className="text-muted-foreground">Progress</span>
                              <span className="font-medium">
                                {task.task?.type?.name?.toLowerCase().includes('share') && task.share_count !== undefined ? (
                                  `${task.share_count} / ${task.task?.share_target || 1}`
                                ) : task.task?.type?.name?.toLowerCase().includes('refer') && task.referral_count !== undefined ? (
                                  `${task.referral_count} / ${task.task?.referral_target || 1}`
                                ) : (
                                  '75%'
                                )}
                              </span>
                            </div>
                            <Progress 
                              value={
                                task.task?.type?.name?.toLowerCase().includes('share') && task.share_count !== undefined ? (
                                  Math.min((task.share_count / (task.task?.share_target || 1)) * 100, 100)
                                ) : task.task?.type?.name?.toLowerCase().includes('refer') && task.referral_count !== undefined ? (
                                  Math.min((task.referral_count / (task.task?.referral_target || 1)) * 100, 100)
                                ) : 75
                              } 
                              className="h-2" 
                            />
                          </div>
                        )}

                        {/* Details */}
                        <div className="space-y-2 text-sm text-muted-foreground">
                          <div className="flex items-center gap-2">
                            <Calendar className="h-4 w-4" />
                            <span>
                              Assigned: {task.assigned_at ? new Date(task.assigned_at).toLocaleDateString() : 'Recently'}
                            </span>
                          </div>
                          {task.completed_at && (
                            <div className="flex items-center gap-2 text-green-600">
                              <CheckCircle className="h-4 w-4" />
                              <span>Completed: {new Date(task.completed_at).toLocaleDateString()}</span>
                            </div>
                          )}
                        </div>

                        {/* Action Button */}
                        {task.status === 'assigned' && (
                          <Button 
                            size="sm" 
                            className="w-full bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white"
                            onClick={() => handleStartTask(task)}
                          >
                            <PlayCircle className="h-4 w-4 mr-2" />
                            Start Task
                          </Button>
                        )}
                        {task.status === 'completed' && (
                          <Button 
                            size="sm" 
                            className="w-full bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white"
                            onClick={() => handleViewReward(task)}
                          >
                            <Award className="h-4 w-4 mr-2" />
                            View Reward
                          </Button>
                        )}
                      </div>
                    </CardContent>
                  </Card>
                </motion.div>
              ))}
            </div>

            {/* Pagination */}
            {pagination.last_page > 1 && (
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="flex flex-col sm:flex-row items-center justify-between gap-4 mt-8"
              >
                <div className="text-sm text-muted-foreground">
                  Showing {pagination.from || 0} to {pagination.to || 0} of {pagination.total || 0} tasks
                </div>
                <div className="flex items-center gap-2">
                  {generatePaginationButtons()}
                </div>
              </motion.div>
            )}
          </>
        )}
      </div>
      {/* Task Instructions Dialog */}
      <Dialog open={showTaskDialog} onOpenChange={setShowTaskDialog}>
        <DialogContent className="max-w-lg w-full max-h-[80vh] flex flex-col">
          <DialogTitle>Task Instructions</DialogTitle>
          {loadingInstructions ? (
            <div className="py-8 flex justify-center items-center">
              <span className="text-muted-foreground">Loading...</span>
            </div>
          ) : taskInstructions ? (
            <div className="flex-1 overflow-y-auto space-y-4 pr-2">
              <DialogDescription>
                {taskInstructions.instructions}
              </DialogDescription>
              {taskInstructions.referral_link && (
                <div className="space-y-2">
                  <label className="block text-sm font-medium">Your Referral Link</label>
                  <div className="flex gap-2 items-center">
                    <Input
                      value={taskInstructions.referral_link}
                      readOnly
                      className="flex-1 bg-muted/30 text-xs sm:text-sm"
                      onFocus={e => e.target.select()}
                    />
                    <Button size="icon" variant="outline" onClick={handleCopyReferralLink}>
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 16h8M8 12h8m-8-4h8m-2 8v2a2 2 0 002 2h4a2 2 0 002-2V6a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" /></svg>
                    </Button>
                  </div>
                  {taskInstructions.progress && (
                    <div className="text-xs text-muted-foreground mt-1">
                      Progress: {taskInstructions.progress.completed} / {taskInstructions.progress.needed} referrals
                    </div>
                  )}
                </div>
              )}

              {/* Product Share Section */}
              {taskInstructions.product && taskInstructions.share_link && (
                <div className="space-y-4">
                  {/* Product Details */}
                  <div className="p-4 bg-blue-50 dark:bg-blue-950/30 rounded-lg border border-blue-200 dark:border-blue-800">
                    <h4 className="font-medium text-blue-700 dark:text-blue-300 mb-3">Product to Share</h4>
                    <div className="space-y-3">
                      <div className="flex items-start gap-3">
                        {taskInstructions.product.image && (
                          <img 
                            src={taskInstructions.product.image} 
                            alt={taskInstructions.product.name}
                            className="w-16 h-16 object-cover rounded-lg border"
                          />
                        )}
                        <div className="flex-1">
                          <h5 className="font-semibold text-sm">{taskInstructions.product.name}</h5>
                          <p className="text-xs text-muted-foreground mt-1 line-clamp-2">
                            {taskInstructions.product.description}
                          </p>
                          <div className="flex items-center gap-2 mt-2">
                            <span className="text-sm font-medium text-blue-600 dark:text-blue-400">
                              <CurrencyDisplay 
                                amount={taskInstructions.product.price || 0} 
                                userCurrencyCode={user?.currency_code}
                                currencySymbol={currencySymbol}
                              />
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  {/* Share Link */}
                  <div className="space-y-2">
                    <label className="block text-sm font-medium">Your Share Link</label>
                    <div className="flex gap-2 items-center">
                      <Input
                        value={taskInstructions.share_link}
                        readOnly
                        className="flex-1 bg-muted/30 text-xs sm:text-sm"
                        onFocus={e => e.target.select()}
                      />
                      <Button size="icon" variant="outline" onClick={handleCopyShareLink}>
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 16h8M8 12h8m-8-4h8m-2 8v2a2 2 0 002 2h4a2 2 0 002-2V6a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" /></svg>
                      </Button>
                    </div>
                    {taskInstructions.progress && (
                      <div className="text-xs text-muted-foreground mt-1">
                        Progress: {taskInstructions.progress.completed} / {taskInstructions.progress.needed} purchases
                      </div>
                    )}
                  </div>

                  {/* Instructions */}
                  <div className="p-3 bg-amber-50 dark:bg-amber-950/30 rounded-lg border border-amber-200 dark:border-amber-800">
                    <div className="flex items-start gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <div className="text-sm">
                        <p className="font-medium text-amber-800 dark:text-amber-200 mb-1">How to complete this task:</p>
                        <ul className="text-xs text-amber-700 dark:text-amber-300 space-y-1">
                          <li>• Share the link above on social media, messaging apps, or any platform</li>
                          <li>• When someone clicks your link and purchases the product, it counts towards your goal</li>
                          <li>• You need {taskInstructions.progress?.needed || 1} purchase(s) to complete this task</li>
                          <li>• Only purchases made through your unique link will be counted</li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </div>
          ) : (
            <div className="py-8 text-center text-destructive">Failed to load instructions.</div>
          )}
          <DialogFooter>
            <Button variant="outline" onClick={() => setShowTaskDialog(false)}>Close</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
      
      {/* Task Reward Dialog */}
      <Dialog open={showRewardDialog} onOpenChange={setShowRewardDialog}>
        <DialogContent className="max-w-lg w-full max-h-[80vh] flex flex-col">
          <DialogTitle>Task Reward</DialogTitle>
          {loadingReward ? (
            <div className="py-8 flex justify-center items-center">
              <span className="text-muted-foreground">Loading reward details...</span>
            </div>
          ) : taskReward ? (
            <div className="flex-1 overflow-y-auto space-y-4 pr-2">
              <div className="text-center">
                <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">
                  <Award className="h-8 w-8 text-white" />
                </div>
                <h3 className="text-lg font-semibold text-green-600">Congratulations!</h3>
                <p className="text-sm text-muted-foreground mt-1">
                  You've successfully completed this task
                </p>
              </div>
              
              <div className="space-y-3">
                <div className="p-4 bg-muted/30 rounded-lg">
                  <h4 className="font-medium mb-2">Your Reward</h4>
                  <p className="text-lg font-semibold text-green-600">
                    {taskReward.reward_details?.description || 'Reward details not available'}
                  </p>
                </div>
                
                {taskReward.reward_details?.type === 'coupon' && (
                  <div className="p-4 bg-blue-50 dark:bg-blue-950/30 rounded-lg">
                    <h4 className="font-medium mb-2">Coupon Code</h4>
                    {taskReward.reward_details?.coupon ? (
                      <div className="flex gap-2 items-center">
                        <code className="flex-1 bg-white dark:bg-gray-800 px-3 py-2 rounded border text-sm font-mono">
                          {taskReward.reward_details.coupon.code}
                        </code>
                        <Button 
                          size="sm" 
                          variant="outline"
                          onClick={() => {
                            navigator.clipboard.writeText(taskReward.reward_details.coupon.code);
                            toast.success('Coupon code copied!');
                          }}
                        >
                          Copy
                        </Button>
                      </div>
                    ) : (
                      <div className="text-sm text-muted-foreground">
                        <p>Your coupon code will be assigned soon. Please check back later or contact support.</p>
                      </div>
                    )}
                  </div>
                )}
                
                {taskReward.reward_details?.type === 'course_access' && taskReward.reward_details?.course && (
                  <div className="p-4 bg-purple-50 dark:bg-purple-950/30 rounded-lg">
                    <h4 className="font-medium mb-2">Course Access</h4>
                    <div className="space-y-3">
                      <div className="text-sm">
                        <p className="font-medium text-purple-700 dark:text-purple-300">
                          {taskReward.reward_details.course.title}
                        </p>
                        <p className="text-muted-foreground mt-1">
                          You have been granted free access to this paid course!
                        </p>
                      </div>
                      
                      <div className="bg-white dark:bg-gray-800 rounded-lg p-3 space-y-2">
                        <div className="flex justify-between text-sm">
                          <span className="text-muted-foreground">Original Tuition Fee:</span>
                          <span className="line-through text-red-500">
                            <CurrencyDisplay 
                              amount={taskReward.reward_details.course.price || 0} 
                              userCurrencyCode={user?.currency_code}
                              currencySymbol={currencySymbol}
                            />
                          </span>
                        </div>
                        <div className="flex justify-between text-sm">
                          <span className="text-muted-foreground">Original Enrollment Fee:</span>
                          <span className="line-through text-red-500">
                            <CurrencyDisplay 
                              amount={taskReward.reward_details.course.enrollment_fee || 0} 
                              userCurrencyCode={user?.currency_code}
                              currencySymbol={currencySymbol}
                            />
                          </span>
                        </div>
                        <div className="border-t pt-2">
                          <div className="flex justify-between text-sm font-medium">
                            <span className="text-green-600">Your Cost:</span>
                            <span className="text-green-600">
                              <CurrencyDisplay 
                                amount={0} 
                                userCurrencyCode={user?.currency_code}
                                currencySymbol={currencySymbol}
                              /> (Fully Covered)
                            </span>
                          </div>
                        </div>
                      </div>
                      
                                             {taskReward.claimed ? (
                         <div className="space-y-3">
                           <div className="bg-green-50 dark:bg-green-950/30 rounded-lg p-3 border border-green-200 dark:border-green-800">
                             <div className="flex items-center gap-2 text-green-700 dark:text-green-300">
                               <CheckCircle className="h-4 w-4" />
                               <span className="text-sm font-medium">Reward Claimed</span>
                             </div>
                             <p className="text-xs text-green-600 dark:text-green-400 mt-1">
                               Claimed on {taskReward.claimed_at ? new Date(taskReward.claimed_at).toLocaleDateString() : 'Unknown date'}
                             </p>
                           </div>
                           <Button 
                             size="sm" 
                             variant="outline"
                             className="w-full"
                             onClick={() => {
                               setShowRewardDialog(false);
                               navigate(`/dashboard/academy/course/${taskReward.reward_details.course.id}/manage`);
                             }}
                           >
                             <BookOpen className="h-4 w-4 mr-2" />
                             Go to Course
                           </Button>
                         </div>
                       ) : (
                         <Button 
                           size="sm" 
                           className="w-full bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white"
                           disabled={claimingCourseAccess}
                           onClick={async () => {
                             try {
                               setClaimingCourseAccess(true);
                               const result = await rewardService.claimCourseAccess(activeTask.id);
                               toast.success('Course access claimed successfully! You are now enrolled.');
                               setShowRewardDialog(false);
                               // Navigate to course management
                               navigate(result.course_management_url);
                             } catch (error: any) {
                               // Check if it's an axios error with response data
                               if (error.response?.data?.data?.already_enrolled) {
                                 // User is already enrolled, offer to go to course management
                                 toast.info('You are already enrolled in this course!');
                                 setShowRewardDialog(false);
                                 navigate(error.response.data.data.course_management_url);
                               } else if (error.response?.data?.data?.already_claimed) {
                                 // Reward already claimed
                                 toast.info('This reward has already been claimed.');
                                 setShowRewardDialog(false);
                                 navigate(error.response.data.data.course_management_url);
                               } else if (error.response?.data?.message) {
                                 // Show the specific error message from the backend
                                 toast.error(error.response.data.message);
                               } else {
                                 toast.error(error.message || 'Failed to claim course access.');
                               }
                             } finally {
                               setClaimingCourseAccess(false);
                             }
                           }}
                         >
                           {claimingCourseAccess ? (
                             <>
                               <div className="h-4 w-4 mr-2 animate-spin rounded-full border-2 border-white border-t-transparent" />
                               Claiming Access...
                             </>
                           ) : (
                             <>
                               <BookOpen className="h-4 w-4 mr-2" />
                               Claim Course Access
                             </>
                           )}
                         </Button>
                       )}
                    </div>
                  </div>
                                 )}
                 
                 {taskReward.reward_details?.type === 'cash' && (
                   <div className="p-4 bg-green-50 dark:bg-green-950/30 rounded-lg">
                     <h4 className="font-medium mb-2">Cash Reward</h4>
                     <div className="space-y-3">
                       <div className="text-sm">
                         <p className="font-medium text-green-700 dark:text-green-300">
                           <CurrencyDisplay 
                             amount={taskReward.reward_details.amount || 0} 
                             userCurrencyCode={user?.currency_code}
                             currencySymbol={currencySymbol}
                           />
                         </p>
                         <p className="text-muted-foreground mt-1">
                           This amount will be added to your wallet balance!
                         </p>
                       </div>
                       
                       {taskReward.claimed ? (
                         <div className="space-y-3">
                           <div className="bg-green-50 dark:bg-green-950/30 rounded-lg p-3 border border-green-200 dark:border-green-800">
                             <div className="flex items-center gap-2 text-green-700 dark:text-green-300">
                               <CheckCircle className="h-4 w-4" />
                               <span className="text-sm font-medium">Reward Claimed</span>
                             </div>
                             <p className="text-xs text-green-600 dark:text-green-400 mt-1">
                               Claimed on {taskReward.claimed_at ? new Date(taskReward.claimed_at).toLocaleDateString() : 'Unknown date'}
                             </p>
                           </div>
                           <Button 
                             size="sm" 
                             variant="outline"
                             className="w-full"
                             onClick={() => {
                               setShowRewardDialog(false);
                               navigate('/dashboard/wallet');
                             }}
                           >
                             <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                               <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                             </svg>
                             View Wallet
                           </Button>
                         </div>
                       ) : (
                         <Button 
                           size="sm" 
                           className="w-full bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white"
                           disabled={claimingCashReward}
                           onClick={async () => {
                             try {
                               setClaimingCashReward(true);
                               const result = await rewardService.claimCashReward(activeTask.id);
                               toast.success(`Cash reward claimed successfully! ${result.amount} added to your wallet.`);
                               setShowRewardDialog(false);
                               // Navigate to wallet
                               navigate('/dashboard/wallet');
                             } catch (error: any) {
                               // Check if it's an axios error with response data
                               if (error.response?.data?.data?.already_claimed) {
                                 // Reward already claimed
                                 toast.info('This reward has already been claimed.');
                                 setShowRewardDialog(false);
                                 navigate('/dashboard/wallet');
                               } else if (error.response?.data?.message) {
                                 // Show the specific error message from the backend
                                 toast.error(error.response.data.message);
                               } else {
                                 toast.error(error.message || 'Failed to claim cash reward.');
                               }
                             } finally {
                               setClaimingCashReward(false);
                             }
                           }}
                         >
                           {claimingCashReward ? (
                             <>
                               <div className="h-4 w-4 mr-2 animate-spin rounded-full border-2 border-white border-t-transparent" />
                               Claiming Cash...
                             </>
                           ) : (
                             <>
                               <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                               </svg>
                               Claim Cash Reward
                             </>
                           )}
                         </Button>
                       )}
                     </div>
                   </div>
                 )}

                 {taskReward.reward_details?.type === 'discount_code' && (
                   <div className="p-4 bg-blue-50 dark:bg-blue-950/30 rounded-lg">
                     <h4 className="font-medium mb-2">Discount Reward</h4>
                     <div className="space-y-3">
                       <div className="text-sm">
                         <p className="font-medium text-blue-700 dark:text-blue-300">
                           {taskReward.reward_details.percentage}% discount on {taskReward.reward_details.service}
                         </p>
                         <p className="text-muted-foreground mt-1">
                           Generate a discount slip that you can use for {taskReward.reward_details.service} services!
                         </p>
                       </div>
                       
                       {taskReward.claimed ? (
                         <div className="space-y-3">
                           <div className="bg-green-50 dark:bg-green-950/30 rounded-lg p-3 border border-green-200 dark:border-green-800">
                             <div className="flex items-center gap-2 text-green-700 dark:text-green-300">
                               <CheckCircle className="h-4 w-4" />
                               <span className="text-sm font-medium">Reward Claimed</span>
                             </div>
                             <p className="text-xs text-green-600 dark:text-green-400 mt-1">
                               Claimed on {taskReward.claimed_at ? new Date(taskReward.claimed_at).toLocaleDateString() : 'Unknown date'}
                             </p>
                           </div>
                           
                           {/* Show discount slip details if available */}
                           {taskReward.discount_slip && (
                             <div className="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                               <div className="space-y-2">
                                 <div className="flex items-center justify-between">
                                   <span className="text-sm font-medium">Discount Code:</span>
                                   <span className="text-sm font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                     {taskReward.discount_slip.discount_code}
                                   </span>
                                 </div>
                                 <div className="flex items-center justify-between">
                                   <span className="text-sm">Service:</span>
                                   <span className="text-sm">{taskReward.discount_slip.service_name}</span>
                                 </div>
                                 <div className="flex items-center justify-between">
                                   <span className="text-sm">Discount:</span>
                                   <span className="text-sm font-medium text-blue-600 dark:text-blue-400">
                                     {taskReward.discount_slip.discount_percent}%
                                   </span>
                                 </div>
                                 <div className="flex items-center justify-between">
                                   <span className="text-sm">Status:</span>
                                   <span className={`text-xs font-semibold px-2 py-1 rounded-full ${
                                     taskReward.discount_slip.is_used 
                                       ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' 
                                       : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                   }`}>
                                     {taskReward.discount_slip.is_used ? 'Used' : 'Active'}
                                   </span>
                                 </div>
                                 <div className="flex items-center justify-between">
                                   <span className="text-sm">Valid Until:</span>
                                   <span className="text-sm">
                                     {new Date(taskReward.discount_slip.expires_at).toLocaleDateString()}
                                   </span>
                                 </div>
                                 {taskReward.discount_slip.is_used && taskReward.discount_slip.used_at && (
                                   <div className="flex items-center justify-between">
                                     <span className="text-sm">Used On:</span>
                                     <span className="text-sm text-gray-600 dark:text-gray-400">
                                       {new Date(taskReward.discount_slip.used_at).toLocaleDateString()}
                                     </span>
                                   </div>
                                 )}
                               </div>
                               
                               <Button 
                                 size="sm" 
                                 variant="outline"
                                 className="w-full mt-3"
                                 disabled={downloadingSlip}
                                 onClick={async () => {
                                   try {
                                     setDownloadingSlip(true);
                                     const result = await rewardService.downloadDiscountSlip(activeTask.id);
                                     
                                     // Check if we're in a Capacitor environment (mobile)
                                     const isCapacitor = window.Capacitor?.isNative;
                                     
                                     if (isCapacitor) {
                                       // Mobile: Use Capacitor Filesystem
                                       const fileName = result.filename || `discount_slip_${Date.now()}.pdf`;
                                       
                                       const savedFile = await Filesystem.writeFile({
                                         path: `Download/${fileName}`,
                                         data: result.pdf_content,
                                         directory: Directory.ExternalStorage,
                                         recursive: true
                                       });
                                       
                                       toast.success('Discount slip downloaded successfully!', {
                                         description: 'File saved to Download folder'
                                       });
                                     } else {
                                       // Web: Use browser download
                                       const pdfBlob = new Blob([Uint8Array.from(atob(result.pdf_content), c => c.charCodeAt(0))], { type: 'application/pdf' });
                                       const url = URL.createObjectURL(pdfBlob);
                                       const link = document.createElement('a');
                                       link.href = url;
                                       link.download = result.filename || 'discount_slip.pdf';
                                       document.body.appendChild(link);
                                       link.click();
                                       document.body.removeChild(link);
                                       URL.revokeObjectURL(url);
                                       toast.success('Discount slip downloaded successfully!');
                                     }
                                   } catch (error: any) {
                                     console.error('Download error:', error);
                                     toast.error('Failed to download discount slip.');
                                   } finally {
                                     setDownloadingSlip(false);
                                   }
                                 }}
                               >
                                 {downloadingSlip ? (
                                   <>
                                     <div className="h-4 w-4 mr-2 animate-spin rounded-full border-2 border-current border-t-transparent" />
                                     Downloading...
                                   </>
                                 ) : (
                                   <>
                                     <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                       <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                     </svg>
                                     Download Slip
                                   </>
                                 )}
                               </Button>
                             </div>
                           )}
                         </div>
                       ) : (
                         <Button 
                           size="sm" 
                           className="w-full bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white"
                           disabled={claimingDiscountReward}
                           onClick={async () => {
                             try {
                               setClaimingDiscountReward(true);
                               const result = await rewardService.claimDiscountReward(activeTask.id);
                               toast.success('Discount reward claimed successfully! Your discount slip has been generated.');
                               setShowRewardDialog(false);
                               // Refresh the reward details to show the discount slip
                               const updatedReward = await rewardService.getTaskReward(activeTask.id);
                               setTaskReward(updatedReward);
                               setShowRewardDialog(true);
                             } catch (error: any) {
                               // Check if it's an axios error with response data
                               if (error.response?.data?.data?.already_claimed) {
                                 // Reward already claimed
                                 toast.info('This reward has already been claimed.');
                                 setShowRewardDialog(false);
                               } else if (error.response?.data?.message) {
                                 // Show the specific error message from the backend
                                 toast.error(error.response.data.message);
                               } else {
                                 toast.error(error.message || 'Failed to claim discount reward.');
                               }
                             } finally {
                               setClaimingDiscountReward(false);
                             }
                           }}
                         >
                           {claimingDiscountReward ? (
                             <>
                               <div className="h-4 w-4 mr-2 animate-spin rounded-full border-2 border-white border-t-transparent" />
                               Generating Slip...
                             </>
                           ) : (
                             <>
                               <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                               </svg>
                               Generate Discount Slip
                             </>
                           )}
                         </Button>
                       )}
                     </div>
                   </div>
                 )}
               </div>
 
             </div>
           ) : (
             <div className="py-8 text-center text-destructive">Failed to load reward details.</div>
           )}
          <DialogFooter>
            <Button variant="outline" onClick={() => setShowRewardDialog(false)}>Close</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}
