import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Progress } from "@/components/ui/progress";
import { 
  ChevronLeft, 
  ChevronRight, 
  PlayCircle, 
  FileText, 
  HelpCircle, 
  BookOpen,
  Clock,
  CheckCircle,
  Loader2,
  AlertCircle,
  Lock
} from "lucide-react";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { courseService } from '@/services/courseService';
import { lessonService, Lesson as BaseLesson } from '@/services/lessonService';
import { useAuthStore } from '@/store/useAuthStore';
import { useLessonAccess } from '@/hooks/useLessonAccess';
import { toast } from "sonner";
import PagePreloader from "@/components/ui/PagePreloader";
import QuizPlayer from "./QuizPlayer";

interface Lesson extends BaseLesson {
  moduleTitle?: string;
  moduleId?: string;
}

interface Module {
  id: string;
  title: string;
  lessons: Lesson[];
}

interface Course {
  id: string;
  title: string;
  modules: Module[];
}

export default function LessonPlayer() {
  // All hooks must be called before any return
  const { courseId, lessonId } = useParams();
  const navigate = useNavigate();
  const user = useAuthStore(state => state.user);
  const queryClient = useQueryClient();
  
  const [currentLessonIndex, setCurrentLessonIndex] = useState(0);
  const [isMarkingComplete, setIsMarkingComplete] = useState(false);

  // Fetch course and lesson data
  const { 
    data: courseData, 
    isLoading: isCourseLoading, 
    error: courseError 
  } = useQuery({
    queryKey: ['course', courseId],
    queryFn: () => courseService.getCourseDetails(courseId!),
    enabled: !!courseId
  });

  const { 
    data: lessonData, 
    isLoading: isLessonLoading, 
    error: lessonError 
  } = useQuery({
    queryKey: ['lesson', lessonId],
    queryFn: () => lessonService.getLessonDetails(lessonId!),
    enabled: !!lessonId
  });

  // Fetch completed lessons for this course
  const { 
    data: progressData, 
    isLoading: isProgressLoading 
  } = useQuery({
    queryKey: ['lesson-progress', courseId],
    queryFn: () => lessonService.getLessonProgress(courseId!),
    enabled: !!courseId
  });

  // Get completed lessons from backend data
  const completedLessons = new Set(progressData?.data?.completed_lessons || []);

  const course = courseData?.course;
  const currentLesson = lessonData?.lesson as Lesson;

  // Use the access control hook - only when lessonId exists
  const { hasAccess, reason, accessType, requiredLesson, isLoading: isAccessLoading, error: accessError } = useLessonAccess(lessonId || '');

  // Find all lessons in the course
  const allLessons = course?.modules?.flatMap(module => 
    [...module.lessons]
      .sort((a, b) => a.order - b.order)
      .map(lesson => ({
        ...lesson,
        moduleTitle: module.title,
        moduleId: module.id // always use parent module's id
      }))
  ).sort((a, b) => {
    // Sort by module order first, then by lesson order
    const moduleA = course?.modules?.find(m => m.id === a.moduleId);
    const moduleB = course?.modules?.find(m => m.id === b.moduleId);
    
    if (moduleA?.order !== moduleB?.order) {
      return (moduleA?.order || 0) - (moduleB?.order || 0);
    }
    
    return a.order - b.order;
  }) || [];

  // Find current module lessons (for sidebar)
  const currentModule = course?.modules?.find(module => 
    module.lessons.some(lesson => lesson.id === currentLesson?.id)
  );
  const currentModuleLessons = currentModule
    ? [...currentModule.lessons]
      .sort((a, b) => a.order - b.order)
      .map(lesson => ({
        ...lesson,
        moduleId: currentModule.id // always set moduleId for navigation
      }))
  : [];

  // Find current lesson index
  useEffect(() => {
    if (currentLesson && allLessons.length > 0) {
      const index = allLessons.findIndex(lesson => lesson.id === currentLesson.id);
      if (index !== -1) {
        setCurrentLessonIndex(index);
      }
    }
  }, [currentLesson, allLessons]);

  // Navigation functions
  const goToPreviousLesson = () => {
    if (currentLessonIndex > 0) {
      const prevLesson = allLessons[currentLessonIndex - 1];
      const isAccessible = isLessonAccessible(prevLesson, currentLessonIndex - 1);
      console.log('[LessonPlayer] goToPreviousLesson:', {
        currentLessonIndex,
        prevLesson,
        isAccessible,
        completedLessons: Array.from(completedLessons),
      });
      if (isAccessible) {
        navigate(`/dashboard/academy/course/${courseId}/lesson/${prevLesson.id}`);
      } else {
        // Find the first incomplete lesson that's blocking access
        let blockingLesson = null;
        for (let i = 0; i < currentLessonIndex; i++) {
          const lesson = allLessons[i];
          if (!lesson.is_preview && !completedLessons.has(lesson.id)) {
            blockingLesson = lesson;
            break;
          }
        }
        if (blockingLesson) {
          toast.error(`You must complete "${blockingLesson.title}" first`);
        } else {
          toast.error('You must complete previous lessons first');
        }
      }
    }
  };

  const goToNextLesson = () => {
    if (currentLessonIndex < allLessons.length - 1) {
      const nextLesson = allLessons[currentLessonIndex + 1];
      const isAccessible = isLessonAccessible(nextLesson, currentLessonIndex + 1);
      console.log('[LessonPlayer] goToNextLesson:', {
        currentLessonIndex,
        nextLesson,
        isAccessible,
        completedLessons: Array.from(completedLessons),
      });
      if (isAccessible) {
        navigate(`/dashboard/academy/course/${courseId}/lesson/${nextLesson.id}`);
      } else {
        // Find the first incomplete lesson that's blocking access
        let blockingLesson = null;
        for (let i = 0; i <= currentLessonIndex; i++) {
          const lesson = allLessons[i];
          if (!lesson.is_preview && !completedLessons.has(lesson.id)) {
            blockingLesson = lesson;
            break;
          }
        }
        if (blockingLesson) {
          toast.error(`You must complete "${blockingLesson.title}" first`);
        } else {
          toast.error('You must complete the current lesson first');
        }
      }
    }
  };

  // Mark lesson as complete
  const markLessonComplete = async () => {
    if (!currentLesson || !user) return;

    setIsMarkingComplete(true);
    try {
      await lessonService.markLessonComplete(currentLesson.id);
      // Invalidate and refetch progress data
      queryClient.invalidateQueries({ queryKey: ['lesson-progress', courseId] });
      toast.success('Lesson marked as complete!');
    } catch (error) {
      toast.error('Failed to mark lesson as complete');
    } finally {
      setIsMarkingComplete(false);
    }
  };

  // Render content based on lesson type
  const renderLessonContent = () => {
    if (!currentLesson) return null;

    switch (currentLesson.content_type) {
      case 'video':
        // Check if it's a YouTube link
        const youtubeMatch = currentLesson.resource_url?.match(
          /(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|v\/))([A-Za-z0-9_-]{11})/
        );
        if (youtubeMatch) {
          const videoId = youtubeMatch[1];
          // Use minimal branding parameters
          const embedUrl = `https://www.youtube.com/embed/${videoId}?rel=0&modestbranding=1&showinfo=0&autoplay=1`;
          return (
            <div className="aspect-video bg-black rounded-lg overflow-hidden">
              <iframe
                src={embedUrl}
                className="w-full h-full"
                frameBorder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowFullScreen
                title="YouTube Video"
              />
            </div>
          );
        }
        // Fallback to HTML5 video for direct links (e.g., .mp4)
        return (
          <div className="aspect-video bg-black rounded-lg overflow-hidden">
            {currentLesson.resource_url ? (
              <video
                controls
                autoPlay
                muted
                className="w-full h-full"
                src={currentLesson.resource_url}
              >
                Your browser does not support the video tag.
              </video>
            ) : (
              <div className="flex items-center justify-center h-full text-white">
                <div className="text-center">
                  <PlayCircle className="h-16 w-16 mx-auto mb-4 opacity-50" />
                  <p>Video content not available</p>
                </div>
              </div>
            )}
          </div>
        );

      case 'text':
        return (
          <div className="prose prose-lg max-w-none">
            <div 
              className="text-content"
              dangerouslySetInnerHTML={{ __html: currentLesson.description }}
            />
          </div>
        );

      case 'quiz':
        return (
          <QuizPlayer 
            lessonId={currentLesson.id} 
            onComplete={() => {
              // Mark lesson as complete when quiz is completed
              markLessonComplete();
            }}
          />
        );

      case 'assignment':
        return (
          <div className="text-center py-12">
            <BookOpen className="h-16 w-16 mx-auto mb-4 text-muted-foreground" />
            <h3 className="text-lg font-semibold mb-2">Assignment</h3>
            <p className="text-muted-foreground mb-4">
              Assignment functionality coming soon!
            </p>
            <Button variant="outline" disabled>
              View Assignment
            </Button>
          </div>
        );

      case 'pdf':
        return (
          <div className="aspect-video bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center">
            {currentLesson.resource_url ? (
              <iframe
                src={currentLesson.resource_url}
                className="w-full h-[80vh] min-h-[400px] rounded"
                title="PDF Viewer"
                frameBorder="0"
              />
            ) : (
              <div className="text-center text-muted-foreground">
                <FileText className="h-16 w-16 mx-auto mb-4 opacity-50" />
                <p>PDF not available</p>
              </div>
            )}
          </div>
        );

      default:
        return (
          <div className="text-center py-12">
            <p className="text-muted-foreground">Content type not supported</p>
          </div>
        );
    }
  };

  // All useEffect hooks must be here, before any return
  useEffect(() => {
    if (accessError) {
      toast.error(accessError);
    }
  }, [accessError]);

  useEffect(() => {
    if (!isAccessLoading && !hasAccess && reason) {
      toast.error(reason);
    }
  }, [hasAccess, reason, isAccessLoading]);

  // Check which lessons are accessible
  const isLessonAccessible = (lesson: any, lessonIndex: number) => {
    // Preview lessons are always accessible
    if (lesson.is_preview) {
      console.log('[LessonPlayer] isLessonAccessible: preview lesson', { lesson, lessonIndex });
      return true;
    }
    // First lesson of the entire course is always accessible
    if (lessonIndex === 0) {
      console.log('[LessonPlayer] isLessonAccessible: first lesson of course', { lesson, lessonIndex });
      return true;
    }
    // Check if it's the first lesson of its module
    const currentModule = course?.modules?.find(module => 
      module.lessons.some(l => l.id === lesson.id)
    );
    if (currentModule) {
      const moduleLessons = [...currentModule.lessons].sort((a, b) => a.order - b.order);
      const isFirstLessonOfModule = moduleLessons[0]?.id === lesson.id;
      if (isFirstLessonOfModule) {
        console.log('[LessonPlayer] isLessonAccessible: first lesson of module', { lesson, lessonIndex, module: currentModule });
        return true;
      }
    }
    // For other lessons, check if all previous lessons in the same module are completed
    if (currentModule) {
      const moduleLessons = [...currentModule.lessons].sort((a, b) => a.order - b.order);
      const lessonIndexInModule = moduleLessons.findIndex(l => l.id === lesson.id);
      if (lessonIndexInModule > 0) {
        for (let i = 0; i < lessonIndexInModule; i++) {
          const previousLesson = moduleLessons[i];
          // Skip preview lessons - they don't block progression
          if (previousLesson.is_preview) {
            continue;
          }
          // If any previous lesson in the module is not completed, deny access
          if (!completedLessons.has(previousLesson.id)) {
            console.log('[LessonPlayer] isLessonAccessible: blocked by previous lesson', {
              lesson,
              lessonIndex,
              previousLesson,
              completedLessons: Array.from(completedLessons),
            });
            return false;
          }
        }
      }
    }
    console.log('[LessonPlayer] isLessonAccessible: accessible', { lesson, lessonIndex });
    return true;
  };

  // Helper: get module number by id
  const getModuleNumber = (moduleId: string) => {
    if (!course?.modules) return null;
    const sortedModules = [...course.modules].sort((a, b) => (a.order || 0) - (b.order || 0));
    const idx = sortedModules.findIndex(m => m.id === moduleId);
    console.log('[LessonPlayer] getModuleNumber', { moduleId, sortedModules, idx });
    return idx !== -1 ? idx + 1 : null;
  };

  // After all hooks, do conditional returns
  if (isCourseLoading || isLessonLoading || isProgressLoading) {
    return <PagePreloader />;
  }
  if (courseError || lessonError) {
    return (
      <div className="container mx-auto p-6">
        <Card>
          <CardContent className="p-8 text-center">
            <h2 className="text-xl font-semibold mb-2">Error Loading Lesson</h2>
            <p className="text-muted-foreground mb-4">
              {courseError?.message || lessonError?.message || 'Failed to load lesson content'}
            </p>
            <Button onClick={() => navigate(`/dashboard/academy/course/${courseId}`)}>
              Back to Course
            </Button>
          </CardContent>
        </Card>
      </div>
    );
  }
  if (lessonError && (lessonError as any).response?.status === 403) {
    const errorData = (lessonError as any).response?.data;
    const requiredLesson = errorData?.required_lesson;
    return (
      <div className="container mx-auto p-6">
        <Card>
          <CardContent className="p-8 text-center">
            <div className="mb-6">
              <AlertCircle className="h-16 w-16 text-orange-500 mx-auto mb-4" />
              <h2 className="text-xl font-semibold mb-2">Lesson Access Restricted</h2>
              <p className="text-muted-foreground mb-4">
                {errorData?.message || 'You need to complete previous lessons first.'}
              </p>
            </div>
            {requiredLesson && (
              <div className="bg-muted/50 rounded-lg p-4 mb-6">
                <h3 className="font-semibold mb-2">Required Lesson</h3>
                <p className="text-sm text-muted-foreground mb-3">
                  {requiredLesson.module_title} • {requiredLesson.title}
                </p>
                <Button 
                  onClick={() => navigate(`/dashboard/academy/course/${courseId}/lesson/${requiredLesson.id}`)}
                  className="w-full sm:w-auto"
                >
                  Go to Required Lesson
                </Button>
              </div>
            )}
            <div className="flex flex-col sm:flex-row gap-3 justify-center">
              <Button 
                variant="outline"
                onClick={() => navigate(`/dashboard/academy/course/${courseId}`)}
              >
                Back to Course
              </Button>
              {requiredLesson && (
                <Button 
                  onClick={() => navigate(`/dashboard/academy/course/${courseId}/lesson/${requiredLesson.id}`)}
                >
                  Start Required Lesson
                </Button>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    );
  }
  if (!course || !currentLesson) {
    return (
      <div className="container mx-auto p-6">
        <Card>
          <CardContent className="p-8 text-center">
            <h2 className="text-xl font-semibold mb-2">Lesson Not Found</h2>
            <p className="text-muted-foreground mb-4">
              The lesson you're looking for doesn't exist.
            </p>
            <Button onClick={() => navigate(`/dashboard/academy/course/${courseId}`)}>
              Back to Course
            </Button>
          </CardContent>
        </Card>
      </div>
    );
  }

  const isCompleted = currentLesson ? completedLessons.has(currentLesson.id) : false;
  const progress = progressData?.data?.progress_percentage || 0;

  // Show loading state while data is being fetched
  if (isCourseLoading || isLessonLoading || isProgressLoading || isAccessLoading) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="text-center">
          <Loader2 className="h-8 w-8 animate-spin text-muted-foreground mx-auto mb-4" />
          <p className="text-muted-foreground">Loading lesson...</p>
        </div>
      </div>
    );
  }

  // Show error state if any data failed to load
  if (courseError || lessonError) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="text-center max-w-md mx-auto p-6">
          <AlertCircle className="h-12 w-12 text-destructive mx-auto mb-4" />
          <h2 className="text-xl font-semibold mb-2">Failed to Load Lesson</h2>
          <p className="text-muted-foreground mb-4">
            {courseError?.message || lessonError?.message || 'An error occurred while loading the lesson'}
          </p>
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

  // Ensure we have the required data
  if (!course || !currentLesson) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="text-center">
          <Loader2 className="h-8 w-8 animate-spin text-muted-foreground mx-auto mb-4" />
          <p className="text-muted-foreground">Loading lesson data...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-background">
      {/* Mobile-optimized header */}
      <div className="sticky top-0 z-10 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 border-b">
        <div className="container mx-auto px-4 py-3">
          <div className="flex items-center justify-between">
            <div className="flex-1 min-w-0">
              <h1 className="text-lg sm:text-xl font-bold truncate">{course.title}</h1>
              <p className="text-xs sm:text-sm text-muted-foreground truncate">
                {currentLesson.moduleTitle || 'Module'} • Lesson {currentLessonIndex + 1} of {allLessons.length}
              </p>
            </div>
            {isCompleted && (
              <CheckCircle className="h-5 w-5 text-green-500 flex-shrink-0 ml-2" />
            )}
          </div>

          {/* Progress Bar */}
          <div className="mt-3 space-y-1">
            <div className="flex justify-between text-xs">
              <span>Course Progress</span>
              <span>{Math.round(progress)}%</span>
            </div>
            <Progress value={progress} className="h-1.5" />
          </div>
          </div>
        </div>

      <div className="container mx-auto px-4 py-4">
        <div className="grid grid-cols-1 lg:grid-cols-4 gap-4 lg:gap-6">
          {/* Main Content - Mobile optimized */}
          <div className="lg:col-span-3">
            {/* Lesson Header - Simplified for mobile */}
            <div className="mb-4 lg:mb-6">
              <h2 className="text-xl sm:text-2xl font-bold mb-2">{currentLesson.title}</h2>
              <div className="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                      <div className="flex items-center gap-1">
                        <Clock className="h-4 w-4" />
                  {currentLesson.duration_minutes} min
                      </div>
                <Badge variant="secondary" className="text-xs">
                        {currentLesson.content_type}
                      </Badge>
                      {currentLesson.is_preview && (
                  <Badge variant="outline" className="text-xs">Preview</Badge>
                      )}
                    </div>
                  </div>

            {/* Lesson Content - Full width on mobile */}
            <div className="space-y-6">
                {renderLessonContent()}

                {/* Lesson Description */}
                {currentLesson.description && (
                <div className="border-t pt-4">
                  <h3 className="font-semibold mb-2 text-sm">About this lesson</h3>
                  <p className="text-sm text-muted-foreground leading-relaxed">
                      {currentLesson.description}
                    </p>
                  </div>
                )}

              {/* Action Buttons - Mobile optimized */}
              <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-4 border-t">
                  <Button
                    variant="outline"
                    onClick={goToPreviousLesson}
                    disabled={currentLessonIndex === 0}
                    className="w-full sm:w-auto"
                    size="sm"
                  >
                    <ChevronLeft className="h-4 w-4 mr-2" />
                    Previous
                  </Button>

                  <div className="flex gap-2 w-full sm:w-auto">
                    {!isCompleted && (
                      <Button
                        onClick={markLessonComplete}
                        disabled={isMarkingComplete}
                        className="flex-1 sm:flex-none"
                        size="sm"
                      >
                        {isMarkingComplete ? (
                          <Loader2 className="h-4 w-4 animate-spin mr-2" />
                        ) : (
                          <CheckCircle className="h-4 w-4 mr-2" />
                        )}
                        <span className="hidden sm:inline">Mark Complete</span>
                        <span className="sm:hidden">Complete</span>
                      </Button>
                    )}

                    <Button
                      onClick={goToNextLesson}
                      disabled={
                        currentLessonIndex === allLessons.length - 1 || 
                        (!isCompleted && !currentLesson?.is_preview)
                      }
                      className="flex-1 sm:flex-none"
                      size="sm"
                    >
                      Next
                      <ChevronRight className="h-4 w-4 ml-2" />
                    </Button>
                                     </div>
                 </div>
              </div>
            </div>

                     {/* Mobile Lesson Navigation - Bottom sheet style */}
           <div className="lg:hidden mt-6">
             <div className="bg-card border rounded-lg p-4">
               <h3 className="font-semibold mb-3 text-sm">Module Lessons</h3>
               <div className="space-y-2 max-h-48 overflow-y-auto">
                 {currentModuleLessons.map((lesson, moduleIndex) => {
                   // Find the global index of this lesson in allLessons
                   const globalIndex = allLessons.findIndex(l => l.id === lesson.id);
                   const isAccessible = isLessonAccessible(lesson, globalIndex);
                   const moduleNumber = getModuleNumber(lesson.moduleId);
                   console.log('[LessonPlayer] MobileNav', { lesson, moduleIndex, moduleNumber, globalIndex });
                   return (
                     <div
                       key={lesson.id}
                       className={`p-2 rounded transition-colors ${
                         lesson.id === currentLesson?.id
                           ? 'bg-primary/10 border border-primary/20'
                           : isAccessible 
                             ? 'cursor-pointer hover:bg-muted/50' 
                             : 'opacity-50 cursor-not-allowed'
                       }`}
                       onClick={() => {
                         if (isAccessible) {
                           navigate(`/dashboard/academy/course/${courseId}/lesson/${lesson.id}`);
                         } else {
                           // Find the first incomplete lesson that's blocking access
                           let blockingLesson = null;
                           for (let i = 0; i < globalIndex; i++) {
                             const prevLesson = allLessons[i];
                             if (!prevLesson.is_preview && !completedLessons.has(prevLesson.id)) {
                               blockingLesson = prevLesson;
                               break;
                             }
                           }
                           
                           if (blockingLesson) {
                             toast.error(`You must complete "${blockingLesson.title}" first`);
                           } else {
                             toast.error('You must complete previous lessons first');
                           }
                         }
                       }}
                     >
                       <div className="flex items-center gap-2">
                         <div className="flex-shrink-0">
                           {completedLessons.has(lesson.id) ? (
                             <CheckCircle className="h-3 w-3 text-green-500" />
                           ) : (
                             <div className="w-3 h-3 rounded-full border-2 border-muted-foreground/30" />
                           )}
                         </div>
                         <div className="flex-1 min-w-0">
                           <p className={`text-xs font-medium truncate ${
                             lesson.id === currentLesson?.id ? 'text-primary' : ''
                           }`}>
                             Module {moduleNumber} • {moduleIndex + 1}. {lesson.title}
                           </p>
                           <p className="text-xs text-muted-foreground">
                             {lesson.duration_minutes} min • {lesson.content_type}
                           </p>
                         </div>
                         {!isAccessible && (
                           <Lock className="h-3 w-3 text-muted-foreground/50" />
                         )}
                       </div>
                     </div>
                   );
                 })}
               </div>
             </div>
           </div>

          {/* Sidebar - Hidden on mobile, shown on desktop */}
          <div className="hidden lg:block lg:col-span-1">
            <div className="sticky top-24">
              <div className="bg-card border rounded-lg p-4">
                <h3 className="font-semibold mb-3">Module Lessons</h3>
                <p className="text-sm text-muted-foreground mb-4">
                  {currentLesson?.moduleTitle || 'Current Module'}
                </p>
                <div className="space-y-2">
                   {currentModuleLessons.map((lesson, moduleIndex) => {
                     // Find the global index of this lesson in allLessons
                     const globalIndex = allLessons.findIndex(l => l.id === lesson.id);
                     const isAccessible = isLessonAccessible(lesson, globalIndex);
                     const moduleNumber = getModuleNumber(lesson.moduleId);
                     console.log('[LessonPlayer] SidebarNav', { lesson, moduleIndex, moduleNumber, globalIndex });
                     return (
                    <div
                      key={lesson.id}
                         className={`p-3 rounded-lg transition-colors ${
                        lesson.id === currentLesson?.id
                          ? 'bg-primary/10 border border-primary/20'
                             : isAccessible 
                               ? 'cursor-pointer hover:bg-muted/50' 
                               : 'opacity-50 cursor-not-allowed'
                      }`}
                         onClick={() => {
                           if (isAccessible) {
                             navigate(`/dashboard/academy/course/${courseId}/lesson/${lesson.id}`);
                           } else {
                             // Find the first incomplete lesson that's blocking access
                             let blockingLesson = null;
                             for (let i = 0; i < globalIndex; i++) {
                               const prevLesson = allLessons[i];
                               if (!prevLesson.is_preview && !completedLessons.has(prevLesson.id)) {
                                 blockingLesson = prevLesson;
                                 break;
                               }
                             }
                             
                             if (blockingLesson) {
                               toast.error(`You must complete "${blockingLesson.title}" first`);
                             } else {
                               toast.error('You must complete previous lessons first');
                             }
                           }
                         }}
                    >
                      <div className="flex items-center gap-3">
                        <div className="flex-shrink-0">
                          {completedLessons.has(lesson.id) ? (
                            <CheckCircle className="h-4 w-4 text-green-500" />
                          ) : (
                            <div className="w-4 h-4 rounded-full border-2 border-muted-foreground/30" />
                          )}
                        </div>
                        <div className="flex-1 min-w-0">
                          <p className={`text-sm font-medium truncate ${
                            lesson.id === currentLesson?.id ? 'text-primary' : ''
                          }`}>
                            Module {moduleNumber} • {moduleIndex + 1}. {lesson.title}
                          </p>
                          <p className="text-xs text-muted-foreground">
                            {lesson.duration_minutes} min • {lesson.content_type}
                          </p>
                        </div>
                        {!isAccessible && (
                          <Lock className="h-4 w-4 text-muted-foreground/50" />
                        )}
                      </div>
                    </div>
                     );
                   })}
                 </div>
              </div>
                </div>
          </div>
        </div>
      </div>
    </div>
  );
} 