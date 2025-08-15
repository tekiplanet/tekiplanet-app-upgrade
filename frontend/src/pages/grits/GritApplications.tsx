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
          <div className="flex items-center gap-4 mb-4">
            <Button
              variant="ghost"
              size="sm"
              onClick={() => navigate(`/dashboard/grits/${id}`)}
              className="flex items-center gap-2"
            >
              <ArrowLeft className="h-4 w-4" />
              Back to GRIT
            </Button>
          </div>
          
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
                 className="border rounded-lg p-6 hover:shadow-md transition-shadow bg-white dark:bg-gray-800 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                 onClick={() => handleProfessionalClick(application.professional.id)}
               >
                <div className="flex flex-col lg:flex-row lg:items-start gap-6">
                  {/* Professional Info */}
                  <div className="flex-1">
                    <div className="flex items-start gap-4 mb-4">
                      <div className="w-16 h-16 bg-primary rounded-full flex items-center justify-center flex-shrink-0">
                        <span className="text-white font-semibold text-lg">
                          {application.professional.name.charAt(0).toUpperCase()}
                        </span>
                      </div>
                      <div className="flex-1">
                        <div className="flex items-center justify-between mb-2">
                          <h3 className="text-xl font-semibold">{application.professional.name}</h3>
                          <Badge className={cn("flex items-center gap-1", statusConfig.bgColor)}>
                            {React.createElement(statusConfig.icon, { className: "w-3 h-3" })}
                            {statusConfig.label}
                          </Badge>
                        </div>
                        <p className="text-gray-600 dark:text-gray-400">{application.professional.email}</p>
                        <p className="text-sm text-gray-500">Category: {application.professional.category}</p>
                      </div>
                    </div>

                    {/* Professional Stats */}
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                      <div className="flex items-center gap-2">
                        <TrendingUp className="h-5 w-5 text-blue-500" />
                        <div>
                          <p className="text-sm text-gray-500">Completion Rate</p>
                          <p className="font-medium">{formatCompletionRate(application.professional.completion_rate)}%</p>
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <Star className="h-5 w-5 text-yellow-500" />
                        <div>
                          <p className="text-sm text-gray-500">Rating</p>
                          <p className="font-medium">{formatRating(application.professional.average_rating)}</p>
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <Award className="h-5 w-5 text-green-500" />
                        <div>
                          <p className="text-sm text-gray-500">Projects</p>
                          <p className="font-medium">{formatProjectsCount(application.professional.total_projects_completed)}</p>
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <Calendar className="h-5 w-5 text-purple-500" />
                        <div>
                          <p className="text-sm text-gray-500">Applied</p>
                          <p className="font-medium">{application.applied_at}</p>
                        </div>
                      </div>
                    </div>

                    {/* Qualifications */}
                    {application.professional.qualifications && (
                      <div className="mb-4">
                        <div className="flex items-center gap-2 mb-2">
                          <FileText className="h-4 w-4 text-gray-500" />
                          <p className="text-sm font-medium text-gray-700 dark:text-gray-300">Qualifications</p>
                        </div>
                        <div className="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                          {Array.isArray(application.professional.qualifications) ? (
                            <ul className="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                              {application.professional.qualifications.map((qual: string, index: number) => (
                                <li key={index} className="flex items-center gap-2">
                                  <span className="w-1 h-1 bg-gray-400 rounded-full"></span>
                                  {qual}
                                </li>
                              ))}
                            </ul>
                          ) : (
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                              {application.professional.qualifications}
                            </p>
                          )}
                        </div>
                      </div>
                    )}
                  </div>

                  
                </div>
              </motion.div>
            );
          })}
        </div>

        {/* Pagination */}
        {pagination && pagination.last_page > 1 && (
          <Card className="mt-6">
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <div className="text-sm text-gray-500">
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
                    Previous
                  </Button>
                  <div className="flex items-center gap-1">
                    {Array.from({ length: pagination.last_page }, (_, i) => i + 1).map((page) => (
                      <Button
                        key={page}
                        variant={currentPage === page ? "default" : "outline"}
                        size="sm"
                        onClick={() => setCurrentPage(page)}
                        className="w-8 h-8 p-0"
                      >
                        {page}
                      </Button>
                    ))}
                  </div>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setCurrentPage(currentPage + 1)}
                    disabled={currentPage === pagination.last_page}
                  >
                    Next
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
      </div>
    </div>
  );
};

export default GritApplications;
