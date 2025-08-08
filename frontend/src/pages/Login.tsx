import React from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'sonner';
import { LoginForm } from "@/components/auth/LoginForm";
import { useAuthStore } from "@/store/useAuthStore";
import { returnUrlUtils } from "@/utils/returnUrlUtils";

interface LoginFormData {
  login: string;
  password: string;
}

const Login = () => {
  const navigate = useNavigate();
  const authStore = useAuthStore();
  const isAuthenticated = useAuthStore((state) => state.isAuthenticated);
  const [isLoggingIn, setIsLoggingIn] = React.useState(false);

  // Redirect to dashboard if already authenticated
  React.useEffect(() => {
    console.log('🔗 Login: useEffect triggered', { isAuthenticated, requiresVerification: authStore.requiresVerification, isLoggingIn });
    if (isAuthenticated && !authStore.requiresVerification && !isLoggingIn) {
      console.log('🔗 Login: User is authenticated, checking for return URL');
      returnUrlUtils.redirectToReturnUrlOrDashboard(navigate);
    }
  }, [isAuthenticated, authStore.requiresVerification, navigate, isLoggingIn]);

  const handleLogin = async (data: LoginFormData) => {
    try {
      console.log('🔗 Login: Starting login process');
      setIsLoggingIn(true);
      const { login, password } = data;
      const response = await authStore.login(login, password);

      if (response.requires_verification) {
        console.log('🔗 Login: User requires verification');
        toast.info('Please verify your email address');
        navigate('/verify-email');
        return;
      }

      if (response.requires_2fa) {
        console.log('🔗 Login: User requires 2FA');
        // Just navigate to 2FA page, no dialog needed
        navigate('/two-factor-auth');
        return;
      }

      console.log('🔗 Login: Login successful, checking for return URL');
      toast.success('Login successful!');
      
      // Check for return URL and redirect there if available
      returnUrlUtils.redirectToReturnUrlOrDashboard(navigate);
    } catch (error: any) {
      console.error('Login error:', error);
      toast.error(error.message || 'Login failed');
    } finally {
      setIsLoggingIn(false);
    }
  };

  // Only render login form if not authenticated
  if (isAuthenticated && !authStore.requiresVerification) {
    return null;
  }

  return (
    <div className="min-h-screen bg-background relative flex items-center justify-center p-4">
      {/* Background gradient */}
      <div className="absolute inset-0 bg-gradient-to-b from-primary/5 to-background" />
      
      <LoginForm onSubmit={handleLogin} />
    </div>
  );
};

export default Login;