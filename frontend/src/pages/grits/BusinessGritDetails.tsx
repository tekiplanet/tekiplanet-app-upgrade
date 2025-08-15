import React, { useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { motion } from 'framer-motion';
import { 
  Calendar, 
  Briefcase,
  ArrowLeft,
  Edit,
  Users,
  DollarSign,
  Timer,
  CheckCircle,
  XCircle,
  AlertCircle,
  MessageSquare,
  Eye,
  MoreHorizontal,
  TrendingUp,
  Clock,
  UserCheck,
  FileText,
  Settings,
  Send
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Progress } from '@/components/ui/progress';
import { toast } from 'sonner';
import { gritService, type Grit } from '@/services/gritService';
import { cn, formatDate } from '@/lib/utils';
import { useCurrencyFormat } from '@/lib/currency';

import PaymentTab from '@/components/grits/PaymentTab';
import ApplicationsTab from '@/components/grits/ApplicationsTab';
import { settingsService } from '@/services/settingsService';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const container = {
  hidden: { opacity: 0 },
  show: {
    opacity: 1,
    transition: {
      staggerChildren: 0.1
    }
  }
};

const item = {
  hidden: { opacity: 0, y: 20 },
  show: { opacity: 1, y: 0 }
};

const BusinessGritDetails = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [activeTab, setActiveTab] = useState('overview');

  const { data, isLoading, error } = useQuery({
    queryKey: ['grit', id],
    queryFn: () => gritService.getGritDetails(id!),
    enabled: !!id
  });

  const { data: settings } = useQuery({
    queryKey: ['settings'],
    queryFn: settingsService.fetchSettings
  });

  const grit = data?.grit;

  // Currency formatting for budget using shared hook (no hardcoding)
  const { formattedAmount: formattedBudget } = useCurrencyFormat(
    grit?.owner_budget || grit?.budget,
    grit?.owner_currency
  );

  // Check if GRIT can be edited (no professional assigned, no applications, and status is open)
  const canEdit = grit && 
    !grit.assigned_professional_id && 
    grit.status === 'open' && 
    (grit.applications_count || 0) === 0;

  const getStatusConfig = (status: string, adminStatus: string) => {
    if (adminStatus === 'pending') {
      return {
        label: 'Pending Approval',
        bgColor: 'bg-yellow-100 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200',
        icon: Clock,
        description: 'Waiting for admin approval'
      };
    }
    if (adminStatus === 'rejected') {
      return {
        label: 'Rejected',
        bgColor: 'bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-200',
        icon: XCircle,
        description: 'Admin rejected this GRIT'
      };
    }

    switch (status) {
      case 'open':
        return {
          label: 'Open',
          bgColor: 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-200',
          icon: CheckCircle,
          description: 'Accepting applications'
        };
      case 'in_progress':
        return {
          label: 'In Progress',
          bgColor: 'bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200',
          icon: TrendingUp,
          description: 'Project is being worked on'
        };
      case 'completed':
        return {
          label: 'Completed',
          bgColor: 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200',
          icon: CheckCircle,
          description: 'Project completed'
        };
      case 'disputed':
        return {
          label: 'Disputed',
          bgColor: 'bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-200',
          icon: AlertCircle,
          description: 'Dispute raised'
        };
      default:
        return {
          label: status,
          bgColor: 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200',
          icon: Settings,
          description: 'Unknown status'
        };
    }
  };

  const handleEdit = () => {
    if (canEdit) {
      navigate(`/dashboard/grits/${id}/edit`);
    } else {
      if (grit?.assigned_professional_id) {
        toast.error('This GRIT cannot be edited because a professional has been assigned to it.');
      } else if ((grit?.applications_count || 0) > 0) {
        toast.error('This GRIT cannot be edited because professionals have already applied. Editing would be unfair to applicants.');
      } else {
        toast.error('This GRIT cannot be edited. It may not be in an editable state.');
      }
    }
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="flex items-center gap-2">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          <span>Loading GRIT details...</span>
        </div>
      </div>
    );
  }

  if (error || !grit) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <AlertCircle className="h-12 w-12 text-red-500 mx-auto mb-4" />
          <h2 className="text-xl font-semibold mb-2">GRIT Not Found</h2>
          <p className="text-muted-foreground mb-4">The GRIT you're looking for doesn't exist or you don't have permission to view it.</p>
          <Button onClick={() => navigate('/dashboard/grits/mine')}>
            Back to My GRITs
          </Button>
        </div>
      </div>
    );
  }

  const statusConfig = getStatusConfig(grit.status, grit.admin_approval_status);

  return (
    <div className="min-h-screen bg-background">
      <div className="container mx-auto px-4 py-6">
        {/* Header */}
        <motion.div 
          className="mb-6"
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
        >
          {/* Title Section - Mobile Optimized */}
          <div className="space-y-4">
            {/* Title, Category, and Status */}
            <div className="space-y-3">
              <h1 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-gray-100 break-words">
                {grit.title}
              </h1>
              <div className="flex flex-wrap items-center gap-2">
                <Badge variant="outline">{grit.category?.name}</Badge>
                <div className={cn(
                  "flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium",
                  statusConfig.bgColor
                )}>
                  {React.createElement(statusConfig.icon, { className: "w-4 h-4" })}
                  {statusConfig.label}
                </div>
              </div>
            </div>

            {/* Action Buttons - Moved below title on mobile */}
            <div className="flex items-center gap-2">
              {canEdit && (
                <Button onClick={handleEdit} className="flex items-center gap-2">
                  <Edit className="h-4 w-4" />
                  Edit GRIT
                </Button>
              )}
              
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="outline" size="sm">
                    <MoreHorizontal className="h-4 w-4" />
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                  {canEdit && (
                    <DropdownMenuItem onClick={handleEdit}>
                      <Edit className="h-4 w-4 mr-2" />
                      Edit GRIT
                    </DropdownMenuItem>
                  )}
                  <DropdownMenuItem onClick={() => navigate(`/dashboard/grits/${id}/applications`)}>
                    <Users className="h-4 w-4 mr-2" />
                    View Applications
                  </DropdownMenuItem>
                  <DropdownMenuItem onClick={() => navigate(`/dashboard/grits/${id}/chat`)}>
                    <MessageSquare className="h-4 w-4 mr-2" />
                    Open Chat
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>
          </div>
        </motion.div>

        {/* Main Content */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Left Column - Main Content */}
          <div className="lg:col-span-2">
            <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
              <TabsList className="grid w-full grid-cols-3">
                <TabsTrigger value="overview">Overview</TabsTrigger>
                <TabsTrigger value="applications" className="relative">
                  Applications
                  {grit.applications_count > 0 && (
                    <Badge 
                      variant="secondary" 
                      className="absolute -top-1 -right-1 h-4 w-4 p-0 text-xs flex items-center justify-center rounded-full"
                    >
                      {grit.applications_count}
                    </Badge>
                  )}
                </TabsTrigger>
                <TabsTrigger value="payments">Payments</TabsTrigger>
              </TabsList>

              <TabsContent value="overview" className="mt-6">
                <motion.div
                  variants={container}
                  initial="hidden"
                  animate="show"
                  className="space-y-6"
                >
                  {/* Description */}
                  <Card>
                    <CardHeader>
                      <CardTitle className="flex items-center gap-2">
                        <FileText className="h-5 w-5" />
                        Description
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <p className="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                        {grit.description}
                      </p>
                    </CardContent>
                  </Card>

                  {/* Requirements */}
                  {grit.requirements && (
                    <Card>
                      <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                          <Settings className="h-5 w-5" />
                          Requirements & Skills
                        </CardTitle>
                      </CardHeader>
                      <CardContent>
                        <p className="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                          {grit.requirements}
                        </p>
                      </CardContent>
                    </Card>
                  )}

                  {/* Status Information */}
                  <Card>
                    <CardHeader>
                      <CardTitle className="flex items-center gap-2">
                        <AlertCircle className="h-5 w-5" />
                        Status Information
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="space-y-4">
                        <div>
                          <p className="text-sm text-gray-500 dark:text-gray-400">Current Status</p>
                          <p className="font-medium">{statusConfig.description}</p>
                        </div>
                        
                        {grit.admin_approval_status === 'pending' && (
                          <div className="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <div className="flex items-center gap-2">
                              <Clock className="h-5 w-5 text-yellow-600" />
                              <p className="text-yellow-800 dark:text-yellow-200">
                                This GRIT is pending admin approval. It will be visible to professionals once approved.
                              </p>
                            </div>
                          </div>
                        )}

                        {grit.admin_approval_status === 'rejected' && (
                          <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                            <div className="flex items-center gap-2">
                              <XCircle className="h-5 w-5 text-red-600" />
                              <p className="text-red-800 dark:text-red-200">
                                This GRIT was rejected by admin. You may need to make changes and resubmit.
                              </p>
                            </div>
                          </div>
                        )}

                        {!canEdit && grit.assigned_professional_id && (
                          <div className="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div className="flex items-center gap-2">
                              <UserCheck className="h-5 w-5 text-gray-600 dark:text-gray-400" />
                              <p className="text-gray-800 dark:text-gray-200">
                                A professional has been assigned to this GRIT. It can no longer be edited.
                              </p>
                            </div>
                          </div>
                        )}
                      </div>
                    </CardContent>
                  </Card>
                </motion.div>
              </TabsContent>

              <TabsContent value="applications" className="mt-6">
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <Users className="h-5 w-5" />
                      Applications ({grit.applications_count || 0})
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <ApplicationsTab 
                      gritId={grit.id} 
                      applicationsCount={grit.applications_count || 0} 
                      onViewAll={() => navigate(`/dashboard/grits/${grit.id}/applications`)}
                    />
                  </CardContent>
                </Card>
              </TabsContent>



              <TabsContent value="payments" className="mt-6">
                <Card>
                  <CardContent className="p-0">
                    <PaymentTab 
                      payments={grit.payments || []} 
                      currency={grit.owner_currency} 
                    />
                  </CardContent>
                </Card>
              </TabsContent>
            </Tabs>
          </div>

          {/* Right Column - Sidebar */}
          <div className="space-y-6">
            {/* Budget & Timeline */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <DollarSign className="h-5 w-5" />
                  Budget & Timeline
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <p className="text-sm text-gray-500 dark:text-gray-400">Budget</p>
                  <p className="text-2xl font-bold">{formattedBudget}</p>
                </div>
                
                <div>
                  <p className="text-sm text-gray-500 dark:text-gray-400">Deadline</p>
                  <p className="font-medium">{formatDate(grit.deadline)}</p>
                </div>

                <div>
                  <p className="text-sm text-gray-500 dark:text-gray-400">Created</p>
                  <p className="font-medium">{formatDate(grit.created_at)}</p>
                </div>
              </CardContent>
            </Card>

            {/* Assigned Professional */}
            {grit.assigned_professional && (
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <UserCheck className="h-5 w-5" />
                    Assigned Professional
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                      <span className="text-white font-semibold">
                        {grit.assigned_professional.name?.charAt(0).toUpperCase() || '?'}
                      </span>
                    </div>
                    <div>
                      <p className="font-medium">{grit.assigned_professional.name || 'Unknown Professional'}</p>
                      <p className="text-sm text-gray-500">Professional</p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            )}

            {/* Quick Actions */}
            <Card>
              <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                {canEdit && (
                  <Button onClick={handleEdit} className="w-full justify-start">
                    <Edit className="h-4 w-4 mr-2" />
                    Edit GRIT
                  </Button>
                )}
                
                <Button 
                  variant="outline" 
                  className="w-full justify-start"
                  onClick={() => navigate(`/dashboard/grits/${id}/applications`)}
                >
                  <Users className="h-4 w-4 mr-2" />
                  View Applications
                </Button>
                
                <Button 
                  variant="outline" 
                  className="w-full justify-start"
                  onClick={() => navigate(`/dashboard/grits/${id}/chat`)}
                >
                  <MessageSquare className="h-4 w-4 mr-2" />
                  Open Chat
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  );
};

export default BusinessGritDetails;
