import { apiClient } from '@/lib/api-client';

export interface Lesson {
  id: string;
  title: string;
  description: string;
  content_type: 'video' | 'text' | 'quiz' | 'assignment' | 'pdf';
  duration_minutes: number;
  resource_url?: string;
  is_preview: boolean;
  order: number;
  module_id: string;
  created_at: string;
  updated_at: string;
}

export interface LessonDetailsResponse {
  success: boolean;
  lesson: Lesson;
  message?: string;
}

export interface LessonCompletionResponse {
  success: boolean;
  message: string;
  data?: {
    lesson_id: string;
    completed_at: string;
    progress_percentage: number;
  };
}

class LessonService {
  /**
   * Get lesson details by lesson ID
   */
  async getLessonDetails(lessonId: string): Promise<LessonDetailsResponse> {
    try {
      const response = await apiClient.get(`/lessons/${lessonId}`);
      return response.data;
    } catch (error) {
      console.error('Error fetching lesson details:', error);
      throw new Error('Failed to fetch lesson details');
    }
  }

  /**
   * Mark a lesson as complete for the current user
   */
  async markLessonComplete(lessonId: string): Promise<LessonCompletionResponse> {
    try {
      const response = await apiClient.post(`/lessons/${lessonId}/complete`);
      return response.data;
    } catch (error) {
      console.error('Error marking lesson complete:', error);
      throw new Error('Failed to mark lesson as complete');
    }
  }

  /**
   * Get lesson progress for a specific course
   */
  async getLessonProgress(courseId: string): Promise<{
    success: boolean;
    data: {
      completed_lessons: string[];
      total_lessons: number;
      progress_percentage: number;
    };
  }> {
    try {
      const response = await apiClient.get(`/courses/${courseId}/lesson-progress`);
      return response.data;
    } catch (error) {
      console.error('Error fetching lesson progress:', error);
      throw new Error('Failed to fetch lesson progress');
    }
  }

  /**
   * Get all lessons for a specific course
   */
  async getCourseLessons(courseId: string): Promise<{
    success: boolean;
    data: Lesson[];
  }> {
    try {
      const response = await apiClient.get(`/courses/${courseId}/lessons`);
      return response.data;
    } catch (error) {
      console.error('Error fetching course lessons:', error);
      throw new Error('Failed to fetch course lessons');
    }
  }

  /**
   * Get next lesson in sequence
   */
  async getNextLesson(currentLessonId: string): Promise<LessonDetailsResponse> {
    try {
      const response = await apiClient.get(`/lessons/${currentLessonId}/next`);
      return response.data;
    } catch (error) {
      console.error('Error fetching next lesson:', error);
      throw new Error('Failed to fetch next lesson');
    }
  }

  /**
   * Get previous lesson in sequence
   */
  async getPreviousLesson(currentLessonId: string): Promise<LessonDetailsResponse> {
    try {
      const response = await apiClient.get(`/lessons/${currentLessonId}/previous`);
      return response.data;
    } catch (error) {
      console.error('Error fetching previous lesson:', error);
      throw new Error('Failed to fetch previous lesson');
    }
  }

  /**
   * Check if user has access to a lesson
   */
  async checkLessonAccess(lessonId: string): Promise<{
    success: boolean;
    has_access: boolean;
    reason?: string;
    access_type?: 'preview' | 'first_lesson' | 'first_module_lesson' | 'enrollment_required' | 'progression_blocked' | 'progression_allowed';
    required_lesson?: {
      id: string;
      title: string;
      module_title: string;
    };
  }> {
    try {
      const response = await apiClient.get(`/lessons/${lessonId}/access`);
      return response.data;
    } catch (error) {
      console.error('Error checking lesson access:', error);
      throw new Error('Failed to check lesson access');
    }
  }

  /**
   * Get quiz questions for a lesson
   */
  async getQuizQuestions(lessonId: string): Promise<{
    success: boolean;
    questions: Array<{
      id: string;
      question: string;
      question_type: 'multiple_choice' | 'true_false' | 'short_answer';
      points: number;
      order: number;
      answers: Array<{
        id: string;
        answer_text: string;
        is_correct: boolean;
        order: number;
      }>;
    }>;
  }> {
    try {
      const response = await apiClient.get(`/lessons/${lessonId}/quiz/questions`);
      return response.data;
    } catch (error) {
      console.error('Error fetching quiz questions:', error);
      throw new Error('Failed to fetch quiz questions');
    }
  }

  /**
   * Start a quiz attempt
   */
  async startQuizAttempt(lessonId: string): Promise<{
    success: boolean;
    attempt: {
      id: string;
      user_id: string;
      lesson_id: string;
      score: number;
      total_points: number;
      percentage: number;
      passed: boolean;
      started_at: string;
      completed_at?: string;
    };
  }> {
    try {
      const response = await apiClient.post(`/lessons/${lessonId}/quiz/start`);
      return response.data;
    } catch (error) {
      console.error('Error starting quiz attempt:', error);
      throw new Error('Failed to start quiz attempt');
    }
  }

  /**
   * Submit quiz answers
   */
  async submitQuizAnswers(lessonId: string, data: {
    attempt_id: string;
    answers: Array<{
      question_id: string;
      user_answer: string;
    }>;
  }): Promise<{
    success: boolean;
    attempt: any;
    responses: any[];
    score: number;
    total_points: number;
    percentage: number;
    passed: boolean;
  }> {
    try {
      const response = await apiClient.post(`/lessons/${lessonId}/quiz/submit`, data);
      return response.data;
    } catch (error) {
      console.error('Error submitting quiz answers:', error);
      throw new Error('Failed to submit quiz answers');
    }
  }

  /**
   * Get quiz results
   */
  async getQuizResults(lessonId: string): Promise<{
    success: boolean;
    attempt: any;
    responses: any[];
  }> {
    try {
      const response = await apiClient.get(`/lessons/${lessonId}/quiz/results`);
      return response.data;
    } catch (error) {
      console.error('Error fetching quiz results:', error);
      throw new Error('Failed to fetch quiz results');
    }
  }

  /**
   * Get quiz attempts for a lesson
   */
  async getQuizAttempts(lessonId: string): Promise<{
    success: boolean;
    attempts: any[];
  }> {
    try {
      const response = await apiClient.get(`/lessons/${lessonId}/quiz/attempts`);
      return response.data;
    } catch (error) {
      console.error('Error fetching quiz attempts:', error);
      throw new Error('Failed to fetch quiz attempts');
    }
  }
}

export const lessonService = new LessonService(); 