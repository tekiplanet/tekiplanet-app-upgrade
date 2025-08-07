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
import { useState } from 'react';
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

export default function TasksPage() {
  const navigate = useNavigate();
  const [statusFilter, setStatusFilter] = useState('all');
  const [sortBy, setSortBy] = useState('created_at');
  const [sortOrder, setSortOrder] = useState('desc');
  const [currentPage, setCurrentPage] = useState(1);
  const [perPage, setPerPage] = useState(10);

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
                              <span className="font-medium">75%</span>
                            </div>
                            <Progress value={75} className="h-2" />
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
                          >
                            <PlayCircle className="h-4 w-4 mr-2" />
                            Start Task
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
    </div>
  );
}
