import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { GraduationCap, Building2, UserCheck, Check, Sparkles, Users, TrendingUp, Award } from 'lucide-react';

interface AccountTypeStepProps {
  onComplete: (accountType: 'student' | 'business' | 'professional') => void;
}

const accountTypes = [
  {
    id: 'student',
    title: 'Student',
    subtitle: 'Learning & Growth',
    description: 'Learn new skills, take courses, and grow your knowledge',
    icon: GraduationCap,
    color: 'from-blue-500 via-blue-600 to-blue-700',
    bgColor: 'bg-blue-50/50 dark:bg-blue-950/30',
    borderColor: 'border-blue-200 dark:border-blue-800',
    accentColor: 'text-blue-600 dark:text-blue-400',
    features: [
      { text: 'Access to all courses', icon: Sparkles },
      { text: 'Track learning progress', icon: TrendingUp },
      { text: 'Earn certificates', icon: Award },
      { text: 'Join student community', icon: Users }
    ]
  },
  {
    id: 'business',
    title: 'Business',
    subtitle: 'Scale & Manage',
    description: 'Manage your business, handle transactions, and scale operations',
    icon: Building2,
    color: 'from-emerald-500 via-emerald-600 to-emerald-700',
    bgColor: 'bg-emerald-50/50 dark:bg-emerald-950/30',
    borderColor: 'border-emerald-200 dark:border-emerald-800',
    accentColor: 'text-emerald-600 dark:text-emerald-400',
    features: [
      { text: 'Business profile management', icon: Building2 },
      { text: 'Customer relationship tools', icon: Users },
      { text: 'Invoice and payment tracking', icon: TrendingUp },
      { text: 'Business analytics', icon: Sparkles }
    ]
  },
  {
    id: 'professional',
    title: 'Professional',
    subtitle: 'Services & Growth',
    description: 'Offer services, manage clients, and grow your professional career',
    icon: UserCheck,
    color: 'from-violet-500 via-violet-600 to-violet-700',
    bgColor: 'bg-violet-50/50 dark:bg-violet-950/30',
    borderColor: 'border-violet-200 dark:border-violet-800',
    accentColor: 'text-violet-600 dark:text-violet-400',
    features: [
      { text: 'Professional profile showcase', icon: UserCheck },
      { text: 'Service management tools', icon: Sparkles },
      { text: 'Client communication', icon: Users },
      { text: 'Portfolio and reviews', icon: Award }
    ]
  }
];

const AccountTypeStep: React.FC<AccountTypeStepProps> = ({ onComplete }) => {
  const [selectedType, setSelectedType] = useState<'student' | 'business' | 'professional' | null>(null);
  const [loading, setLoading] = useState(false);

  const handleContinue = async () => {
    if (!selectedType) return;
    
    setLoading(true);
    try {
      await onComplete(selectedType);
    } catch (error) {
      setLoading(false);
    }
  };

  return (
    <div className="max-w-md mx-auto px-2">
      {/* Enhanced Header */}
      <div className="text-center mb-8">
        <motion.div
          initial={{ opacity: 0, scale: 0.9 }}
          animate={{ opacity: 1, scale: 1 }}
          className="mb-4"
        >
          <div className="w-12 h-12 mx-auto mb-3 rounded-full bg-gradient-to-r from-primary to-primary/80 flex items-center justify-center">
            <Sparkles className="h-6 w-6 text-white" />
          </div>
        </motion.div>
        
        <motion.h1
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="text-2xl font-bold text-foreground mb-2 bg-gradient-to-r from-foreground to-foreground/80 bg-clip-text"
        >
          Choose Your Path
        </motion.h1>
        <motion.p
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.1 }}
          className="text-base text-muted-foreground leading-relaxed"
        >
          Select the account type that best describes your journey with TekiPlanet
        </motion.p>
      </div>

      {/* Enhanced Account Type Cards */}
      <div className="space-y-4 mb-8">
        {accountTypes.map((type, index) => (
          <motion.div
            key={type.id}
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: index * 0.15 }}
            whileHover={{ scale: 1.02 }}
            whileTap={{ scale: 0.98 }}
          >
            <Card
              className={`cursor-pointer transition-all duration-300 hover:shadow-xl hover:shadow-primary/10 ${
                selectedType === type.id
                  ? `ring-2 ring-primary/50 shadow-lg shadow-primary/20 ${type.bgColor} ${type.borderColor} scale-105`
                  : 'hover:shadow-lg hover:shadow-primary/5'
              }`}
              onClick={() => setSelectedType(type.id as any)}
            >
              <CardContent className="p-6">
                <div className="flex items-start space-x-4">
                  {/* Enhanced Icon */}
                  <div className={`p-3 rounded-xl bg-gradient-to-r ${type.color} text-white shadow-lg flex-shrink-0`}>
                    <type.icon className="h-6 w-6" />
                  </div>

                  {/* Enhanced Content - No Truncation */}
                  <div className="flex-1 min-w-0">
                    <div className="flex items-start justify-between mb-2">
                      <div className="min-w-0 flex-1">
                        <h3 className="text-lg font-bold text-foreground mb-1">
                          {type.title}
                        </h3>
                        <p className={`text-sm font-medium ${type.accentColor} mb-2`}>
                          {type.subtitle}
                        </p>
                      </div>
                      <AnimatePresence>
                        {selectedType === type.id && (
                          <motion.div
                            initial={{ scale: 0, rotate: -180 }}
                            animate={{ scale: 1, rotate: 0 }}
                            exit={{ scale: 0, rotate: 180 }}
                            className="w-6 h-6 rounded-full bg-primary flex items-center justify-center shadow-lg flex-shrink-0 ml-2"
                          >
                            <Check className="h-4 w-4 text-primary-foreground" />
                          </motion.div>
                        )}
                      </AnimatePresence>
                    </div>
                    
                    <p className="text-sm text-muted-foreground mb-3 leading-relaxed">
                      {type.description}
                    </p>

                    {/* Compact Features - No Truncation */}
                    <div className="space-y-1.5">
                      {type.features.map((feature, featureIndex) => (
                        <motion.div 
                          key={featureIndex} 
                          className="flex items-center text-xs text-muted-foreground"
                          initial={{ opacity: 0, x: -10 }}
                          animate={{ opacity: 1, x: 0 }}
                          transition={{ delay: (index * 0.15) + (featureIndex * 0.05) }}
                        >
                          <feature.icon className="h-3 w-3 mr-2 text-primary flex-shrink-0" />
                          <span>{feature.text}</span>
                        </motion.div>
                      ))}
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </motion.div>
        ))}
      </div>

      {/* Enhanced Continue Button */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.6 }}
        className="space-y-3"
      >
        <Button
          onClick={handleContinue}
          disabled={!selectedType || loading}
          className="w-full h-12 text-base font-semibold bg-gradient-to-r from-primary to-primary/90 hover:from-primary/90 hover:to-primary shadow-lg hover:shadow-xl transition-all duration-300"
          size="lg"
        >
          {loading ? (
            <div className="flex items-center">
              <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2" />
              Setting up your account...
            </div>
          ) : (
            <div className="flex items-center">
              <span>Continue to Profile Setup</span>
              <motion.div
                initial={{ x: 0 }}
                animate={{ x: [0, 5, 0] }}
                transition={{ duration: 1.5, repeat: Infinity }}
                className="ml-2"
              >
                →
              </motion.div>
            </div>
          )}
        </Button>

        {/* Enhanced Note */}
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.7 }}
          className="text-center"
        >
          <p className="text-xs text-muted-foreground">
            💡 You can change your account type anytime in your settings
          </p>
        </motion.div>
      </motion.div>
    </div>
  );
};

export default AccountTypeStep; 