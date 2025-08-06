import React from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useLessonAccess } from '@/hooks/useLessonAccess';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Lock, Loader2, AlertCircle, BookOpen } from 'lucide-react';
import { toast } from 'sonner';

interface LessonAccessGuardProps {
  children: React.ReactNode;
  courseId?: string;
}

export default function LessonAccessGuard({ children, courseId }: LessonAccessGuardProps) {
  const { lessonId } = useParams();
  const navigate = useNavigate();
  const { hasAccess, reason, accessType, requiredLesson, isLoading, error } = useLessonAccess(lessonId!);

  // Handle loading state
  if (isLoading) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="text-center">
          <Loader2 className="h-8 w-8 animate-spin text-muted-foreground mx-auto mb-4" />
          <p className="text-muted-foreground">Checking lesson access...</p>
        </div>
      </div>
    );
  }

  // Handle error state
  if (error) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="text-center max-w-md mx-auto p-6">
          <AlertCircle className="h-12 w-12 text-destructive mx-auto mb-4" />
          <h2 className="text-xl font-semibold mb-2">Access Error</h2>
          <p className="text-muted-foreground mb-4">{error}</p>
          <Button 
            onClick={() => navigate('/dashboard/academy')}
            variant="outline"
          >
            Back to Academy
          </Button>
        </div>
      </div>
    );
  }

  // Handle access denied
  if (!hasAccess) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="text-center max-w-md mx-auto p-6">
          <Card className="border-destructive/20 bg-destructive/5">
            <CardContent className="p-6">
              <Lock className="h-12 w-12 text-destructive mx-auto mb-4" />
              <h2 className="text-xl font-semibold mb-2">Lesson Access Restricted</h2>
              <p className="text-muted-foreground mb-4">{reason}</p>
              
              {requiredLesson && (
                <div className="bg-muted/50 rounded-lg p-4 mb-4">
                  <p className="text-sm font-medium mb-2">Required Lesson:</p>
                  <div className="flex items-center gap-2">
                    <BookOpen className="h-4 w-4 text-muted-foreground" />
                    <span className="text-sm text-muted-foreground">
                      {requiredLesson.title}
                    </span>
                  </div>
                  <Badge variant="secondary" className="text-xs mt-1">
                    {requiredLesson.module_title}
                  </Badge>
                </div>
              )}
              
              <div className="flex flex-col sm:flex-row gap-2">
                {courseId && (
                  <Button 
                    onClick={() => navigate(`/dashboard/academy/course/${courseId}`)}
                    variant="outline"
                    className="flex-1"
                  >
                    Back to Course
                  </Button>
                )}
                <Button 
                  onClick={() => navigate('/dashboard/academy')}
                  variant="outline"
                  className="flex-1"
                >
                  Browse Courses
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    );
  }

  // Access granted - render children
  return <>{children}</>;
} 