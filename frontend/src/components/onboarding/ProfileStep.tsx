import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { User, Upload, X, Camera, Sparkles, Shield, CheckCircle } from 'lucide-react';

interface ProfileStepProps {
  onComplete: (profileData: { first_name: string; last_name: string; avatar?: File }) => void;
}

const ProfileStep: React.FC<ProfileStepProps> = ({ onComplete }) => {
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [avatar, setAvatar] = useState<File | null>(null);
  const [avatarPreview, setAvatarPreview] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const [focusedField, setFocusedField] = useState<string | null>(null);

  const handleAvatarChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (file) {
      setAvatar(file);
      const reader = new FileReader();
      reader.onload = (e) => {
        setAvatarPreview(e.target?.result as string);
      };
      reader.readAsDataURL(file);
    }
  };

  const handleRemoveAvatar = () => {
    setAvatar(null);
    setAvatarPreview(null);
  };

  const handleContinue = async () => {
    if (!firstName.trim() || !lastName.trim()) return;
    
    setLoading(true);
    try {
      await onComplete({
        first_name: firstName.trim(),
        last_name: lastName.trim(),
        avatar: avatar || undefined
      });
    } catch (error) {
      setLoading(false);
    }
  };

  const isFormValid = firstName.trim() && lastName.trim();

  return (
    <div className="max-w-lg mx-auto px-4">
      {/* Enhanced Header */}
      <div className="text-center mb-10">
        <motion.div
          initial={{ opacity: 0, scale: 0.9 }}
          animate={{ opacity: 1, scale: 1 }}
          className="mb-6"
        >
          <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-r from-primary to-primary/80 flex items-center justify-center">
            <User className="h-8 w-8 text-white" />
          </div>
        </motion.div>
        
        <motion.h1
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="text-3xl font-bold text-foreground mb-3 bg-gradient-to-r from-foreground to-foreground/80 bg-clip-text"
        >
          Complete Your Profile
        </motion.h1>
        <motion.p
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.1 }}
          className="text-lg text-muted-foreground leading-relaxed"
        >
          Tell us a bit about yourself to personalize your experience
        </motion.p>
      </div>

      {/* Enhanced Profile Form */}
      <Card className="mb-8 shadow-lg">
        <CardContent className="p-8 space-y-8">
          {/* Enhanced Avatar Upload */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.2 }}
            className="text-center"
          >
            <Label htmlFor="avatar" className="text-base font-semibold text-foreground mb-6 block">
              Profile Picture
              <span className="text-sm font-normal text-muted-foreground ml-2">(Optional)</span>
            </Label>
            
            <div className="flex flex-col items-center space-y-6">
              <AnimatePresence mode="wait">
                {avatarPreview ? (
                  <motion.div
                    key="avatar-preview"
                    initial={{ scale: 0.8, opacity: 0 }}
                    animate={{ scale: 1, opacity: 1 }}
                    exit={{ scale: 0.8, opacity: 0 }}
                    className="relative group"
                  >
                    <div className="relative">
                      <img
                        src={avatarPreview}
                        alt="Avatar preview"
                        className="w-32 h-32 rounded-full object-cover border-4 border-primary/20 shadow-xl"
                      />
                      <div className="absolute inset-0 rounded-full bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                        <Camera className="h-8 w-8 text-white" />
                      </div>
                    </div>
                    <Button
                      type="button"
                      variant="destructive"
                      size="sm"
                      className="absolute -top-2 -right-2 w-8 h-8 rounded-full p-0 shadow-lg hover:shadow-xl transition-all duration-200"
                      onClick={handleRemoveAvatar}
                    >
                      <X className="h-4 w-4" />
                    </Button>
                  </motion.div>
                ) : (
                  <motion.div
                    key="avatar-placeholder"
                    initial={{ scale: 0.8, opacity: 0 }}
                    animate={{ scale: 1, opacity: 1 }}
                    exit={{ scale: 0.8, opacity: 0 }}
                    className="relative group cursor-pointer"
                    onClick={() => document.getElementById('avatar')?.click()}
                  >
                    <div className="w-32 h-32 rounded-full bg-gradient-to-br from-muted to-muted/50 flex items-center justify-center border-2 border-dashed border-muted-foreground/30 group-hover:border-primary/50 transition-all duration-300 group-hover:shadow-lg">
                      <div className="text-center">
                        <User className="h-12 w-12 text-muted-foreground group-hover:text-primary transition-colors duration-300 mx-auto mb-2" />
                        <p className="text-xs text-muted-foreground group-hover:text-primary transition-colors duration-300">
                          Add Photo
                        </p>
                      </div>
                    </div>
                  </motion.div>
                )}
              </AnimatePresence>
              
              <div className="flex flex-col items-center space-y-3">
                <Input
                  id="avatar"
                  type="file"
                  accept="image/*"
                  onChange={handleAvatarChange}
                  className="hidden"
                />
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  onClick={() => document.getElementById('avatar')?.click()}
                  className="flex items-center space-x-2 px-6 py-2 rounded-full hover:shadow-md transition-all duration-200"
                >
                  <Upload className="h-4 w-4" />
                  <span>Upload Photo</span>
                </Button>
              </div>
            </div>
          </motion.div>

          {/* Enhanced Name Fields */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.3 }}
            className="space-y-6"
          >
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-3">
                <Label htmlFor="firstName" className="text-sm font-semibold text-foreground flex items-center">
                  <span>First Name</span>
                  <Shield className="h-3 w-3 ml-1 text-muted-foreground" />
                </Label>
                <div className="relative">
                  <Input
                    id="firstName"
                    type="text"
                    value={firstName}
                    onChange={(e) => setFirstName(e.target.value)}
                    onFocus={() => setFocusedField('firstName')}
                    onBlur={() => setFocusedField(null)}
                    placeholder="Enter your first name"
                    className={`w-full transition-all duration-200 ${
                      focusedField === 'firstName' ? 'ring-2 ring-primary/50 shadow-lg' : ''
                    }`}
                  />
                  {firstName.trim() && (
                    <motion.div
                      initial={{ scale: 0 }}
                      animate={{ scale: 1 }}
                      className="absolute right-3 top-1/2 transform -translate-y-1/2"
                    >
                      <CheckCircle className="h-4 w-4 text-green-500" />
                    </motion.div>
                  )}
                </div>
              </div>
              
              <div className="space-y-3">
                <Label htmlFor="lastName" className="text-sm font-semibold text-foreground flex items-center">
                  <span>Last Name</span>
                  <Shield className="h-3 w-3 ml-1 text-muted-foreground" />
                </Label>
                <div className="relative">
                  <Input
                    id="lastName"
                    type="text"
                    value={lastName}
                    onChange={(e) => setLastName(e.target.value)}
                    onFocus={() => setFocusedField('lastName')}
                    onBlur={() => setFocusedField(null)}
                    placeholder="Enter your last name"
                    className={`w-full transition-all duration-200 ${
                      focusedField === 'lastName' ? 'ring-2 ring-primary/50 shadow-lg' : ''
                    }`}
                  />
                  {lastName.trim() && (
                    <motion.div
                      initial={{ scale: 0 }}
                      animate={{ scale: 1 }}
                      className="absolute right-3 top-1/2 transform -translate-y-1/2"
                    >
                      <CheckCircle className="h-4 w-4 text-green-500" />
                    </motion.div>
                  )}
                </div>
              </div>
            </div>

            {/* Legal Name Notice */}
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ delay: 0.4 }}
              className="bg-blue-50 dark:bg-blue-950/30 rounded-lg p-4 border border-blue-200 dark:border-blue-800"
            >
              <div className="flex items-start space-x-3">
                <Shield className="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="text-sm font-medium text-blue-900 dark:text-blue-100 mb-1">
                    Legal Name Required
                  </p>
                  <p className="text-xs text-blue-700 dark:text-blue-300">
                    Please use your legal name as it may be required for KYC verification and official documents.
                  </p>
                </div>
              </div>
            </motion.div>
          </motion.div>
        </CardContent>
      </Card>

      {/* Enhanced Continue Button */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.5 }}
        className="space-y-4"
      >
        <Button
          onClick={handleContinue}
          disabled={!isFormValid || loading}
          className="w-full h-14 text-lg font-semibold bg-gradient-to-r from-primary to-primary/90 hover:from-primary/90 hover:to-primary shadow-lg hover:shadow-xl transition-all duration-300"
          size="lg"
        >
          {loading ? (
            <div className="flex items-center">
              <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-3" />
              Completing your profile...
            </div>
          ) : (
            <div className="flex items-center">
              <span>Complete Profile</span>
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
          transition={{ delay: 0.6 }}
          className="text-center"
        >
          <p className="text-sm text-muted-foreground">
            ✨ You can update your profile information anytime in your account settings
          </p>
        </motion.div>
      </motion.div>
    </div>
  );
};

export default ProfileStep; 