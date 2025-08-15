import React, { useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { motion } from 'framer-motion';
import { 
  ArrowLeft,
  Users, 
  Star, 
  Calendar, 
  CheckCircle, 
  XCircle, 
  Clock, 
  User,
  Award,
  TrendingUp,
  FileText,
  ChevronLeft,
  ChevronRight,
  Filter,
  Search
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ConfirmDialog } from '@/components/ui/confirm-dialog';
import { toast } from 'sonner';
import { gritService, type GritApplicationForBusiness } from '@/services/gritService';
import { cn, formatDate } from '@/lib/utils';

const GritApplications = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  
  const [currentPage, setCurrentPage] = useState(1);
  const [statusFilter, setStatusFilter] = useState<string>('all');
  const [searchTerm, setSearchTerm] = useState<string>('');
  const [confirmDialog, setConfirmDialog] = useState<{
    open: boolean;
    action: 'approve' | 'reject' | null;
    applicationId: string | null;
    professionalName: string | null;
  }>({
    open: false,
    action: null,
    applicationId: null,
    professionalName: null
  });

  const { data, isLoading, error } = useQuery({
    queryKey: ['grit-applications', id, currentPage, statusFilter, searchTerm],
    queryFn: () => gritService.getGritApplications(id!, currentPage, 10),
    enabled: !!id
  });

  const updateStatusMutation = useMutation({
    mutationFn: ({ applicationId, status }: { applicationId: string; status: 'approved' | 'rejected' }) =>
      gritService.updateApplicationStatus(applicationId, status),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['grit-applications', id] });
      queryClient.invalidateQueries({ queryKey: ['grit', id] });
      toast.success('Application status updated successfully');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to update application status');
    }
  });

  const handleUpdateStatus = (applicationId: string, status: 'approved' | 'rejected') => {
    updateStatusMutation.mutate({ applicationId, status });
  };

  const handleConfirmAction = (action: 'approve' | 'reject', applicationId: string, professionalName: string) => {
    setConfirmDialog({
      open: true,
      action,
      applicationId,
      professionalName
    });
  };

  const handleConfirmDialogConfirm = () => {
    if (confirmDialog.action && confirmDialog.applicationId) {
      const status = confirmDialog.action === 'approve' ? 'approved' : 'rejected';
      updateStatusMutation.mutate({ 
        applicationId: confirmDialog.applicationId, 
        status 
      });
      setConfirmDialog({ open: false, action: null, applicationId: null, professionalName: null });
    }
  };

  const handleProfessionalClick = (professionalId: string) => {
    navigate(`/dashboard/professionals/${professionalId}?grit_id=${id}`);
  };

  const getStatusConfig = (status: string) => {
    switch (status) {
      case 'pending':
        return {
          label: 'Pending',
          bgColor: 'bg-yellow-100 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200',
          icon: Clock
        };
      case 'approved':
        return {
          label: 'Approved',
          bgColor: 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-200',
          icon: CheckCircle
        };
      case 'rejected':
        return {
          label: 'Rejected',
          bgColor: 'bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-200',
          icon: XCircle
        };
      default:
        return {
          label: status,
          bgColor: 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200',
          icon: Clock
        };
    }
  };

  // Helper functions for safe formatting
  const formatRating = (rating: any): string => {
    if (rating === null || rating === undefined || rating === '') {
      return '0.0';
    }
    const numRating = typeof rating === 'number' ? rating : parseFloat(rating);
    if (isNaN(numRating)) {
      return '0.0';
    }
    return numRating.toFixed(1);
  };

  const formatCompletionRate = (rate: any): string => {
    if (rate === null || rate === undefined || rate === '') {
      return '0';
    }
    const numRate = typeof rate === 'number' ? rate : parseFloat(rate);
    if (isNaN(numRate)) {
      return '0';
    }
    return numRate.toString();
  };

  const formatProjectsCount = (count: any): string => {
    if (count === null || count === undefined || count === '') {
      return '0';
    }
    const numCount = typeof count === 'number' ? count : parseInt(count);
    if (isNaN(numCount)) {
      return '0';
    }
    return numCount.toString();
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="flex items-center gap-2">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          <span>Loading applications...</span>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <XCircle className="h-12 w-12 text-red-500 mx-auto mb-4" />
          <h2 className="text-xl font-semibold mb-2">Failed to Load Applications</h2>
          <p className="text-muted-foreground mb-4">Please try again later.</p>
          <Button onClick={() => navigate(`/dashboard/grits/${id}`)}>
            Back to GRIT Details
          </Button>
        </div>
      </div>
    );
  }

  const applications = data?.applications || [];
  const pagination = data?.pagination;
  const grit = data?.grit;

  return (
    <div className="min-h-screen bg-background">
      <div className="container mx-auto px-4 py-6">
        {/* Header */}
        <div className="mb-6">
          
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h1 className="text-2xl font-bold">Applications</h1>
              <p className="text-muted-foreground">
                {grit?.title} • {pagination?.total || 0} applications
              </p>
            </div>
          </div>
        </div>

        {/* Filters */}
        <Card className="mb-6">
          <CardContent className="p-4">
            <div className="flex flex-col sm:flex-row gap-4">
              <div className="flex-1">
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                  <Input
                    placeholder="Search by professional name..."
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
                  </SelectContent>
                </Select>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Applications List */}
        <div className="space-y-4">
          {applications.map((application: GritApplicationForBusiness) => {
            const statusConfig = getStatusConfig(application.status);
            
            return (
                             <motion.div
                 key={application.id}
                 initial={{ opacity: 0, y: 20 }}
                 animate={{ opacity: 1, y: 0 }}
                 className="border rounded-lg p-4 sm:p-6 hover:shadow-md transition-shadow bg-card cursor-pointer hover:bg-accent/50"
                 onClick={() => handleProfessionalClick(application.professional.id)}
               >
                                 <div className="flex flex-col lg:flex-row lg:items-start gap-4 lg:gap-6">
                   {/* Professional Info */}
                   <div className="flex-1 min-w-0">
                     <div className="flex items-start gap-3 sm:gap-4 mb-4">
                       <div className="w-12 h-12 sm:w-16 sm:h-16 bg-primary rounded-full flex items-center justify-center flex-shrink-0">
                         <span className="text-white font-semibold text-sm sm:text-lg">
                           {application.professional.name.charAt(0).toUpperCase()}
                         </span>
                       </div>
                       <div className="flex-1 min-w-0">
                         <div className="flex items-center justify-between gap-2 mb-2">
                           <h3 className="text-lg sm:text-xl font-semibold truncate">{application.professional.name}</h3>
                           <Badge className={cn("flex items-center gap-1 w-fit", statusConfig.bgColor)}>
                             {React.createElement(statusConfig.icon, { className: "w-3 h-3" })}
                             <span className="hidden sm:inline">{statusConfig.label}</span>
                             <span className="sm:hidden">{statusConfig.label}</span>
                           </Badge>
                         </div>
                         <p className="text-sm text-muted-foreground">Category: {application.professional.category}</p>
                       </div>
                     </div>

                    {/* Professional Stats */}
                    <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-4">
                      <div className="flex items-center gap-2">
                        <TrendingUp className="h-4 w-4 sm:h-5 sm:w-5 text-blue-500 flex-shrink-0" />
                        <div className="min-w-0">
                          <p className="text-xs sm:text-sm text-muted-foreground">Completion Rate</p>
                          <p className="font-medium text-sm sm:text-base">{formatCompletionRate(application.professional.completion_rate)}%</p>
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <Star className="h-4 w-4 sm:h-5 sm:w-5 text-yellow-500 flex-shrink-0" />
                        <div className="min-w-0">
                          <p className="text-xs sm:text-sm text-muted-foreground">Rating</p>
                          <p className="font-medium text-sm sm:text-base">{formatRating(application.professional.average_rating)}</p>
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <Award className="h-4 w-4 sm:h-5 sm:w-5 text-green-500 flex-shrink-0" />
                        <div className="min-w-0">
                          <p className="text-xs sm:text-sm text-muted-foreground">Projects</p>
                          <p className="font-medium text-sm sm:text-base">{formatProjectsCount(application.professional.total_projects_completed)}</p>
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <Calendar className="h-4 w-4 sm:h-5 sm:w-5 text-purple-500 flex-shrink-0" />
                        <div className="min-w-0">
                          <p className="text-xs sm:text-sm text-muted-foreground">Applied</p>
                          <p className="font-medium text-sm sm:text-base">{application.applied_at}</p>
                        </div>
                      </div>
                    </div>

                    {/* Qualifications */}
                    {application.professional.qualifications && (
                      <div className="mb-4">
                        <div className="flex items-center gap-2 mb-2">
                          <FileText className="h-4 w-4 text-muted-foreground" />
                          <p className="text-sm font-medium">Qualifications</p>
                        </div>
                        <div className="bg-muted p-3 rounded-lg">
                          {Array.isArray(application.professional.qualifications) ? (
                            <ul className="text-sm text-muted-foreground space-y-1">
                              {application.professional.qualifications.map((qual: string, index: number) => (
                                <li key={index} className="flex items-center gap-2">
                                  <span className="w-1 h-1 bg-muted-foreground rounded-full flex-shrink-0"></span>
                                  <span className="break-words">{qual}</span>
                                </li>
                              ))}
                            </ul>
                          ) : (
                            <p className="text-sm text-muted-foreground break-words">
                              {application.professional.qualifications}
                            </p>
                          )}
                        </div>
                      </div>
                    )}
                  </div>

                  {/* Action Buttons */}
                  {application.status === 'pending' && (
                    <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mt-4">
                      <Button
                        onClick={(e) => {
                          e.stopPropagation();
                          handleConfirmAction('approve', application.id, application.professional.name);
                        }}
                        disabled={updateStatusMutation.isPending}
                        className="bg-green-600 hover:bg-green-700 flex-1 sm:flex-none h-12 sm:h-10"
                      >
                        <CheckCircle className="h-5 w-5 mr-2" />
                        <span className="hidden sm:inline">Approve Application</span>
                        <span className="sm:hidden text-base font-medium">Approve</span>
                      </Button>
                      <Button
                        variant="outline"
                        onClick={(e) => {
                          e.stopPropagation();
                          handleConfirmAction('reject', application.id, application.professional.name);
                        }}
                        disabled={updateStatusMutation.isPending}
                        className="border-red-300 text-red-600 hover:bg-red-50 flex-1 sm:flex-none h-12 sm:h-10"
                      >
                        <XCircle className="h-5 w-5 mr-2" />
                        <span className="hidden sm:inline">Reject Application</span>
                        <span className="sm:hidden text-base font-medium">Reject</span>
                      </Button>
                    </div>
                  )}
                  
                </div>
              </motion.div>
            );
          })}
        </div>

        {/* Pagination */}
        {pagination && pagination.last_page > 1 && (
          <Card className="mt-6">
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
        )}

        {/* Empty State */}
        {applications.length === 0 && (
          <Card>
            <CardContent className="p-8">
              <div className="text-center">
                <Users className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                <h3 className="text-lg font-semibold mb-2">No Applications Found</h3>
                <p className="text-gray-500 mb-4">
                  {searchTerm || statusFilter !== 'all' 
                    ? 'Try adjusting your search or filter criteria.'
                    : 'No applications have been submitted for this GRIT yet.'
                  }
                </p>
                {(searchTerm || statusFilter !== 'all') && (
                  <Button
                    variant="outline"
                    onClick={() => {
                      setSearchTerm('');
                      setStatusFilter('all');
                    }}
                  >
                    Clear Filters
                  </Button>
                )}
              </div>
            </CardContent>
          </Card>
        )}

        {/* Confirmation Dialog */}
        <ConfirmDialog
          open={confirmDialog.open}
          onOpenChange={(open) => setConfirmDialog(prev => ({ ...prev, open }))}
          onConfirm={handleConfirmDialogConfirm}
          title={
            confirmDialog.action === 'approve' 
              ? 'Approve Application' 
              : 'Reject Application'
          }
          description={
            confirmDialog.action === 'approve'
              ? `Are you sure you want to approve ${confirmDialog.professionalName}'s application? This will automatically reject all other pending applications for this GRIT.`
              : `Are you sure you want to reject ${confirmDialog.professionalName}'s application? This action cannot be undone.`
          }
          actionLabel={
            confirmDialog.action === 'approve' ? 'Approve' : 'Reject'
          }
          variant={
            confirmDialog.action === 'approve' ? 'default' : 'destructive'
          }
        />
      </div>
    </div>
  );
};

export default GritApplications;
