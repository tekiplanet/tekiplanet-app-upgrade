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
  Loader2
} from "lucide-react";
import { useQuery } from "@tanstack/react-query";
import { courseService } from '@/services/courseService';
import { lessonService, Lesson as BaseLesson } from '@/services/lessonService';
import { useAuthStore } from '@/store/useAuthStore';
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
  const { courseId, lessonId } = useParams();
  const navigate = useNavigate();
  const user = useAuthStore(state => state.user);
  
  const [currentLessonIndex, setCurrentLessonIndex] = useState(0);
  const [completedLessons, setCompletedLessons] = useState<Set<string>>(new Set());
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

  const course = courseData?.course;
  const currentLesson = lessonData?.lesson as Lesson;

  // Find all lessons in the course
  const allLessons = course?.modules?.flatMap(module => 
    [...module.lessons]
      .sort((a, b) => a.order - b.order)
      .map(lesson => ({ ...lesson, moduleTitle: module.title, moduleId: module.id }))
  ) || [];

  // Find current module lessons (for sidebar)
  const currentModule = course?.modules?.find(module => 
    module.lessons.some(lesson => lesson.id === currentLesson?.id)
  );
  const currentModuleLessons = currentModule
    ? [...currentModule.lessons].sort((a, b) => a.order - b.order)
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
      navigate(`/dashboard/academy/course/${courseId}/lesson/${prevLesson.id}`);
    }
  };

  const goToNextLesson = () => {
    if (currentLessonIndex < allLessons.length - 1) {
      const nextLesson = allLessons[currentLessonIndex + 1];
      navigate(`/dashboard/academy/course/${courseId}/lesson/${nextLesson.id}`);
    }
  };

  // Mark lesson as complete
  const markLessonComplete = async () => {
    if (!currentLesson || !user) return;

    setIsMarkingComplete(true);
    try {
      await lessonService.markLessonComplete(currentLesson.id);
      setCompletedLessons(prev => new Set([...prev, currentLesson.id]));
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

  // Loading states
  if (isCourseLoading || isLessonLoading) {
    return <PagePreloader />;
  }

  // Error states
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

  const isCompleted = completedLessons.has(currentLesson.id);
  const progress = allLessons.length > 0 ? (completedLessons.size / allLessons.length) * 100 : 0;

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
                    disabled={currentLessonIndex === allLessons.length - 1}
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
                {currentModuleLessons.map((lesson, index) => (
                  <div
                    key={lesson.id}
                    className={`p-2 rounded cursor-pointer transition-colors ${
                      lesson.id === currentLesson?.id
                        ? 'bg-primary/10 border border-primary/20'
                        : 'hover:bg-muted/50'
                    }`}
                    onClick={() => navigate(`/dashboard/academy/course/${courseId}/lesson/${lesson.id}`)}
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
                          {index + 1}. {lesson.title}
                        </p>
                        <p className="text-xs text-muted-foreground">
                          {lesson.duration_minutes} min • {lesson.content_type}
                        </p>
                      </div>
                    </div>
                  </div>
                ))}
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
                  {currentModuleLessons.map((lesson, index) => (
                    <div
                      key={lesson.id}
                      className={`p-3 rounded-lg cursor-pointer transition-colors ${
                        lesson.id === currentLesson?.id
                          ? 'bg-primary/10 border border-primary/20'
                          : 'hover:bg-muted/50'
                      }`}
                      onClick={() => navigate(`/dashboard/academy/course/${courseId}/lesson/${lesson.id}`)}
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
                            {index + 1}. {lesson.title}
                          </p>
                          <p className="text-xs text-muted-foreground">
                            {lesson.duration_minutes} min • {lesson.content_type}
                          </p>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
} 