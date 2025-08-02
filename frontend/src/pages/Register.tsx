import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'sonner';
import { RegisterForm, RegisterFormData } from "@/components/auth/RegisterForm";
import { useAuthStore } from "@/store/useAuthStore";

const Register: React.FC = () => {
  const navigate = useNavigate();
  const isAuthenticated = useAuthStore((state) => state.isAuthenticated);
  const register = useAuthStore((state) => state.register);

  // Redirect to dashboard if already authenticated
  useEffect(() => {
    if (isAuthenticated) {
      navigate('/dashboard', { replace: true });
    }
  }, [isAuthenticated, navigate]);

  const handleRegister = async (data: RegisterFormData) => {
    try {
      console.log('🚀 Starting registration...');
      const response = await register(data);
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
    }
  };

  // Only render registration form if not authenticated
  if (isAuthenticated) {
    return null; // Prevents flashing of registration form before redirect
  }

  return (
    <div className="min-h-screen flex items-center justify-center p-4">
      <RegisterForm onSubmit={handleRegister} />
    </div>
  );
};

export default Register;