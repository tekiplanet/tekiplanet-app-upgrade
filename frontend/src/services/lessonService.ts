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
  }> {
    try {
      const response = await apiClient.get(`/lessons/${lessonId}/access`);
      return response.data;
    } catch (error) {
      console.error('Error checking lesson access:', error);
      throw new Error('Failed to check lesson access');
    }
  }
}

export const lessonService = new LessonService(); 