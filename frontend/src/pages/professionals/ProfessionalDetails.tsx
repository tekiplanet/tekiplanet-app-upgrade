import React, { useState } from 'react';
import { useParams, useNavigate, useSearchParams } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { motion } from 'framer-motion';
import { 
  ArrowLeft,
  Star,
  Calendar,
  DollarSign,
  Users,
  Award,
  TrendingUp,
  Clock,
  CheckCircle,
  XCircle,
  User,
  Mail,
  Phone,
  MapPin,
  Briefcase,
  FileText,
  ExternalLink,
  Shield,
  BadgeCheck,
  MessageSquare,
  Eye,
  ThumbsUp,
  ThumbsDown
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Progress } from '@/components/ui/progress';
import { toast } from 'sonner';
import { gritService } from '@/services/gritService';
import { cn, formatCurrency } from '@/lib/utils';
import { useCurrencyFormat } from '@/lib/currency';

const ProfessionalDetails = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const queryClient = useQueryClient();
  
  const gritId = searchParams.get('grit_id');

  const { data, isLoading, error } = useQuery({
    queryKey: ['professional-details', id, gritId],
    queryFn: () => gritService.getProfessionalDetails(id!, gritId || undefined),
    enabled: !!id
  });

  const updateStatusMutation = useMutation({
    mutationFn: ({ applicationId, status }: { applicationId: string; status: 'approved' | 'rejected' }) =>
      gritService.updateApplicationStatusFromDetails(applicationId, status),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['professional-details', id, gritId] });
      queryClient.invalidateQueries({ queryKey: ['grit-applications', gritId] });
      toast.success('Application status updated successfully');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to update application status');
    }
  });

  const handleUpdateStatus = (applicationId: string, status: 'approved' | 'rejected') => {
    updateStatusMutation.mutate({ applicationId, status });
  };

  const formatRating = (rating: number) => {
    return rating.toFixed(1);
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

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="flex items-center gap-2">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          <span>Loading professional details...</span>
        </div>
      </div>
    );
  }

  if (error || !data) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <XCircle className="h-12 w-12 text-red-500 mx-auto mb-4" />
          <h2 className="text-xl font-semibold mb-2">Failed to Load Professional Details</h2>
          <p className="text-muted-foreground mb-4">Please try again later.</p>
          <Button onClick={() => navigate(-1)}>
            Go Back
          </Button>
        </div>
      </div>
    );
  }

  const { professional, statistics, reviews, recentProjects, currentApplication } = data;

  return (
    <div className="min-h-screen bg-background">
      <div className="container mx-auto px-4 py-6">
        {/* Header */}
        <div className="mb-6">
          <div className="flex items-center gap-4 mb-4">
            <Button
              variant="ghost"
              size="sm"
              onClick={() => navigate(-1)}
              className="flex items-center gap-2"
            >
              <ArrowLeft className="h-4 w-4" />
              Back
            </Button>
          </div>
          
          <div className="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div className="flex items-start gap-4">
              {/* Avatar */}
              <div className="w-20 h-20 bg-primary rounded-full flex items-center justify-center flex-shrink-0">
                <span className="text-white font-semibold text-2xl">
                  {professional.user.name.charAt(0).toUpperCase()}
                </span>
              </div>
              
              {/* Basic Info */}
              <div>
                <div className="flex items-center gap-3 mb-2">
                  <h1 className="text-2xl font-bold">{professional.user.name}</h1>
                  {professional.verified_at && (
                    <BadgeCheck className="h-5 w-5 text-blue-500" />
                  )}
                </div>
                <p className="text-lg text-muted-foreground mb-1">{professional.title}</p>
                <p className="text-sm text-muted-foreground">{professional.category.name}</p>
                
                {/* Contact Info */}
                <div className="flex items-center gap-4 mt-3 text-sm text-muted-foreground">
                  <span className="flex items-center gap-1">
                    <Mail className="h-4 w-4" />
                    {professional.user.email}
                  </span>
                  {professional.user.phone && (
                    <span className="flex items-center gap-1">
                      <Phone className="h-4 w-4" />
                      {professional.user.phone}
                    </span>
                  )}
                </div>
              </div>
            </div>

            {/* Application Actions */}
            {currentApplication && currentApplication.status === 'pending' && (
              <div className="flex gap-2">
                <Button
                  onClick={() => handleUpdateStatus(currentApplication.id, 'approved')}
                  disabled={updateStatusMutation.isPending}
                  className="bg-green-600 hover:bg-green-700"
                >
                  <CheckCircle className="h-4 w-4 mr-2" />
                  Approve Application
                </Button>
                <Button
                  variant="outline"
                  onClick={() => handleUpdateStatus(currentApplication.id, 'rejected')}
                  disabled={updateStatusMutation.isPending}
                  className="border-red-300 text-red-600 hover:bg-red-50"
                >
                  <XCircle className="h-4 w-4 mr-2" />
                  Reject Application
                </Button>
              </div>
            )}
          </div>
        </div>

        {/* Statistics Cards */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <Card>
            <CardContent className="p-4">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-blue-500/10 rounded-lg">
                  <Star className="h-5 w-5 text-blue-500" />
                </div>
                <div>
                  <p className="text-sm text-muted-foreground">Rating</p>
                  <p className="text-xl font-semibold">{formatRating(statistics.average_rating)}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-4">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-green-500/10 rounded-lg">
                  <TrendingUp className="h-5 w-5 text-green-500" />
                </div>
                <div>
                  <p className="text-sm text-muted-foreground">Completion Rate</p>
                  <p className="text-xl font-semibold">{statistics.completion_rate}%</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-4">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-purple-500/10 rounded-lg">
                  <Award className="h-5 w-5 text-purple-500" />
                </div>
                <div>
                  <p className="text-sm text-muted-foreground">Projects Completed</p>
                  <p className="text-xl font-semibold">{statistics.total_grits_completed}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-4">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-orange-500/10 rounded-lg">
                  <DollarSign className="h-5 w-5 text-orange-500" />
                </div>
                <div>
                  <p className="text-sm text-muted-foreground">Total Earnings</p>
                  <p className="text-xl font-semibold">
                    {formatCurrency(statistics.total_earnings, 'USD')}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Main Content */}
        <Tabs defaultValue="overview" className="space-y-6">
          <TabsList className="grid w-full grid-cols-4">
            <TabsTrigger value="overview">Overview</TabsTrigger>
            <TabsTrigger value="reviews">Reviews</TabsTrigger>
            <TabsTrigger value="projects">Recent Projects</TabsTrigger>
            <TabsTrigger value="portfolio">Portfolio</TabsTrigger>
          </TabsList>

          <TabsContent value="overview" className="space-y-6">
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              {/* Professional Info */}
              <div className="lg:col-span-2 space-y-6">
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <User className="h-5 w-5" />
                      Professional Information
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div>
                      <h3 className="font-semibold mb-2">Bio</h3>
                      <p className="text-muted-foreground">
                        {professional.bio || 'No bio available'}
                      </p>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <p className="text-sm text-muted-foreground">Experience</p>
                        <p className="font-medium">{professional.experience_years} years</p>
                      </div>
                      <div>
                        <p className="text-sm text-muted-foreground">Hourly Rate</p>
                        <p className="font-medium">
                          {formatCurrency(professional.hourly_rate, 'USD')}/hr
                        </p>
                      </div>
                      <div>
                        <p className="text-sm text-muted-foreground">Member Since</p>
                        <p className="font-medium">{professional.user.created_at}</p>
                      </div>
                      <div>
                        <p className="text-sm text-muted-foreground">Status</p>
                        <Badge variant={professional.status === 'active' ? 'default' : 'secondary'}>
                          {professional.status}
                        </Badge>
                      </div>
                    </div>

                    {professional.qualifications && (
                      <div>
                        <h3 className="font-semibold mb-2">Qualifications</h3>
                        <div className="bg-muted p-3 rounded-lg">
                          {Array.isArray(professional.qualifications) ? (
                            <ul className="space-y-1">
                              {professional.qualifications.map((qual: string, index: number) => (
                                <li key={index} className="flex items-center gap-2">
                                  <span className="w-1 h-1 bg-primary rounded-full"></span>
                                  {qual}
                                </li>
                              ))}
                            </ul>
                          ) : (
                            <p className="text-muted-foreground">{professional.qualifications}</p>
                          )}
                        </div>
                      </div>
                    )}
                  </CardContent>
                </Card>

                {/* Application Status */}
                {currentApplication && (
                  <Card>
                    <CardHeader>
                      <CardTitle className="flex items-center gap-2">
                        <Briefcase className="h-5 w-5" />
                        Application Status
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="flex items-center justify-between">
                        <div>
                          <p className="font-medium">{currentApplication.grit.title}</p>
                          <p className="text-sm text-muted-foreground">
                            Applied on {currentApplication.applied_at}
                          </p>
                        </div>
                        <Badge className={cn("", getStatusConfig(currentApplication.status).bgColor)}>
                          {currentApplication.status}
                        </Badge>
                      </div>
                    </CardContent>
                  </Card>
                )}
              </div>

              {/* Sidebar */}
              <div className="space-y-6">
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <Shield className="h-5 w-5" />
                      Verification
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-3">
                      <div className="flex items-center justify-between">
                        <span className="text-sm">Email Verified</span>
                        <CheckCircle className="h-4 w-4 text-green-500" />
                      </div>
                      <div className="flex items-center justify-between">
                        <span className="text-sm">Profile Verified</span>
                        {professional.verified_at ? (
                          <CheckCircle className="h-4 w-4 text-green-500" />
                        ) : (
                          <Clock className="h-4 w-4 text-yellow-500" />
                        )}
                      </div>
                      <div className="flex items-center justify-between">
                        <span className="text-sm">Active Projects</span>
                        <Badge variant="outline">{statistics.active_projects}</Badge>
                      </div>
                    </div>
                  </CardContent>
                </Card>

                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <MessageSquare className="h-5 w-5" />
                      Contact
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-3">
                      <Button variant="outline" className="w-full justify-start">
                        <Mail className="h-4 w-4 mr-2" />
                        Send Message
                      </Button>
                      {professional.user.phone && (
                        <Button variant="outline" className="w-full justify-start">
                          <Phone className="h-4 w-4 mr-2" />
                          Call
                        </Button>
                      )}
                    </div>
                  </CardContent>
                </Card>
              </div>
            </div>
          </TabsContent>

          <TabsContent value="reviews" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Star className="h-5 w-5" />
                  Client Reviews ({reviews.length})
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-6">
                  {reviews.map((review: any) => (
                    <div key={review.id} className="border-b pb-6 last:border-b-0">
                      <div className="flex items-start justify-between mb-3">
                        <div>
                          <h4 className="font-semibold">{review.business_name}</h4>
                          <p className="text-sm text-muted-foreground">{review.grit_title}</p>
                        </div>
                        <div className="flex items-center gap-2">
                          <div className="flex items-center gap-1">
                            {[...Array(5)].map((_, i) => (
                              <Star
                                key={i}
                                className={cn(
                                  "h-4 w-4",
                                  i < review.rating ? "text-yellow-500 fill-current" : "text-gray-300"
                                )}
                              />
                            ))}
                          </div>
                          <span className="text-sm text-muted-foreground">
                            {formatCurrency(review.project_amount, review.currency)}
                          </span>
                        </div>
                      </div>
                      <p className="text-muted-foreground mb-2">{review.comment}</p>
                      <p className="text-xs text-muted-foreground">{review.created_at}</p>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="projects" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Briefcase className="h-5 w-5" />
                  Recent Projects ({recentProjects.length})
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {recentProjects.map((project: any) => (
                    <div key={project.id} className="border rounded-lg p-4">
                      <div className="flex items-start justify-between mb-2">
                        <div>
                          <h4 className="font-semibold">{project.title}</h4>
                          <p className="text-sm text-muted-foreground">{project.business_name}</p>
                        </div>
                        <Badge variant="outline">{project.status}</Badge>
                      </div>
                      <div className="flex items-center justify-between text-sm text-muted-foreground">
                        <span>{project.category}</span>
                        <span>{formatCurrency(project.budget, project.currency)}</span>
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="portfolio" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <FileText className="h-5 w-5" />
                  Portfolio
                </CardTitle>
              </CardHeader>
              <CardContent>
                {professional.portfolio_items && professional.portfolio_items.length > 0 ? (
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {professional.portfolio_items.map((item: any, index: number) => (
                      <div key={index} className="border rounded-lg p-4">
                        <h4 className="font-semibold mb-2">{item.title || `Portfolio Item ${index + 1}`}</h4>
                        <p className="text-sm text-muted-foreground mb-2">
                          {item.description || 'No description available'}
                        </p>
                        {item.url && (
                          <Button variant="outline" size="sm" asChild>
                            <a href={item.url} target="_blank" rel="noopener noreferrer">
                              <ExternalLink className="h-4 w-4 mr-2" />
                              View Project
                            </a>
                          </Button>
                        )}
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <FileText className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                    <p className="text-muted-foreground">No portfolio items available</p>
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
};

export default ProfessionalDetails;
