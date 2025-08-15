import React, { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { motion } from 'framer-motion';
import { useNavigate } from 'react-router-dom';
import { 
  Briefcase, 
  Calendar, 
  Clock, 
  XCircle,
  Loader2,
  ArrowRight,
  DollarSign,
  Search,
  Filter,
  ChevronLeft,
  ChevronRight,
  CheckCircle,
  AlertCircle,
  UserCheck,
  TrendingUp
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ScrollArea } from '@/components/ui/scroll-area';
import { toast } from 'sonner';
import { gritService, type GritApplication } from '@/services/gritService';
import { settingsService } from '@/services/settingsService';
import { formatCurrency, formatDate, formatShortDate, cn } from '@/lib/utils';
import WithdrawApplicationDialog from '@/components/grits/WithdrawApplicationDialog';

const MyApplications = () => {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  
  // State for pagination, search, and filters
  const [currentPage, setCurrentPage] = useState(1);
  const [statusFilter, setStatusFilter] = useState<string>('all');
  const [categoryFilter, setCategoryFilter] = useState<string>('all');
  const [searchTerm, setSearchTerm] = useState<string>('');

  const { data: settings } = useQuery<any>({
    queryKey: ['settings'],
    queryFn: settingsService.getAllSettings,
  });

  const { data, isLoading, error } = useQuery({
    queryKey: ['my-applications', currentPage, statusFilter, categoryFilter, searchTerm],
    queryFn: () => gritService.getMyGritApplications({
      page: currentPage,
      per_page: 8,
      search: searchTerm || undefined,
      status: statusFilter !== 'all' ? statusFilter : undefined,
      category: categoryFilter !== 'all' ? categoryFilter : undefined,
    }),
  });

  const applications = data?.applications || [];
  const pagination = data?.pagination;

  const [withdrawalDialog, setWithdrawalDialog] = useState<{
    isOpen: boolean;
    applicationId: string | null;
    gritTitle: string;
  }>({
    isOpen: false,
    applicationId: null,
    gritTitle: ''
  });

  const withdrawMutation = useMutation({
    mutationFn: (applicationId: string) => gritService.withdrawGritApplication(applicationId),
    onSuccess: () => {
      toast.success('Application withdrawn successfully');
      queryClient.invalidateQueries({ queryKey: ['my-applications'] });
      setWithdrawalDialog({ isOpen: false, applicationId: null, gritTitle: '' });
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to withdraw application');
    }
  });

  const handleWithdraw = (applicationId: string, gritTitle: string) => {
    setWithdrawalDialog({
      isOpen: true,
      applicationId,
      gritTitle
    });
  };

  const handleConfirmWithdraw = () => {
    if (withdrawalDialog.applicationId) {
      withdrawMutation.mutate(withdrawalDialog.applicationId);
    }
  };

  const getStatusConfig = (status: string) => {
    switch (status) {
      case 'pending':
        return {
          label: 'Pending',
          bgColor: 'bg-yellow-100 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200',
          icon: Clock,
          description: 'Under review'
        };
      case 'approved':
        return {
          label: 'Approved',
          bgColor: 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-200',
          icon: CheckCircle,
          description: 'Application accepted'
        };
      case 'rejected':
        return {
          label: 'Rejected',
          bgColor: 'bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-200',
          icon: XCircle,
          description: 'Not selected'
        };
      case 'withdrawn':
        return {
          label: 'Withdrawn',
          bgColor: 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200',
          icon: AlertCircle,
          description: 'Application withdrawn'
        };
      default:
        return {
          label: status,
          bgColor: 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200',
          icon: Clock,
          description: 'Unknown status'
        };
    }
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="flex items-center gap-2">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          <span>Loading applications...</span>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <XCircle className="h-12 w-12 text-red-500 mx-auto mb-4" />
          <h2 className="text-xl font-semibold mb-2">Failed to Load Applications</h2>
          <p className="text-muted-foreground mb-4">Please try again later.</p>
          <Button onClick={() => window.location.reload()}>
            Retry
          </Button>
        </div>
      </div>
    );
  }

  return (
    <ScrollArea className="h-[calc(100vh-4rem)]">
      <div className="container mx-auto p-4 space-y-6">
        {/* Header */}
        <motion.div 
          className="space-y-2"
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
        >
          <h1 className="text-2xl font-bold tracking-tight">My Applications</h1>
          <p className="text-muted-foreground">
            Track and manage your grit applications
          </p>
        </motion.div>

        {/* Filters */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.1 }}
        >
          <Card>
            <CardContent className="p-4">
              <div className="flex flex-col sm:flex-row gap-4">
                <div className="flex-1">
                  <div className="relative">
                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                      placeholder="Search by grit title..."
                      value={searchTerm}
                      onChange={(e) => setSearchTerm(e.target.value)}
                      className="pl-10"
                    />
                  </div>
                </div>
                <div className="w-full sm:w-48">
                  <Select value={statusFilter} onValueChange={setStatusFilter}>
                    <SelectTrigger>
                      <SelectValue placeholder="Filter by status" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">All Status</SelectItem>
                      <SelectItem value="pending">Pending</SelectItem>
                      <SelectItem value="approved">Approved</SelectItem>
                      <SelectItem value="rejected">Rejected</SelectItem>
                      <SelectItem value="withdrawn">Withdrawn</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
            </CardContent>
          </Card>
        </motion.div>

        {/* Applications Grid */}
        <div className="space-y-4">
          {applications.map((application: GritApplication, index: number) => {
            const statusConfig = getStatusConfig(application.status);
            
            return (
              <motion.div
                key={application.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: index * 0.1 }}
              >
                <Card className="relative overflow-hidden group hover:shadow-lg transition-all duration-300 border-l-4 border-l-primary">
                  {/* Status Indicator */}
                  <div 
                    className={cn(
                      "absolute top-0 right-0 w-0 h-0 border-l-[20px] border-l-transparent border-t-[20px]",
                      application.status === 'approved' ? "border-t-green-500" :
                      application.status === 'rejected' ? "border-t-red-500" :
                      application.status === 'withdrawn' ? "border-t-gray-500" :
                      "border-t-yellow-500"
                    )}
                  />

                  <CardContent className="p-6">
                    <div className="flex flex-col lg:flex-row lg:items-start gap-4 lg:gap-6">
                      {/* Main Content */}
                      <div className="flex-1 min-w-0">
                        <div className="space-y-3 mb-3">
                          {/* Title and Date */}
                          <div>
                            <h3 className="text-lg font-semibold group-hover:text-primary transition-colors break-words">
                              {application.grit.title}
                            </h3>
                            <p className="text-sm text-muted-foreground mt-1">
                              Applied on {formatDate(application.applied_at)}
                            </p>
                          </div>
                          
                          {/* Status and Category Badges */}
                          <div className="flex flex-wrap items-center gap-2">
                            <Badge className={cn("flex items-center gap-1", statusConfig.bgColor)}>
                              {React.createElement(statusConfig.icon, { className: "w-3 h-3" })}
                              {statusConfig.label}
                            </Badge>
                            <Badge variant="outline" className="bg-background">
                              {application.grit.category?.name || 'Unknown Category'}
                            </Badge>
                          </div>
                        </div>

                        {/* Stats Grid */}
                        <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
                          <div className="flex items-center gap-2 bg-muted/30 rounded-lg p-3">
                            <Calendar className="h-4 w-4 text-blue-500 flex-shrink-0" />
                            <div className="min-w-0">
                              <p className="text-xs text-muted-foreground">Deadline</p>
                              <p className="font-medium text-sm">{formatShortDate(application.grit.deadline)}</p>
                            </div>
                          </div>
                          <div className="flex items-center gap-2 bg-muted/30 rounded-lg p-3">
                            <DollarSign className="h-4 w-4 text-green-500 flex-shrink-0" />
                                                         <div className="min-w-0">
                               <p className="text-xs text-muted-foreground">Budget</p>
                               <p className="font-medium text-sm">
                                 {application.grit.owner_currency ? 
                                   `${application.grit.owner_currency} ${application.grit.budget?.toLocaleString()}` :
                                   formatCurrency(application.grit.budget, settings?.default_currency)
                                 }
                               </p>
                             </div>
                          </div>
                          <div className="flex items-center gap-2 bg-muted/30 rounded-lg p-3 sm:col-span-1 col-span-2">
                            <TrendingUp className="h-4 w-4 text-purple-500 flex-shrink-0" />
                            <div className="min-w-0">
                              <p className="text-xs text-muted-foreground">GRIT Status</p>
                              <p className="font-medium text-sm capitalize">{application.grit.status.replace('_', ' ')}</p>
                            </div>
                          </div>
                        </div>

                        {/* Status Description */}
                        <div className="bg-muted/20 rounded-lg p-3 mb-4">
                          <div className="flex items-center gap-2">
                            {React.createElement(statusConfig.icon, { className: "h-4 w-4 text-muted-foreground" })}
                            <p className="text-sm text-muted-foreground">{statusConfig.description}</p>
                          </div>
                        </div>
                      </div>

                      {/* Actions */}
                      <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 lg:flex-col lg:items-stretch">
                        <Button
                          variant="outline"
                          onClick={() => navigate(`/dashboard/grits/${application.grit.id}`)}
                          className="group/button"
                        >
                          View Details
                          <ArrowRight className="h-4 w-4 ml-2 group-hover/button:translate-x-1 transition-transform" />
                        </Button>

                        {application.status === 'pending' && (
                          <Button
                            variant="ghost"
                            onClick={() => handleWithdraw(application.id, application.grit.title)}
                            className="text-destructive hover:text-destructive hover:bg-destructive/10"
                          >
                            <XCircle className="h-4 w-4 mr-2" />
                            Withdraw
                          </Button>
                        )}

                        {application.status === 'approved' && (
                          <Button
                            onClick={() => navigate(`/dashboard/grits/${application.grit.id}/chat`)}
                            className="bg-blue-600 hover:bg-blue-700"
                          >
                            <UserCheck className="h-4 w-4 mr-2" />
                            Open Chat
                          </Button>
                        )}
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </motion.div>
            );
          })}
        </div>

        {/* Pagination */}
        {pagination && pagination.last_page > 1 && (
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.3 }}
          >
            <Card>
              <CardContent className="p-4">
                <div className="flex flex-col sm:flex-row items-center justify-between gap-4">
                  <div className="text-sm text-muted-foreground text-center sm:text-left">
                    Showing {pagination.from} to {pagination.to} of {pagination.total} applications
                  </div>
                  <div className="flex items-center gap-2">
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => setCurrentPage(currentPage - 1)}
                      disabled={currentPage === 1}
                    >
                      <ChevronLeft className="h-4 w-4 mr-1" />
                      <span className="hidden sm:inline">Previous</span>
                    </Button>
                    <div className="flex items-center gap-1">
                      {Array.from({ length: Math.min(pagination.last_page, 5) }, (_, i) => {
                        const page = i + 1;
                        if (pagination.last_page <= 5) {
                          return (
                            <Button
                              key={page}
                              variant={currentPage === page ? "default" : "outline"}
                              size="sm"
                              onClick={() => setCurrentPage(page)}
                              className="w-8 h-8 p-0"
                            >
                              {page}
                            </Button>
                          );
                        }
                        // Show first page, current page, and last page with ellipsis
                        if (page === 1 || page === pagination.last_page || (page >= currentPage - 1 && page <= currentPage + 1)) {
                          return (
                            <Button
                              key={page}
                              variant={currentPage === page ? "default" : "outline"}
                              size="sm"
                              onClick={() => setCurrentPage(page)}
                              className="w-8 h-8 p-0"
                            >
                              {page}
                            </Button>
                          );
                        }
                        if (page === 2 && currentPage > 3) {
                          return <span key="ellipsis1" className="px-2 text-muted-foreground">...</span>;
                        }
                        if (page === pagination.last_page - 1 && currentPage < pagination.last_page - 2) {
                          return <span key="ellipsis2" className="px-2 text-muted-foreground">...</span>;
                        }
                        return null;
                      })}
                    </div>
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => setCurrentPage(currentPage + 1)}
                      disabled={currentPage === pagination.last_page}
                    >
                      <span className="hidden sm:inline">Next</span>
                      <ChevronRight className="h-4 w-4 ml-1" />
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          </motion.div>
        )}

        {/* Empty State */}
        {applications.length === 0 && (
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.2 }}
          >
            <Card>
              <CardContent className="p-12">
                <div className="text-center">
                  <div className="relative w-24 h-24 mx-auto mb-6">
                    <div className="absolute inset-0 bg-primary/10 rounded-full animate-ping" />
                    <div className="relative flex items-center justify-center w-24 h-24 bg-primary/5 rounded-full">
                      <Briefcase className="h-12 w-12 text-primary/50" />
                    </div>
                  </div>
                  <h3 className="text-xl font-semibold mb-2">No Applications Found</h3>
                  <p className="text-muted-foreground mb-6">
                    {searchTerm || statusFilter !== 'all' 
                      ? 'Try adjusting your search or filter criteria.'
                      : 'You haven\'t applied to any grits yet. Start exploring opportunities!'
                    }
                  </p>
                  {(searchTerm || statusFilter !== 'all') ? (
                    <Button
                      variant="outline"
                      onClick={() => {
                        setSearchTerm('');
                        setStatusFilter('all');
                        setCategoryFilter('all');
                      }}
                    >
                      Clear Filters
                    </Button>
                  ) : (
                    <Button 
                      onClick={() => navigate('/dashboard/grits')}
                      className="gap-2"
                    >
                      Browse Grits
                      <ArrowRight className="h-4 w-4" />
                    </Button>
                  )}
                </div>
              </CardContent>
            </Card>
          </motion.div>
        )}
      </div>

      <WithdrawApplicationDialog
        isOpen={withdrawalDialog.isOpen}
        onClose={() => setWithdrawalDialog({ isOpen: false, applicationId: null, gritTitle: '' })}
        onConfirm={handleConfirmWithdraw}
        isLoading={withdrawMutation.isPending}
        gritTitle={withdrawalDialog.gritTitle}
      />
    </ScrollArea>
  );
};

export default MyApplications;