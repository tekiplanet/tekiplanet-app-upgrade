import React from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { toast } from 'sonner';  
import { useAuthStore } from '@/store/useAuthStore';
import enrollmentService from '@/services/enrollmentService';
import { courseManagementService } from '@/services/courseManagementService';
import { apiClient } from '@/lib/axios';
import { isAxiosError } from 'axios';
import { useQuery } from "@tanstack/react-query";
import { settingsService } from "@/services/settingsService";

import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Progress } from "@/components/ui/progress";
import { formatCurrency } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { Calendar, Clock, Bell, GraduationCap, FileText, BookOpen, Wallet, PlayCircle, Lock } from "lucide-react";
import PagePreloader from '@/components/ui/PagePreloader';

// Components for each tab
import PaymentInfo from '@/components/course-management/PaymentInfo';
import CourseContent from '@/components/course-management/CourseContent';
import CourseSchedule from '@/components/course-management/CourseSchedule';
import CourseNotices from '@/components/course-management/CourseNotices';
import ExamSchedule from '@/components/course-management/ExamSchedule';

const CourseManagement: React.FC = () => {
  const { courseId } = useParams();
  const user = useAuthStore((state) => state.user);
  const navigate = useNavigate();

  // Add settings query at the top with other queries
  const { data: settings } = useQuery({
    queryKey: ['settings'],
    queryFn: settingsService.fetchSettings
  });

  const [courseIdState, setCourseId] = React.useState<string | null>(courseId || null);
  const [courseDetails, setCourseDetails] = React.useState<{
    course: any;
    modules: any[];
    lessons: any[];
    exams: any[];
    schedules: any[];
    notices: any[];
    features: any[];
    instructor: any;
    enrollment: {
      id: string;
      status: string;
      progress: number;
      enrolled_at: string;
    } | null;
    installments: any[];
    canAccessCourse: boolean;
    accessReason: string | null;
  } | null>(null);

  // Access control check - moved after state declaration
  React.useEffect(() => {
    if (courseDetails && !courseDetails.canAccessCourse) {
      toast.error(`Access denied: ${courseDetails.accessReason || 'No reason provided'}`);
      navigate('/dashboard/academy/my-courses');
    }
  }, [courseDetails, navigate]);

  const [errorMessage, setErrorMessage] = React.useState('');
  // const [enrollments, setEnrollments] = React.useState<any[]>([]);
  const [notices, setNotices] = React.useState<any[]>([]);
  const [noticesLoading, setNoticesLoading] = React.useState(true);
  const [isLoading, setIsLoading] = React.useState(true);

  const [upcomingExamsCount, setUpcomingExamsCount] = React.useState(0);

  const handleNoticeDelete = React.useCallback((noticeId: string) => {
    setNotices(prevNotices => 
      prevNotices.filter(notice => notice.id !== noticeId)
    );
  }, []);

  const handleUpcomingExamsCountChange = React.useCallback((count: number) => {
    setUpcomingExamsCount(count);
  }, []);


    // Function to calculate upcoming exams
    const calculateUpcomingExams = React.useCallback((exams: any[] = []) => {
      const now = new Date();
      const nowDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
      
      const upcomingExams = exams.filter(exam => {
        const examDate = new Date(exam.date);
        
        // Log details for debugging
        // console.log('Upcoming Exams Calculation:', {
        //   examTitle: exam.title,
        //   examDate: examDate.toISOString(),
        //   nowDate: nowDate.toISOString(),
        //   isUpcoming: examDate >= nowDate
        // });
        
        return examDate >= nowDate;
      });
  
      return upcomingExams.length;
    }, []);

  // Move the state update to useEffect
  React.useEffect(() => {
    if (courseDetails?.exams) {
      const count = calculateUpcomingExams(courseDetails.exams);
      setUpcomingExamsCount(count);
    }
  }, [courseDetails?.exams, calculateUpcomingExams]);

  // Add this method near other React.useCallback methods
  const refreshExams = React.useCallback(async () => {
    try {
      if (!courseIdState) return;
  
      const examsResponse = await apiClient.get(`/courses/${courseIdState}/exams`);
      const exams = examsResponse.data;
  
      // Recalculate upcoming exams
      const upcomingCount = calculateUpcomingExams(exams);
  
      // Update course details to reflect new exam state
      if (courseDetails) {
        setCourseDetails(prevDetails => ({
          ...prevDetails!,
          exams: exams
        }));
      }
  
      // Call the callback to update upcoming exams count if provided
      if (handleUpcomingExamsCountChange) {
        handleUpcomingExamsCountChange(upcomingCount);
      }
  
      // Optional: Add a toast to confirm refresh
      toast.success('Exams Refreshed', {
        description: 'Exam schedule has been updated'
      });
  
      return exams;
    } catch (error) {
      console.error('Error refreshing exams:', error);
      toast.error('Failed to Refresh Exams', {
        description: 'Could not update exam schedule'
      });
      
      return [];
    }
  }, [courseIdState, calculateUpcomingExams, courseDetails, handleUpcomingExamsCountChange]);


  // Remove this separate effect as it conflicts with the main courseDetails fetch
  // The exams are already included in the main courseDetails response

  // React.useEffect(() => {
  //   const fetchCourseDetails = async () => {
  //     setIsLoading(true);
  //     try {
  //       if (!courseIdState) return;

  //       const courseDetails = await courseManagementService.getCourseDetails(courseIdState);
  //       setCourseDetails(courseDetails);
  //       setEnrollment(courseDetails.enrollment);

  //       setIsLoading(false);
  //     } catch (error) {
  //       console.error('Error fetching course details:', error);
  //       setIsLoading(false);
  //     }
  //   };

  //   fetchCourseDetails();
  // }, [courseIdState]);





  // const [enrollment, setEnrollment] = React.useState<any>(null);

  React.useEffect(() => {
    const fetchCourseDetails = async () => {
      setIsLoading(true);
      try {
        if (!courseIdState) return;
  
        const courseDetails = await courseManagementService.getCourseDetails(courseIdState, user?.currency_code);
        setCourseDetails(courseDetails);
        // Log modules array for debugging order
        if (courseDetails && courseDetails.course && courseDetails.course.modules) {
          console.log('Modules from backend:', courseDetails.course.modules.map(m => ({id: m.id, title: m.title, order: m.order})));
        }
        
        // Calculate and set upcoming exams count from the fetched data
        if (courseDetails.exams) {
          const count = calculateUpcomingExams(courseDetails.exams);
          setUpcomingExamsCount(count);
        }
  
        // If enrollment is not in course details, fetch it separately
        if (!courseDetails.enrollment) {
          const enrollmentDetails = await enrollmentService.getUserCourseEnrollment(courseIdState);
          setCourseDetails(prev => ({
            ...prev,
            enrollment: enrollmentDetails
          }));
        }
  
        setIsLoading(false);
      } catch (error) {
        console.error('Error fetching course details:', error);
        setIsLoading(false);
        toast.error('Failed to fetch course details');
      }
    };
  
    fetchCourseDetails();
  }, [courseIdState]);



  React.useEffect(() => {
    const fetchCourseNotices = async () => {
      if (!courseId) return;

      try {
        setNoticesLoading(true);
        // console.log(`Fetching notices for courseId: ${courseId}`);
        
        const noticesResponse = await courseManagementService.getCourseNotices(courseId);
        
        // console.log('Notices Response:', {
        //   success: noticesResponse.success,
        //   message: noticesResponse.message,
        //   noticesCount: noticesResponse.notices?.length,
        //   noticesDetails: JSON.stringify(noticesResponse.notices, null, 2)
        // });
        
        // Sort notices by date, most recent first
        const sortedNotices = (noticesResponse.notices || [])
          .sort((a, b) => b.date.getTime() - a.date.getTime());
        
        setNotices(sortedNotices);
        
        // Log any error message if notices fetch was unsuccessful
        if (!noticesResponse.success) {
          toast.warning(noticesResponse.message || 'Could not fetch all notices', {
            description: 'Falling back to default notifications'
          });
        }
      } catch (error) {
        console.error('Comprehensive Error in fetchCourseNotices:', error);
        toast.error('Failed to load course notices', {
          description: error instanceof Error ? error.message : 'Unknown error occurred'
        });
      } finally {
        setNoticesLoading(false);
      }
    };

    fetchCourseNotices();
  }, [courseId]);

  // Update the existing course and enrollment logic
  const course = React.useMemo(() => {
    return courseDetails?.course || null;
  }, [courseDetails]);

  const enrollment = React.useMemo(() => {
    // Use the enrollment from courseDetails instead of enrollments
    return courseDetails?.enrollment || null;
  }, [courseDetails]);

  // After fetching courseDetails and setting courseDetails
  // Add this helper to always get sorted modules
  const sortedModules = React.useMemo(() => {
    return courseDetails?.course?.modules
      ? [...courseDetails.course.modules].sort((a, b) => (a.order || 0) - (b.order || 0))
      : [];
  }, [courseDetails]);

  // Get completed lessons from backend data
  const completedLessons = new Set([]); // For now, use empty array since enrollment doesn't have completed_lessons

  // Get all lessons in the course for access checking
  const allLessons = React.useMemo(() => {
    return sortedModules?.flatMap(module => 
      [...module.lessons]
        .sort((a, b) => a.order - b.order)
        .map(lesson => ({
          ...lesson,
          moduleTitle: module.title,
          moduleId: module.id
        }))
    ).sort((a, b) => {
      // Sort by module order first, then by lesson order
      const moduleA = sortedModules?.find(m => m.id === a.moduleId);
      const moduleB = sortedModules?.find(m => m.id === b.moduleId);
      
      if (moduleA?.order !== moduleB?.order) {
        return (moduleA?.order || 0) - (moduleB?.order || 0);
      }
      
      return a.order - b.order;
    }) || [];
  }, [sortedModules]);

  // Check which lessons are accessible (same logic as LessonPlayer)
  const isLessonAccessible = (lesson: any, lessonIndex: number) => {
    // Preview lessons are always accessible
    if (lesson.is_preview) {
      return true;
    }
    // For all lessons, check if all previous non-preview lessons in the course are completed
    for (let i = 0; i < lessonIndex; i++) {
      const prevLesson = allLessons[i];
      if (!prevLesson.is_preview && !completedLessons.has(prevLesson.id)) {
        return false;
      }
    }
    return true;
  };

  // Debug curriculum
  React.useEffect(() => {
    // console.log('Course Object:', course);
  }, [course]);

  // Render Curriculum Section
  const renderCurriculum = () => {
    console.log('Rendering Curriculum - Course:', course);
    
    const curriculumData = 
      course?.curriculum || 
      sortedModules || 
      course?.content || 
      course?.courseContent;

    console.log('Curriculum Data:', curriculumData);
    console.log('courseIdState:', courseIdState);
    
    // Log the first module structure to see how lessons are organized
    if (curriculumData && curriculumData.length > 0) {
      console.log('First module structure:', curriculumData[0]);
      console.log('First module topics:', curriculumData[0].topics);
      console.log('First module lessons:', curriculumData[0].lessons);
      console.log('First module content:', curriculumData[0].content);
      console.log('All module properties:', Object.keys(curriculumData[0]));
      
      if (curriculumData[0].topics && curriculumData[0].topics.length > 0) {
        console.log('First topic lessons:', curriculumData[0].topics[0].lessons);
      }
    }

    if (!curriculumData || curriculumData.length === 0) {
      return (
        <div className="flex flex-col items-center justify-center py-12">
          <BookOpen className="h-12 w-12 text-muted-foreground/50 mb-4" />
          <p className="text-muted-foreground font-medium">No curriculum available yet</p>
          <p className="text-sm text-muted-foreground/70">Check back soon for updates</p>
        </div>
      );
    }

    return (
      <div className="space-y-4">
        {curriculumData.map((module, moduleIndex) => (
          <div key={module.id || moduleIndex} className="group">
            <div className="flex items-center gap-3 mb-3">
              <div className="flex-shrink-0 w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                <span className="text-sm font-semibold text-primary">{moduleIndex + 1}</span>
              </div>
              <div className="flex-grow min-w-0">
                <h4 className="text-base font-semibold group-hover:text-primary transition-colors truncate">
                  {module.title || module.name || `Module ${moduleIndex + 1}`}
                </h4>
                                 <p className="text-xs text-muted-foreground">
                   {module.lessons?.length || 0} Lessons
                 </p>
              </div>
            </div>
            
                         {/* Lessons */}
             {module.lessons && module.lessons.length > 0 && (
               <div className="ml-4 pl-8 border-l border-border/50 space-y-2">
                 {[...module.lessons]
                   .sort((a, b) => a.order - b.order)
                   .map((lesson, lessonIndex) => {
                     // Find the global index of this lesson in allLessons
                     const globalIndex = allLessons.findIndex(l => l.id === lesson.id);
                     const isAccessible = isLessonAccessible(lesson, globalIndex);
                     
                     return (
                       <div 
                         key={lesson.id || lessonIndex}
                         className={`flex items-center gap-2 p-2 rounded-lg transition-colors ${
                           isAccessible 
                             ? 'bg-background/50 hover:bg-background cursor-pointer' 
                             : 'bg-background/30 opacity-50 cursor-not-allowed'
                         }`}
                         onClick={() => {
                           if (isAccessible) {
                             console.log('Lesson clicked!', {
                               courseIdState,
                               lessonId: lesson.id,
                               lessonTitle: lesson.title,
                               lessonData: lesson,
                               navigateUrl: `/dashboard/academy/course/${courseIdState}/lesson/${lesson.id}`
                             });
                             navigate(`/dashboard/academy/course/${courseIdState}/lesson/${lesson.id}`);
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
                         <div className="flex items-center gap-2 flex-1 min-w-0">
                           {isAccessible ? (
                             <PlayCircle className="h-3.5 w-3.5 text-muted-foreground flex-shrink-0" />
                           ) : (
                             <Lock className="h-3.5 w-3.5 text-muted-foreground/50 flex-shrink-0" />
                           )}
                           <span className={`text-xs truncate ${!isAccessible ? 'text-muted-foreground/50' : ''}`}>
                             {lesson.title}
                           </span>
                         </div>
                         {lesson.duration_minutes && (
                           <Badge variant="secondary" className="text-[10px] h-5">
                             {lesson.duration_minutes} mins
                           </Badge>
                         )}
                       </div>
                     );
                   })}
               </div>
             )}
          </div>
        ))}
      </div>
    );
  };

  // If loading, show preloader
  if (isLoading) {
    return <PagePreloader />;
  }

  // If no course found after loading, show not found
  if (!course) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold mb-2">Course Not Found</h1>
          <p className="text-muted-foreground mb-4">
            The course you're looking for doesn't exist.
          </p>
          <Button onClick={() => navigate('/dashboard/academy/my-courses')}>
            Back to My Courses
          </Button>
        </div>
      </div>
    );
  }

  return (
    <div className="flex flex-col min-h-screen bg-background">
      {/* Main container with mobile-first padding */}
      <div className="flex-1 w-full">
        {/* Hero Section */}
        <div className="relative bg-gradient-to-b from-primary/10 to-background px-4 md:px-6 pt-4 pb-6 md:pt-6 md:pb-8">
          <div className="max-w-[1200px] mx-auto">
            {/* Course Details */}
            <div className="flex flex-col space-y-3">
              <div className="flex flex-wrap gap-2">
                <Badge variant="secondary" className="px-2.5 py-0.5 text-xs">
                  {course.category || 'General'}
                </Badge>
                <Badge 
                  variant={enrollment?.status === 'completed' ? "default" : "secondary"}
                  className="px-2.5 py-0.5 text-xs"
                >
                  {enrollment?.status || 'Enrolled'}
                </Badge>
              </div>
              
              <div>
                <h1 className="text-xl md:text-2xl font-bold mb-2">{course.title}</h1>
                <p className="text-sm text-muted-foreground line-clamp-2">{course.description}</p>
              </div>
              
              {/* Course Meta Info */}
              <div className="flex flex-wrap items-center gap-3 text-xs text-muted-foreground mt-2">
                <div className="flex items-center gap-1.5">
                  <Clock className="h-3.5 w-3.5" />
                  <span>{course.duration_hours} Months</span>
                </div>
                <div className="flex items-center gap-1.5">
                  <Calendar className="h-3.5 w-3.5" />
                  <span>
                    {enrollment?.enrolled_at ? new Date(enrollment.enrolled_at).toLocaleDateString() : 'N/A'}
                  </span>
                </div>
                <div className="flex items-center gap-1.5">
                  <GraduationCap className="h-3.5 w-3.5" />
                  <span>{course.level}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Stats Grid */}
        <div className="px-4 md:px-6 -mt-4">
          <div className="max-w-[1200px] mx-auto">
            <div className="grid gap-3 md:gap-4">
              <Card className="bg-card/50 backdrop-blur-sm border-none shadow-sm">
                <CardContent className="p-3 md:p-4">
                  <div className="flex items-center gap-2.5">
                    <div className="p-2 rounded-lg bg-primary/10">
                      <GraduationCap className="h-4 w-4 text-primary" />
                    </div>
                    <div>
                      <p className="text-xs text-muted-foreground">Progress</p>
                      <div className="flex items-center gap-2">
                        <p className="text-sm font-medium">{enrollment?.progress || 0}%</p>
                        <Progress value={enrollment?.progress || 0} className="w-24 h-1.5" />
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card className="bg-card/50 backdrop-blur-sm border-none shadow-sm">
                <CardContent className="p-3 md:p-4">
                  <div className="flex items-center gap-2.5">
                    <div className="p-2 rounded-lg bg-primary/10">
                      <Wallet className="h-4 w-4 text-primary" />
                    </div>
                    <div>
                      <p className="text-xs text-muted-foreground">Tuition Fee</p>
                      <p className="text-sm font-medium">
                        {formatCurrency(Number(course.price), settings?.default_currency)}
                      </p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>

        {/* Tabs Section */}
        <div className="px-4 md:px-6 mt-6">
          <div className="max-w-[1200px] mx-auto">
            <Tabs defaultValue="content" className="w-full">
              <div className="relative">
                <div className="overflow-x-auto scrollbar-none">
                  <TabsList className="w-full h-10 p-1 bg-muted rounded-lg grid grid-cols-5">
                    <TabsTrigger value="content" className="rounded-md data-[state=active]:bg-background">
                      <div className="flex items-center gap-2">
                        <BookOpen className="h-4 w-4" />
                        <span className="hidden md:inline text-sm">Content</span>
                      </div>
                    </TabsTrigger>
                    <TabsTrigger value="schedule" className="rounded-md data-[state=active]:bg-background">
                      <div className="flex items-center gap-2">
                        <Calendar className="h-4 w-4" />
                        <span className="hidden md:inline text-sm">Schedule</span>
                      </div>
                    </TabsTrigger>
                    <TabsTrigger value="notices" className="rounded-md data-[state=active]:bg-background relative">
                      <div className="flex items-center gap-2">
                        <Bell className="h-4 w-4" />
                        <span className="hidden md:inline text-sm">Notices</span>
                        {notices.length > 0 && (
                          <span className="absolute top-1 right-1 h-1.5 w-1.5 bg-primary rounded-full" />
                        )}
                      </div>
                    </TabsTrigger>
                    <TabsTrigger value="exams" className="rounded-md data-[state=active]:bg-background relative">
                      <div className="flex items-center gap-2">
                        <FileText className="h-4 w-4" />
                        <span className="hidden md:inline text-sm">Exams</span>
                        {upcomingExamsCount > 0 && (
                          <span className="absolute top-1 right-1 h-1.5 w-1.5 bg-primary rounded-full" />
                        )}
                      </div>
                    </TabsTrigger>
                    <TabsTrigger value="payment" className="rounded-md data-[state=active]:bg-background">
                      <div className="flex items-center gap-2">
                        <Wallet className="h-4 w-4" />
                        <span className="hidden md:inline text-sm">Payment</span>
                      </div>
                    </TabsTrigger>
                  </TabsList>
                </div>
              </div>

              {/* Tab Content */}
              <div className="mt-6 space-y-4">
                <TabsContent value="content">
                  <Card className="border-none shadow-sm">
                    <CardContent className="p-4">
                      {renderCurriculum()}
                    </CardContent>
                  </Card>
                </TabsContent>
                <TabsContent value="schedule">
                  <Card className="border-none shadow-sm">
                    <CardContent className="p-4">
                      <CourseSchedule courseId={courseIdState} />
                    </CardContent>
                  </Card>
                </TabsContent>
                <TabsContent value="notices">
                  <Card className="border-none shadow-sm">
                    <CardContent className="p-4">
                      <CourseNotices 
                        courseId={courseIdState} 
                        notices={notices} 
                        loading={noticesLoading} 
                        onNoticeDelete={handleNoticeDelete} 
                      />
                    </CardContent>
                  </Card>
                </TabsContent>
                <TabsContent value="exams">
                  <Card className="border-none shadow-sm">
                    <CardContent className="p-4">
                      <ExamSchedule 
                        courseId={courseIdState} 
                        refreshExams={refreshExams}
                        onUpcomingExamsCountChange={handleUpcomingExamsCountChange}
                      />
                    </CardContent>
                  </Card>
                </TabsContent>
                <TabsContent value="payment">
                  <Card className="border-none shadow-sm">
                    <CardContent className="p-4">
                      {courseIdState && (
                        <PaymentInfo courseId={courseIdState} settings={settings} />
                      )}
                    </CardContent>
                  </Card>
                </TabsContent>
              </div>
            </Tabs>
          </div>
        </div>
      </div>
    </div>
  );
};

export default CourseManagement;