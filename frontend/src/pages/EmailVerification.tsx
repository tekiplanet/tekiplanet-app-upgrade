import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'sonner';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { useAuthStore } from "@/store/useAuthStore";
import { returnUrlUtils } from "@/utils/returnUrlUtils";

const EmailVerification = () => {
  const [code, setCode] = useState('');
  const [loading, setLoading] = useState(false);
  const [resendLoading, setResendLoading] = useState(false);
  const [countdown, setCountdown] = useState(0);
  const [isVerifying, setIsVerifying] = useState(false);
  const navigate = useNavigate();
  const authStore = useAuthStore();
  const isAuthenticated = useAuthStore((state) => state.isAuthenticated);
  const requiresVerification = useAuthStore((state) => state.requiresVerification);
  const user = useAuthStore((state) => state.user);

  useEffect(() => {
    const checkAuth = async () => {
      // Only redirect if explicitly not authenticated
      if (isAuthenticated === false && !localStorage.getItem('token')) {
        navigate('/login');
        return;
      }

      // Only redirect to dashboard if explicitly verified
      if (isAuthenticated && requiresVerification === false && !isVerifying) {
        returnUrlUtils.redirectToReturnUrlOrDashboard(navigate);
      }
    };

    checkAuth();
  }, [isAuthenticated, requiresVerification, navigate, isVerifying]);

  // Countdown timer effect
  useEffect(() => {
    if (countdown > 0) {
      const timer = setInterval(() => {
        setCountdown((prev) => prev - 1);
      }, 1000);
      return () => clearInterval(timer);
    }
  }, [countdown]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setIsVerifying(true);

    try {
      await authStore.verifyEmail(code);
      await authStore.initialize();
      toast.success('Email verified successfully');
      
      // Check for return URL first
      const returnUrl = localStorage.getItem('returnUrl');
      if (returnUrl) {
        localStorage.removeItem('returnUrl');
        console.log('🔗 EmailVerification: Redirecting to return URL:', returnUrl);
        // Use navigate for internal routes, window.location for external
        if (returnUrl.includes(window.location.origin)) {
          const url = new URL(returnUrl);
          // For hash routing, we need to handle the path differently
          if (url.hash) {
            const path = url.hash.substring(1); // Remove the # from the hash
            navigate(path, { replace: true });
          } else {
            navigate(url.pathname, { replace: true });
          }
        } else {
          window.location.href = returnUrl;
        }
        return;
      }
      
      // Check onboarding status after email verification
      try {
        const status = await authStore.checkOnboardingStatus();
        
        if (!status.is_complete) {
          // Redirect to onboarding if not complete
          navigate('/onboarding');
        } else {
          // Redirect to dashboard if onboarding is complete
          navigate('/dashboard');
        }
      } catch (error) {
        console.error('Failed to check onboarding status:', error);
        // Fallback to dashboard if onboarding check fails
        navigate('/dashboard');
      }
    } catch (error: any) {
      toast.error(error.message || 'Failed to verify email');
      setLoading(false);
    } finally {
      setIsVerifying(false);
    }
  };

  const handleResend = async () => {
    if (countdown > 0) return;
    
    setResendLoading(true);
    try {
      await authStore.resendVerification();
      toast.success('Verification email sent');
      // Clear the input field when resending
      setCode('');
      // Start countdown for 60 seconds
      setCountdown(60);
    } catch (error: any) {
      toast.error(error.message || 'Failed to resend verification email');
    } finally {
      setResendLoading(false);
    }
  };

  const handleUseAnotherAccount = () => {
    authStore.logout();
    toast.success('Logged out successfully');
    navigate('/login');
  };

  // Only show if authenticated (even without full user data)
  if (!isAuthenticated) {
    return null;
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-background p-4">
      <div className="w-full max-w-md">
        <div className="bg-card shadow-lg rounded-lg p-8">
          <h1 className="text-2xl font-bold text-center mb-2 text-foreground">
            Verify Your Email
          </h1>
          <p className="text-muted-foreground text-center mb-6">
            Please enter the verification code sent to {user?.email}
          </p>

          <form onSubmit={handleSubmit} className="space-y-6">
            <div className="space-y-2">
              <Input
                type="text"
                placeholder="Enter code"
                value={code}
                onChange={(e) => setCode(e.target.value.replace(/\D/g, '').slice(0, 6))}
                maxLength={6}
                className="text-center text-xl md:text-2xl tracking-[0.25em] md:tracking-[0.5em] font-mono px-2 md:px-4"
                disabled={loading}
              />
            </div>

            <Button 
              type="submit" 
              className="w-full"
              disabled={loading || code.length !== 6}
            >
              {loading ? 'Verifying...' : 'Verify Email'}
            </Button>

            <div className="text-center space-y-3">
              <div>
                <p className="text-sm text-gray-600 mb-2">
                  Didn't receive the code?
                </p>
                <Button
                  type="button"
                  variant="ghost"
                  onClick={handleResend}
                  disabled={resendLoading || countdown > 0}
                  className="text-primary hover:text-primary/90"
                >
                  {resendLoading ? (
                    'Sending...'
                  ) : countdown > 0 ? (
                    `Resend Code (${countdown}s)`
                  ) : (
                    'Resend Code'
                  )}
                </Button>
              </div>
              
              <div className="pt-2 border-t border-border">
                <Button
                  type="button"
                  variant="ghost"
                  onClick={handleUseAnotherAccount}
                  disabled={loading}
                  className="text-muted-foreground hover:text-foreground text-sm"
                >
                  Use Another Account
                </Button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};

export default EmailVerification; 