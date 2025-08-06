import { useState, useEffect } from 'react';
import { lessonService } from '../services/lessonService';
import { useAuthStore } from '../store/useAuthStore';

export interface LessonAccessResult {
  hasAccess: boolean;
  reason?: string;
  accessType?: 'preview' | 'first_lesson' | 'first_module_lesson' | 'enrollment_required' | 'progression_blocked' | 'progression_allowed';
  requiredLesson?: {
    id: string;
    title: string;
    module_title: string;
  };
  isLoading: boolean;
  error?: string;
}

export const useLessonAccess = (lessonId: string): LessonAccessResult => {
  const [accessResult, setAccessResult] = useState<LessonAccessResult>({
    hasAccess: false,
    isLoading: true
  });
  const { user } = useAuthStore();

  useEffect(() => {
    const checkAccess = async () => {
      if (!lessonId || lessonId === '' || !user) {
        setAccessResult({
          hasAccess: false,
          isLoading: false,
          error: 'User not authenticated or lesson ID not provided'
        });
        return;
      }

      try {
        setAccessResult(prev => ({ ...prev, isLoading: true, error: undefined }));
        
        const response = await lessonService.checkLessonAccess(lessonId);
        
        setAccessResult({
          hasAccess: response.has_access,
          reason: response.reason,
          accessType: response.access_type,
          requiredLesson: response.required_lesson,
          isLoading: false
        });
      } catch (error) {
        console.error('Error checking lesson access:', error);
        setAccessResult({
          hasAccess: false,
          isLoading: false,
          error: 'Failed to check lesson access'
        });
      }
    };

    checkAccess();
  }, [lessonId, user]);

  return accessResult;
};

export default useLessonAccess; 