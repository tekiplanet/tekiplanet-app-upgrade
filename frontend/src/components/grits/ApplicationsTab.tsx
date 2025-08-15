import React, { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { motion } from 'framer-motion';
import { 
  Users, 
  Star, 
  Calendar, 
  CheckCircle, 
  XCircle, 
  Clock, 
  User,
  Mail,
  Award,
  TrendingUp,
  FileText
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { toast } from 'sonner';
import { gritService, type GritApplicationForBusiness } from '@/services/gritService';
import { cn, formatDate } from '@/lib/utils';

interface ApplicationsTabProps {
  gritId: string;
  applicationsCount: number;
  onViewAll?: () => void;
}

const ApplicationsTab: React.FC<ApplicationsTabProps> = ({ gritId, applicationsCount, onViewAll }) => {
  const queryClient = useQueryClient();
  const [selectedApplication, setSelectedApplication] = useState<string | null>(null);

  const { data, isLoading, error } = useQuery({
    queryKey: ['grit-applications', gritId, 'preview'],
    queryFn: () => gritService.getGritApplications(gritId, 1, 2), // Only get 2 most recent
    enabled: !!gritId
  });

  const updateStatusMutation = useMutation({
    mutationFn: ({ applicationId, status }: { applicationId: string; status: 'approved' | 'rejected' }) =>
      gritService.updateApplicationStatus(applicationId, status),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['grit-applications', gritId] });
      queryClient.invalidateQueries({ queryKey: ['grit', gritId] });
      toast.success('Application status updated successfully');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to update application status');
    }
  });

  const handleUpdateStatus = (applicationId: string, status: 'approved' | 'rejected') => {
    updateStatusMutation.mutate({ applicationId, status });
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

  // Helper function to safely format rating
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

  // Helper function to safely format completion rate
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

  // Helper function to safely format projects count
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
      <div className="flex items-center justify-center py-8">
        <div className="flex items-center gap-2">
          <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
          <span>Loading applications...</span>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="text-center py-8">
        <XCircle className="h-12 w-12 text-red-500 mx-auto mb-4" />
        <p className="text-red-600">Failed to load applications</p>
        <p className="text-sm text-gray-500 mt-2">Please try again later</p>
      </div>
    );
  }

  const applications = data?.applications || [];

  // Debug: Log the first application to see the data structure
  if (applications.length > 0) {
    console.log('First application data:', applications[0]);
  }

  if (applications.length === 0) {
    return (
      <div className="text-center py-8">
        <Users className="h-12 w-12 text-gray-400 mx-auto mb-4" />
        <p className="text-gray-500">No applications yet</p>
        <p className="text-sm text-gray-400 mt-2">
          Professionals will be able to apply once this GRIT is visible to them.
        </p>
      </div>
    );
  }

    return (
    <div className="space-y-4">
      {/* Header with View All button */}
      <div className="flex items-center justify-between">
        <div>
          <h3 className="text-lg font-semibold">Recent Applications</h3>
          <p className="text-sm text-gray-500">
            Showing {applications.length} of {applicationsCount} applications
          </p>
        </div>
        {applicationsCount > 2 && onViewAll && (
          <Button 
            variant="outline" 
            size="sm"
            onClick={onViewAll}
            className="text-primary hover:text-primary"
          >
            View All ({applicationsCount})
          </Button>
        )}
      </div>

      {/* Applications List - Compact Design */}
      <div className="space-y-3">
        {applications.map((application: GritApplicationForBusiness) => {
          const statusConfig = getStatusConfig(application.status);
          
          return (
            <motion.div
              key={application.id}
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              className="border rounded-lg p-3 hover:shadow-sm transition-shadow bg-white dark:bg-gray-800"
            >
              <div className="flex items-center gap-3">
                {/* Avatar */}
                <div className="w-10 h-10 bg-primary rounded-full flex items-center justify-center flex-shrink-0">
                  <span className="text-white font-semibold text-sm">
                    {application.professional.name.charAt(0).toUpperCase()}
                  </span>
                </div>

                {/* Main Content */}
                <div className="flex-1 min-w-0">
                  <div className="flex items-center justify-between mb-1">
                    <h4 className="font-medium text-sm truncate">
                      {application.professional.name}
                    </h4>
                    <Badge className={cn("text-xs", statusConfig.bgColor)}>
                      {statusConfig.label}
                    </Badge>
                  </div>
                  
                  <div className="flex items-center gap-4 text-xs text-gray-500 mb-2">
                    <span className="flex items-center gap-1">
                      <Star className="h-3 w-3 text-yellow-500" />
                      {formatRating(application.professional.average_rating)}
                    </span>
                    <span className="flex items-center gap-1">
                      <TrendingUp className="h-3 w-3 text-blue-500" />
                      {formatCompletionRate(application.professional.completion_rate)}%
                    </span>
                    <span className="flex items-center gap-1">
                      <Award className="h-3 w-3 text-green-500" />
                      {formatProjectsCount(application.professional.total_projects_completed)} projects
                    </span>
                  </div>

                  <div className="flex items-center justify-between">
                    <span className="text-xs text-gray-400">
                      Applied {application.applied_at}
                    </span>
                    
                    {application.status === 'pending' && (
                      <div className="flex gap-1">
                        <Button
                          size="sm"
                          variant="ghost"
                          onClick={() => handleUpdateStatus(application.id, 'approved')}
                          disabled={updateStatusMutation.isPending}
                          className="h-6 px-2 text-xs bg-green-50 text-green-700 hover:bg-green-100"
                        >
                          <CheckCircle className="h-3 w-3 mr-1" />
                          Approve
                        </Button>
                        <Button
                          size="sm"
                          variant="ghost"
                          onClick={() => handleUpdateStatus(application.id, 'rejected')}
                          disabled={updateStatusMutation.isPending}
                          className="h-6 px-2 text-xs bg-red-50 text-red-700 hover:bg-red-100"
                        >
                          <XCircle className="h-3 w-3 mr-1" />
                          Reject
                        </Button>
                      </div>
                    )}
                  </div>
                </div>
              </div>
            </motion.div>
          );
        })}
      </div>

      {/* Empty State */}
      {applications.length === 0 && (
        <div className="text-center py-6">
          <Users className="h-8 w-8 text-gray-400 mx-auto mb-2" />
          <p className="text-sm text-gray-500">No applications yet</p>
          <p className="text-xs text-gray-400 mt-1">
            Professionals will be able to apply once this GRIT is visible to them.
          </p>
        </div>
      )}
    </div>
  );
};

export default ApplicationsTab;
