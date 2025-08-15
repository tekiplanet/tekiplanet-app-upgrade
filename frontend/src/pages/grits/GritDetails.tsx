import React from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { motion } from 'framer-motion';
import { 
  Calendar, 
  Briefcase,
  ArrowLeft,
  Send,
  Loader2,
  DollarSign,
  Timer,
  UserCheck,
  Users
} from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Progress } from '@/components/ui/progress';
import { toast } from 'sonner';
import { gritService, type Grit } from '@/services/gritService';
import { cn, formatCurrency } from '@/lib/utils';
import ApplyGritDialog from '@/components/grits/ApplyGritDialog';

import PaymentTab from '@/components/grits/PaymentTab';
import { settingsService } from '@/services/settingsService';

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

const GritDetails = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  const [isApplyDialogOpen, setIsApplyDialogOpen] = React.useState(false);

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

  const applyMutation = useMutation({
    mutationFn: () => gritService.applyForGrit(id!),
    onSuccess: () => {
      toast.success('Application submitted successfully');
      queryClient.invalidateQueries({ queryKey: ['grit', id] });
    },
    onError: (err: any) => {
      toast.error(err.response?.data?.message || 'Failed to submit application');
    }
  });

  const { data: profileData } = useQuery({
    queryKey: ['professional-profile'],
    queryFn: gritService.checkProfessionalProfile
  });

  const getApplicationStatus = (grit: Grit) => {
    if (!profileData?.has_profile) {
      return {
        can_apply: false,
        reason: 'You need to create a professional profile to apply for Grits'
      };
    }

    if (!profileData?.profile || profileData.profile.status !== 'active') {
      return {
        can_apply: false,
        reason: profileData?.profile?.status === 'inactive' 
          ? 'Your professional profile is inactive. Please activate it to apply.'
          : profileData?.profile?.status === 'suspended'
          ? 'Your professional profile is suspended. Please contact support.'
          : 'Your professional profile must be active to apply for Grits'
      };
    }

    if (profileData.profile.category_id !== grit.category.id) {
      return {
        can_apply: false,
        reason: 'This Grit is for a different professional category'
      };
    }

    if (grit.assigned_professional_id === profileData.profile.id) {
      return {
        can_apply: false,
        reason: 'Grit assigned to you. Please complete within the time frame'
      };
    }

    if (grit.application_status) {
      return {
        can_apply: false,
        reason: grit.application_status === 'pending' ? 'Your application is under review' :
               grit.application_status === 'approved' ? 'Your application has been approved' :
               grit.application_status === 'rejected' ? 'Your application was not successful' :
               'You have withdrawn your application'
      };
    }

    const currentDate = new Date();
    const deadlineDate = new Date(grit.deadline);
    if (deadlineDate < currentDate) {
      return {
        can_apply: false,
        reason: 'The application deadline has passed'
      };
    }

    if (grit.status !== 'open' || grit.assigned_professional_id) {
      return {
        can_apply: false,
        reason: grit.assigned_professional_id 
          ? 'A professional has already been assigned to this Grit'
          : 'This Grit is no longer accepting applications'
      };
    }

    return {
      can_apply: true,
      reason: null
    };
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <Loader2 className="h-8 w-8 animate-spin text-primary" />
      </div>
    );
  }

  if (error || !grit) {
    return (
      <div className="text-center py-12">
        <h2 className="text-xl font-semibold">Grit not found</h2>
        <Button 
          variant="link" 
          onClick={() => navigate('/dashboard/grits')}
          className="mt-4"
        >
          Back to Grits
        </Button>
      </div>
    );
  }

  const daysRemaining = Math.max(0, Math.ceil(
    (new Date(grit.deadline).getTime() - new Date().getTime()) / (1000 * 3600 * 24)
  ));

  const applicationStatus = getApplicationStatus(grit);

  return (
    <ScrollArea className="h-[calc(100vh-4rem)]">
      <motion.div 
        variants={container}
        initial="hidden"
        animate="show"
        className="container mx-auto px-4 py-4 md:py-6 space-y-4 max-w-5xl"
      >
        <motion.div variants={item} className="relative">
          <div className="absolute inset-0 bg-gradient-to-br from-primary/10 via-primary/5 to-transparent rounded-xl" />
          <div className="relative p-4 md:p-6">
            <div className="flex items-center gap-3 mb-4">
              <Button
                variant="ghost"
                size="icon"
                onClick={() => navigate(-1)}
                className="h-8 w-8 rounded-full hover:bg-background/80"
              >
                <ArrowLeft className="h-4 w-4" />
              </Button>
              <Badge variant="secondary" className="bg-background/50 backdrop-blur-sm">
                <Briefcase className="h-3 w-3 mr-1" />
                {grit.category.name}
              </Badge>
            </div>

            <h1 className="text-xl md:text-2xl lg:text-3xl font-bold mb-4">
              {grit.title}
            </h1>

            <div className="bg-background/50 backdrop-blur-sm rounded-xl p-4 space-y-3">
              <div className="flex flex-col gap-2">
                {grit.application_status && (
                  <div className="flex items-center gap-2">
                    <Badge 
                      variant={
                        grit.application_status === 'approved' ? 'success' :
                        grit.application_status === 'rejected' ? 'destructive' :
                        'secondary'
                      }
                      className="px-2.5 py-0.5 text-xs font-medium"
                    >
                      {grit.application_status.toUpperCase()}
                    </Badge>
                  </div>
                )}
                {!applicationStatus.can_apply && (
                  <span className="text-sm text-primary font-medium">
                    {applicationStatus.reason}
                  </span>
                )}
              </div>

              <Button 
                size="lg"
                onClick={() => setIsApplyDialogOpen(true)}
                disabled={!applicationStatus.can_apply || applyMutation.isPending}
                className={cn(
                  "w-full h-12 rounded-xl text-sm font-medium transition-all",
                  applicationStatus.can_apply 
                    ? "bg-primary text-primary-foreground hover:bg-primary/90" 
                    : grit.application_status === 'approved'
                      ? "bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100 hover:bg-green-200 dark:hover:bg-green-700"
                      : "bg-muted text-muted-foreground"
                )}
              >
                {applyMutation.isPending ? (
                  <>
                    <Loader2 className="h-5 w-5 mr-2 animate-spin" />
                    Submitting Application...
                  </>
                ) : (
                  <>
                    <UserCheck className="h-5 w-5 mr-2" />
                    {applicationStatus.can_apply ? 'Apply for Grit' : (
                      grit.application_status === 'pending' ? 'Application Pending' :
                      grit.application_status === 'approved' ? 'Application Approved' :
                      grit.application_status === 'rejected' ? 'Application Rejected' :
                      'Cannot Apply'
                    )}
                  </>
                )}
              </Button>
            </div>
          </div>
        </motion.div>

        <motion.div variants={item} className="grid grid-cols-2 lg:grid-cols-4 gap-3">
          <Card className="relative overflow-hidden group">
            <div className="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent group-hover:from-primary/10 transition-colors" />
            <CardContent className="p-3 h-full">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-primary/10 rounded-lg group-hover:scale-110 transition-transform shrink-0">
                  <Timer className="h-4 w-4 text-primary" />
                </div>
                <div className="min-w-0">
                  <p className="text-xs text-muted-foreground">Time Left</p>
                  <p className="font-semibold text-sm truncate">{daysRemaining} days</p>
                </div>
              </div>
              <Progress 
                value={Math.max(0, Math.min(100, (daysRemaining / 30) * 100))} 
                className="mt-2 h-1"
              />
            </CardContent>
          </Card>

          <Card className="relative overflow-hidden group">
            <div className="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent group-hover:from-blue-500/10 transition-colors" />
            <CardContent className="p-3 h-full">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-blue-500/10 rounded-lg group-hover:scale-110 transition-transform shrink-0">
                  <Calendar className="h-4 w-4 text-blue-500" />
                </div>
                <div className="min-w-0">
                  <p className="text-xs text-muted-foreground">Deadline</p>
                  <p className="font-semibold text-sm truncate">
                    {new Date(grit.deadline).toLocaleDateString()}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="relative overflow-hidden group">
            <div className="absolute inset-0 bg-gradient-to-br from-green-500/5 to-transparent group-hover:from-green-500/10 transition-colors" />
            <CardContent className="p-3 h-full">
              <div className="flex flex-col gap-2">
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-green-500/10 rounded-lg group-hover:scale-110 transition-transform shrink-0">
                    <DollarSign className="h-4 w-4 text-green-500" />
                  </div>
                  <div>
                    <p className="text-xs text-muted-foreground">Budget</p>
                  </div>
                </div>
                <p className="font-semibold text-sm px-2">
                  {formatCurrency(grit.professional_budget, grit.currency)}
                </p>
              </div>
            </CardContent>
          </Card>

          <Card className="relative overflow-hidden group">
            <div className="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-transparent group-hover:from-purple-500/10 transition-colors" />
            <CardContent className="p-3 h-full">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-purple-500/10 rounded-lg group-hover:scale-110 transition-transform shrink-0">
                  <Users className="h-4 w-4 text-purple-500" />
                </div>
                <div className="min-w-0">
                  <p className="text-xs text-muted-foreground">Applications</p>
                  <p className="font-semibold text-sm truncate">
                    {grit.applications_count}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>
        </motion.div>

        <motion.div variants={item}>
          <Tabs defaultValue="details" className="space-y-4">
            <TabsList className="w-full justify-start h-11 p-1 bg-muted/50 backdrop-blur-sm">
              <TabsTrigger value="details" className="flex items-center gap-2 rounded-lg">
                <Briefcase className="h-4 w-4" />
                Details
              </TabsTrigger>
              {grit.application_status === 'approved' && (
                <TabsTrigger value="payments" className="flex items-center gap-2 rounded-lg">
                  <DollarSign className="h-4 w-4" />
                  Payments
                </TabsTrigger>
              )}
            </TabsList>

            <TabsContent value="details" className="space-y-4">
              <Card>
                <CardContent className="p-4 md:p-6 prose dark:prose-invert max-w-none">
                  <div dangerouslySetInnerHTML={{ __html: grit.description }} />
                </CardContent>
              </Card>
            </TabsContent>

            {grit.application_status === 'approved' && (
              <TabsContent value="payments">
                <PaymentTab 
                  payments={grit.payments || []} 
                  currency={grit.currency || 'USD'} 
                />
              </TabsContent>
            )}
          </Tabs>
        </motion.div>
      </motion.div>

      <ApplyGritDialog
        isOpen={isApplyDialogOpen}
        onClose={() => setIsApplyDialogOpen(false)}
        onConfirm={() => {
          applyMutation.mutate();
          setIsApplyDialogOpen(false);
        }}
        isLoading={applyMutation.isPending}
        gritTitle={grit.title}
      />
    </ScrollArea>
  );
};

export default GritDetails;