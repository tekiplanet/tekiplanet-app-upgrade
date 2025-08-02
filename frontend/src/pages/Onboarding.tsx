import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import { toast } from 'sonner';
import { useAuthStore } from '@/store/useAuthStore';
import { onboardingService, OnboardingStatus } from '@/services/onboardingService';
import AccountTypeStep from '@/components/onboarding/AccountTypeStep';
import ProfileStep from '@/components/onboarding/ProfileStep';
import { Loader2 } from 'lucide-react';

const Onboarding: React.FC = () => {
  const navigate = useNavigate();
  const { user, updateUser } = useAuthStore();
  const [currentStep, setCurrentStep] = useState<'account_type' | 'profile' | 'complete'>('account_type');
  const [loading, setLoading] = useState(true);
  const [onboardingStatus, setOnboardingStatus] = useState<OnboardingStatus | null>(null);

  useEffect(() => {
    checkOnboardingStatus();
  }, []);

  const checkOnboardingStatus = async () => {
    try {
      setLoading(true);
      const status = await onboardingService.getOnboardingStatus();
      setOnboardingStatus(status);
      
      if (status.is_complete) {
        // Onboarding is complete, redirect to dashboard
        navigate('/dashboard', { replace: true });
        return;
      }
      
      setCurrentStep(status.current_step);
    } catch (error: any) {
      console.error('Failed to check onboarding status:', error);
      toast.error('Failed to load onboarding status');
    } finally {
      setLoading(false);
    }
  };

  const handleAccountTypeComplete = async (accountType: 'student' | 'business' | 'professional') => {
    try {
      const response = await onboardingService.updateAccountType({ account_type: accountType });
      
      // Update user in auth store
      if (response.user) {
        updateUser(response.user);
      }
      
      toast.success('Account type updated successfully');
      setCurrentStep('profile');
    } catch (error: any) {
      console.error('Failed to update account type:', error);
      toast.error(error.message || 'Failed to update account type');
    }
  };

  const handleProfileComplete = async (profileData: { first_name: string; last_name: string; avatar?: File }) => {
    try {
      const response = await onboardingService.updateProfile(profileData);
      
      // Update user in auth store
      if (response.user) {
        updateUser(response.user);
      }
      
      toast.success('Profile updated successfully');
      setCurrentStep('complete');
      
      // Redirect to dashboard after a short delay
      setTimeout(() => {
        navigate('/dashboard', { replace: true });
      }, 1500);
    } catch (error: any) {
      console.error('Failed to update profile:', error);
      toast.error(error.message || 'Failed to update profile');
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="text-center">
          <Loader2 className="h-8 w-8 animate-spin mx-auto mb-4 text-primary" />
          <p className="text-muted-foreground">Loading onboarding...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
      {/* Enhanced Progress Bar */}
      <div className="fixed top-0 left-0 right-0 z-50">
        <div className="h-2 bg-muted/50 backdrop-blur-sm">
          <motion.div
            className="h-full bg-gradient-to-r from-primary via-primary/90 to-primary/80 shadow-lg"
            initial={{ width: currentStep === 'account_type' ? '50%' : currentStep === 'profile' ? '100%' : '100%' }}
            animate={{ width: currentStep === 'account_type' ? '50%' : currentStep === 'profile' ? '100%' : '100%' }}
            transition={{ duration: 0.5, ease: "easeInOut" }}
          />
        </div>
      </div>

      {/* Enhanced Step Indicator */}
      <div className="pt-12 pb-6 px-4">
        <div className="max-w-lg mx-auto">
          <div className="flex items-center justify-between">
            <motion.div 
              className={`flex items-center transition-all duration-300 ${
                currentStep === 'account_type' ? 'text-primary scale-110' : 'text-muted-foreground'
              }`}
              whileHover={{ scale: 1.05 }}
            >
              <div className={`w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 ${
                currentStep === 'account_type' 
                  ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/30' 
                  : 'bg-muted text-muted-foreground'
              }`}>
                1
              </div>
              <span className="ml-3 text-sm font-semibold">Account Type</span>
            </motion.div>
            
            <div className="flex-1 h-px bg-gradient-to-r from-muted via-muted-foreground/30 to-muted mx-6" />
            
            <motion.div 
              className={`flex items-center transition-all duration-300 ${
                currentStep === 'profile' ? 'text-primary scale-110' : 'text-muted-foreground'
              }`}
              whileHover={{ scale: 1.05 }}
            >
              <div className={`w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 ${
                currentStep === 'profile' 
                  ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/30' 
                  : 'bg-muted text-muted-foreground'
              }`}>
                2
              </div>
              <span className="ml-3 text-sm font-semibold">Profile</span>
            </motion.div>
          </div>
        </div>
      </div>

      {/* Enhanced Step Content */}
      <div className="px-4 pb-8">
        <AnimatePresence mode="wait">
          {currentStep === 'account_type' && (
            <motion.div
              key="account-type"
              initial={{ opacity: 0, x: 30, scale: 0.95 }}
              animate={{ opacity: 1, x: 0, scale: 1 }}
              exit={{ opacity: 0, x: -30, scale: 0.95 }}
              transition={{ duration: 0.4, ease: "easeInOut" }}
            >
              <AccountTypeStep onComplete={handleAccountTypeComplete} />
            </motion.div>
          )}
          
          {currentStep === 'profile' && (
            <motion.div
              key="profile"
              initial={{ opacity: 0, x: 30, scale: 0.95 }}
              animate={{ opacity: 1, x: 0, scale: 1 }}
              exit={{ opacity: 0, x: -30, scale: 0.95 }}
              transition={{ duration: 0.4, ease: "easeInOut" }}
            >
              <ProfileStep onComplete={handleProfileComplete} />
            </motion.div>
          )}
        </AnimatePresence>
      </div>

      {/* Background Decoration */}
      <div className="fixed inset-0 -z-10 overflow-hidden">
        <div className="absolute -top-40 -right-40 w-80 h-80 bg-primary/5 rounded-full blur-3xl" />
        <div className="absolute -bottom-40 -left-40 w-80 h-80 bg-primary/5 rounded-full blur-3xl" />
      </div>
    </div>
  );
};

export default Onboarding; 