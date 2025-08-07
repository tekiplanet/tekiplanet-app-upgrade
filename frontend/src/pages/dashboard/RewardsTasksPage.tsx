import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { rewardService } from '@/services/rewardService';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  Gift, Loader2, Trophy, Sparkles, Target, Clock, 
  CheckCircle, AlertCircle, PlayCircle, Zap, Star,
  TrendingUp, Award, BookOpen, Users, Calendar
} from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { cn } from '@/lib/utils';
import { Skeleton } from '@/components/ui/skeleton';
import { useNavigate } from 'react-router-dom';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';

export default function RewardsTasksPage() {
  const queryClient = useQueryClient();
  const navigate = useNavigate();
  const [isConverting, setIsConverting] = useState(false);
  const [showConfirmDialog, setShowConfirmDialog] = useState(false);

  // Fetch user tasks and rewards
  const { data, isLoading, refetch } = useQuery({
    queryKey: ['rewards-tasks'],
    queryFn: () => rewardService.getUserTasks({ page: 1, per_page: 2 }),
    retry: 2
  });

  const tasks = (data as any)?.tasks || [];
  const learnRewards = (data as any)?.learn_rewards ?? 0;

  // Get only the 2 most recent tasks for display
  const recentTasks = tasks;

  // Calculate stats
  const stats = {
    totalTasks: (data as any)?.stats?.total_tasks || 0,
    completedTasks: (data as any)?.stats?.completed_tasks || 0,
    activeTasks: ((data as any)?.stats?.assigned_tasks || 0) + ((data as any)?.stats?.in_progress_tasks || 0),
    completionRate: (data as any)?.stats?.total_tasks > 0 ? (((data as any)?.stats?.completed_tasks || 0) / (data as any)?.stats?.total_tasks) * 100 : 0
  };

  // Mutation for conversion
  const mutation = useMutation({
    mutationFn: rewardService.initiateConversion,
    onSuccess: (res) => {
      toast.success('Task assigned successfully!');
      queryClient.invalidateQueries({ queryKey: ['rewards-tasks'] });
      setShowConfirmDialog(false);
    },
    onError: (err: any) => {
      let errorMessage = err?.response?.data?.message || err?.message || 'Conversion failed';
      
      // Improve error messages for better user experience
      if (errorMessage.includes('No eligible conversion tasks')) {
        errorMessage = 'No eligible tasks available right now. Try again later or continue earning more learning rewards!';
      } else if (errorMessage.includes('Insufficient learning rewards')) {
        errorMessage = 'You need more learning rewards to convert. Continue learning to earn more points!';
      }
      
      toast.error(errorMessage);
      console.error('Conversion error:', err);
      setShowConfirmDialog(false);
    },
    onSettled: () => setIsConverting(false),
  });

  const handleConvert = () => {
    setShowConfirmDialog(true);
  };

  const handleConfirmConversion = () => {
    setIsConverting(true);
    mutation.mutate();
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

  if (isLoading) {
    return (
      <div className="min-h-screen bg-gradient-to-b from-background to-primary/5 p-2 sm:p-4 md:p-6 space-y-6">
        <Skeleton className="h-[200px] rounded-xl" />
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          {[1, 2, 3, 4].map((i) => (
            <Skeleton key={i} className="h-[120px] rounded-xl" />
          ))}
        </div>
        <div className="space-y-4">
          <Skeleton className="h-8 w-48" />
          <div className="grid gap-4 md:grid-cols-2">
            {[1, 2].map((i) => (
              <Skeleton key={i} className="h-[300px] rounded-xl" />
            ))}
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-b from-background to-primary/5 p-2 sm:p-4 md:p-6">
      {/* Hero Section */}
      <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="relative mb-4 sm:mb-6"
      >
        <Card className={cn(
          "relative overflow-hidden border-none bg-gradient-to-br backdrop-blur-xl",
          "hover:shadow-lg transition-all dark:shadow-none",
          "dark:bg-background/50",
          "from-purple-500/10 via-purple-500/5 to-transparent",
          "dark:from-purple-500/20 dark:via-purple-500/10 dark:to-transparent"
        )}>
          <CardContent className="p-3 sm:p-6">
            <div className="flex flex-col gap-4 sm:gap-6">
              {/* Header */}
              <div className="flex items-start justify-between gap-2">
                <div className="space-y-1 sm:space-y-2">
                  <motion.h1 
                    initial={{ opacity: 0, x: -20 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: 0.2 }}
                    className="text-lg sm:text-3xl font-bold flex items-center gap-2 sm:gap-3"
                  >
                    <div className="p-1.5 sm:p-2 rounded-xl bg-purple-500/20">
                      <Gift className="h-5 w-5 sm:h-6 sm:w-6 text-purple-500" />
                    </div>
                    <span className="truncate">Rewards & Tasks</span>
                  </motion.h1>
                  <motion.p 
                    initial={{ opacity: 0, x: -20 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: 0.3 }}
                    className="text-xs sm:text-base text-muted-foreground"
                  >
                    Convert your learning rewards into amazing benefits
                  </motion.p>
                </div>
                <motion.div
                  initial={{ opacity: 0, scale: 0.5 }}
                  animate={{ opacity: 1, scale: 1 }}
                  transition={{ delay: 0.4 }}
                  className="hidden sm:flex items-center justify-center w-10 h-10 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-purple-500/20 to-pink-500/20"
                >
                  <Sparkles className="h-6 w-6 sm:h-8 sm:w-8 text-purple-500" />
                </motion.div>
              </div>

              {/* Rewards Display */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <motion.div
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.5 }}
                  className="flex items-center gap-4 p-4 rounded-2xl bg-gradient-to-r from-blue-500/10 to-purple-500/10 border border-blue-500/20"
                >
                  <div className="p-3 rounded-xl bg-blue-500/20">
                    <Trophy className="h-6 w-6 text-blue-500" />
                  </div>
                  <div>
                    <p className="text-sm text-blue-500/70 font-medium">Learning Rewards</p>
                    <p className="text-2xl font-bold text-blue-500">
                      {learnRewards.toLocaleString('en-US')}
                    </p>
                  </div>
                </motion.div>

                <motion.div
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.6 }}
                  className="flex items-center gap-4 p-4 rounded-2xl bg-gradient-to-r from-green-500/10 to-emerald-500/10 border border-green-500/20"
                >
                  <div className="p-3 rounded-xl bg-green-500/20">
                    <Target className="h-6 w-6 text-green-500" />
                  </div>
                  <div>
                    <p className="text-sm text-green-500/70 font-medium">Completion Rate</p>
                    <p className="text-2xl font-bold text-green-500">
                      {stats.completionRate.toFixed(0)}%
                    </p>
                  </div>
                </motion.div>
              </div>

              {/* Convert Button */}
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.7 }}
                className="flex justify-center"
              >
                <Button 
                  onClick={handleConvert} 
                  disabled={isConverting || learnRewards < 100}
                  size="lg"
                  className="h-12 px-8 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300"
                >
                  {isConverting ? (
                    <div className="flex items-center gap-2">
                      <Loader2 className="animate-spin h-5 w-5" />
                      Converting...
                    </div>
                  ) : (
                    <div className="flex items-center gap-2">
                      <Zap className="h-5 w-5" />
                      Convert Rewards
                    </div>
                  )}
                </Button>
              </motion.div>
            </div>
          </CardContent>
        </Card>
      </motion.div>

      {/* Stats Grid */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-6">
        {[
          {
            title: "Total Tasks",
            value: stats.totalTasks,
            icon: <BookOpen className="h-5 w-5" />,
            color: "from-blue-500/10 via-blue-500/5 to-transparent",
            iconColor: "text-blue-500"
          },
          {
            title: "Completed",
            value: stats.completedTasks,
            icon: <CheckCircle className="h-5 w-5" />,
            color: "from-green-500/10 via-green-500/5 to-transparent",
            iconColor: "text-green-500"
          },
          {
            title: "Active",
            value: stats.activeTasks,
            icon: <PlayCircle className="h-5 w-5" />,
            color: "from-yellow-500/10 via-yellow-500/5 to-transparent",
            iconColor: "text-yellow-500"
          },
          {
            title: "Success Rate",
            value: `${stats.completionRate.toFixed(0)}%`,
            icon: <TrendingUp className="h-5 w-5" />,
            color: "from-purple-500/10 via-purple-500/5 to-transparent",
            iconColor: "text-purple-500"
          }
        ].map((stat, index) => (
          <motion.div
            key={stat.title}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: index * 0.1 }}
            whileHover={{ scale: 1.02 }}
            className="group"
          >
            <Card className={cn(
              "relative overflow-hidden border-none bg-gradient-to-br backdrop-blur-xl",
              "hover:shadow-lg transition-all dark:shadow-none rounded-2xl",
              "dark:bg-background/50",
              stat.color
            )}>
              <CardContent className="p-4">
                <div className="flex items-center gap-4">
                  <div className={cn(
                    "p-3 rounded-xl bg-background/50 dark:bg-background/80 backdrop-blur-xl",
                    "group-hover:bg-background/80 dark:group-hover:bg-background/60 transition-colors",
                    "shadow-sm dark:shadow-none"
                  )}>
                    <div className={stat.iconColor}>{stat.icon}</div>
                  </div>
                  <div className="space-y-1">
                    <p className="text-sm text-muted-foreground">{stat.title}</p>
                    <p className="text-xl font-bold tracking-tight">{stat.value}</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </motion.div>
        ))}
      </div>

      {/* Tasks Section */}
      <div className="space-y-4">
        <div className="flex items-center justify-between">
          <motion.h2 
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            className="text-xl font-bold flex items-center gap-2"
          >
            <Award className="h-5 w-5 text-primary" />
            Your Conversion Tasks
          </motion.h2>
        </div>

        {tasks.length === 0 ? (
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="text-center py-12"
          >
            <div className="flex flex-col items-center gap-4">
              <div className="p-4 rounded-full bg-muted/50">
                <Gift className="h-8 w-8 text-muted-foreground" />
              </div>
              <div className="space-y-2">
                <h3 className="text-lg font-semibold">No conversion tasks yet</h3>
                <p className="text-muted-foreground">
                  Click "Convert Rewards" to get started and earn amazing benefits!
                </p>
              </div>
            </div>
          </motion.div>
        ) : (
          <div className="grid gap-4 md:grid-cols-2">
            {recentTasks.map((task: any, index: number) => (
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
                      <div className="flex items-center justify-between text-sm text-muted-foreground">
                        <div className="flex items-center gap-2">
                          <Calendar className="h-4 w-4" />
                          <span>
                            {task.assigned_at ? new Date(task.assigned_at).toLocaleDateString() : 'Recently'}
                          </span>
                        </div>
                        {task.completed_at && (
                          <div className="flex items-center gap-2 text-green-600">
                            <CheckCircle className="h-4 w-4" />
                            <span>Completed</span>
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
            {tasks.length > 2 && (
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.8 }}
                className="flex justify-center"
              >
                <Button
                  onClick={() => navigate('/tasks')}
                  className="h-12 px-8 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300"
                >
                  <Users className="h-5 w-5 mr-2" />
                  View All Tasks
                </Button>
              </motion.div>
            )}
          </div>
        )}
      </div>

      <Dialog open={showConfirmDialog} onOpenChange={setShowConfirmDialog}>
        <DialogContent className="sm:max-w-md">
          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.2 }}
          >
            <DialogHeader>
              <DialogTitle className="flex items-center gap-2">
                <div className="p-2 rounded-lg bg-purple-500/10">
                  <Gift className="h-5 w-5 text-purple-500" />
                </div>
                Confirm Conversion
              </DialogTitle>
              <DialogDescription>
                <div className="flex flex-col gap-4">
                  <p className="mb-1">
                    Are you sure you want to convert your <span className="font-semibold text-blue-600">{learnRewards.toLocaleString('en-US')}</span> learning rewards into a task?
                  </p>
                  <div className="p-3 rounded-lg bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-500/20">
                    <div className="flex items-start gap-2">
                      <AlertCircle className="h-4 w-4 text-yellow-600 mt-0.5 flex-shrink-0" />
                      <p className="text-sm text-yellow-800 dark:text-yellow-200">
                        This action will deduct points from your balance and assign you a task to complete. The points cannot be refunded.
                      </p>
                    </div>
                  </div>
                </div>
              </DialogDescription>
            </DialogHeader>
            <DialogFooter className="flex flex-col gap-2 pt-4 sm:flex-row sm:gap-4 sm:pt-6">
              <Button 
                variant="outline" 
                onClick={() => setShowConfirmDialog(false)}
                disabled={isConverting}
                className="w-full sm:w-auto"
              >
                Cancel
              </Button>
              <Button 
                onClick={handleConfirmConversion} 
                disabled={isConverting}
                className="w-full sm:w-auto bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white"
              >
                {isConverting ? (
                  <div className="flex items-center gap-2">
                    <Loader2 className="animate-spin h-4 w-4" />
                    Converting...
                  </div>
                ) : (
                  <div className="flex items-center gap-2">
                    <Zap className="h-4 w-4" />
                    Convert Rewards
                  </div>
                )}
              </Button>
            </DialogFooter>
          </motion.div>
        </DialogContent>
      </Dialog>
    </div>
  );
}
