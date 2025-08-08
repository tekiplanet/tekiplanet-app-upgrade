import React, { useEffect, useState } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { toast } from 'sonner';
import { RegisterForm, RegisterFormData } from "@/components/auth/RegisterForm";
import { useAuthStore } from "@/store/useAuthStore";
import { returnUrlUtils } from "@/utils/returnUrlUtils";

const Register: React.FC = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const [isRegistering, setIsRegistering] = useState(false);
  
  // Parse URL parameters from the full URL to handle hash routing
  const fullUrl = window.location.href;
  const url = new URL(fullUrl);
  const ref = url.searchParams.get('ref');
  const task = url.searchParams.get('task');

  // Debug logging
  console.log('🔗 URL Debug:', {
    fullUrl: window.location.href,
    search: location.search,
    ref,
    task,
    hasRef: !!ref,
    hasTask: !!task
  });

  const isAuthenticated = useAuthStore((state) => state.isAuthenticated);
  const requiresVerification = useAuthStore((state) => state.requiresVerification);
  const register = useAuthStore((state) => state.register);

  // Redirect to dashboard if already authenticated and verified
  useEffect(() => {
    if (isAuthenticated && !requiresVerification && !isRegistering) {
      returnUrlUtils.redirectToReturnUrlOrDashboard(navigate);
    }
  }, [isAuthenticated, requiresVerification, navigate, isRegistering]);

  const handleRegister = async (data: RegisterFormData) => {
    try {
      console.log('🚀 Starting registration...');
      setIsRegistering(true);
      // Pass referral params to register function
      const response = await register({ ...data, ref, task });
      console.log('✅ Registration response:', response);

      if (response.requires_verification) {
        console.log('📧 User requires verification, navigating to verify-email');
        toast.success('Registration successful! Please check your email for verification.');
        
        // Add a small delay to ensure auth state is updated
        await new Promise(resolve => setTimeout(resolve, 100));
        
        navigate('/verify-email');
        return;
      }

      console.log('✅ Registration successful, navigating to login');
      toast.success('Registration successful! Please login.');
      navigate('/login');
    } catch (error: any) {
      console.error('❌ Registration error:', error);
      // Handle registration errors
      const errorMessage = error.message || 'Registration failed';
      toast.error(errorMessage);
      throw error; // Re-throw to be caught by the form
    } finally {
      setIsRegistering(false);
    }
  };

  // Only render registration form if not authenticated or if requires verification
  if (isAuthenticated && !requiresVerification) {
    return null; // Prevents flashing of registration form before redirect
  }

  return (
    <div className="min-h-screen bg-background relative flex items-center justify-center p-4">
      {/* Background gradient */}
      <div className="absolute inset-0 bg-gradient-to-b from-primary/5 to-background" />
      
      <RegisterForm onSubmit={handleRegister} />
    </div>
  );
};

export default Register;