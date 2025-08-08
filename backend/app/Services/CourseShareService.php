<?php

namespace App\Services;

use App\Models\UserCourseShare;
use App\Models\UserConversionTask;
use App\Models\Course;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CourseShareService
{
    /**
     * Generate a course share link for a user conversion task
     */
    public function generateShareLink(UserConversionTask $userTask, Course $course): string
    {
        // Check if share link already exists
        $existingShare = UserCourseShare::where('user_conversion_task_id', $userTask->id)
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->first();

        if ($existingShare && !$existingShare->hasExpired()) {
            return $existingShare->share_link;
        }

        // Create new share link
        $shareLink = UserCourseShare::create([
            'user_id' => $userTask->user_id,
            'user_conversion_task_id' => $userTask->id,
            'course_id' => $course->id,
            'share_link' => $userTask->generateCourseShareLink($course->id),
            'shared_at' => now(),
            'expires_at' => now()->addDays(7), // 7-day expiration
            'status' => 'active',
        ]);

        Log::info('Course share link created', [
            'user_conversion_task_id' => $userTask->id,
            'course_id' => $course->id,
            'share_link_id' => $shareLink->id,
        ]);

        return $shareLink->share_link;
    }

    /**
     * Track a course share link click
     */
    public function trackShareClick(string $shareLink, string $visitorIp = null, string $userAgent = null, string $referrer = null): bool
    {
        try {
            // Extract share ID from the URL
            $shareId = $this->extractShareIdFromUrl($shareLink);
            
            if (!$shareId) {
                Log::warning('Could not extract share ID from URL', ['share_link' => $shareLink]);
                return false;
            }

            // Find the share link record
            $courseShare = UserCourseShare::where('id', $shareId)
                ->orWhere('share_link', $shareLink)
                ->first();

            if (!$courseShare) {
                Log::warning('Course share link not found', [
                    'share_id' => $shareId,
                    'share_link' => $shareLink
                ]);
                return false;
            }

            // Check if link is active and not expired
            if (!$courseShare->isActive()) {
                Log::info('Course share link is not active or expired', [
                    'share_id' => $courseShare->id,
                    'status' => $courseShare->status,
                    'expires_at' => $courseShare->expires_at
                ]);
                return false;
            }

            // Record the visit
            $courseShare->recordVisit($visitorIp, $userAgent, $referrer);

            Log::info('Course share click tracked', [
                'share_id' => $courseShare->id,
                'course_id' => $courseShare->course_id,
                'user_id' => $courseShare->user_id,
                'click_count' => $courseShare->click_count,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error tracking course share click', [
                'share_link' => $shareLink,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Record an enrollment through a course share link
     */
    public function recordEnrollment(string $shareLinkId, string $enrollmentId, string $enrolledUserId, float $enrollmentAmount): bool
    {
        try {
            $courseShare = UserCourseShare::find($shareLinkId);
            
            if (!$courseShare) {
                Log::warning('Course share link not found for enrollment tracking', [
                    'share_link_id' => $shareLinkId,
                    'enrollment_id' => $enrollmentId
                ]);
                return false;
            }

            // Check if link is active
            if (!$courseShare->isActive()) {
                Log::info('Course share link is not active for enrollment', [
                    'share_id' => $courseShare->id,
                    'status' => $courseShare->status
                ]);
                return false;
            }

            // Check for self-referral prevention
            if ($courseShare->user_id === $enrolledUserId) {
                Log::info('Self-referral prevented for course share', [
                    'share_link_id' => $shareLinkId,
                    'user_id' => $enrolledUserId,
                    'enrollment_id' => $enrollmentId
                ]);
                return false;
            }

            // Check for duplicate enrollment tracking
            $existingEnrollment = $courseShare->enrollments()
                ->where('enrollment_id', $enrollmentId)
                ->first();

            if ($existingEnrollment) {
                Log::info('Duplicate enrollment tracking prevented', [
                    'share_link_id' => $shareLinkId,
                    'enrollment_id' => $enrollmentId
                ]);
                return false;
            }

            // Record the enrollment
            $courseShare->recordEnrollment($enrollmentId, $enrolledUserId, $enrollmentAmount);

            Log::info('Course share enrollment recorded', [
                'share_id' => $courseShare->id,
                'course_id' => $courseShare->course_id,
                'enrollment_id' => $enrollmentId,
                'enrolled_user_id' => $enrolledUserId,
                'enrollment_count' => $courseShare->enrollment_count,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error recording course share enrollment', [
                'share_link_id' => $shareLinkId,
                'enrollment_id' => $enrollmentId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Extract share ID from URL
     */
    private function extractShareIdFromUrl(string $shareLink): ?string
    {
        // Try to extract from query parameter
        $parsedUrl = parse_url($shareLink);
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
            if (isset($queryParams['share'])) {
                return $queryParams['share'];
            }
        }

        // Try to extract from path (legacy format)
        $pathParts = explode('/', trim($parsedUrl['path'] ?? '', '/'));
        $lastPart = end($pathParts);
        
        // Check if it's a valid UUID
        if (Str::isUuid($lastPart)) {
            return $lastPart;
        }

        return null;
    }

    /**
     * Get analytics for a specific course share
     */
    public function getShareAnalytics(string $shareId): array
    {
        $courseShare = UserCourseShare::with(['course', 'visits', 'enrollments'])->find($shareId);
        
        if (!$courseShare) {
            return [];
        }

        return [
            'share_id' => $courseShare->id,
            'course' => $courseShare->course,
            'click_count' => $courseShare->click_count,
            'enrollment_count' => $courseShare->enrollment_count,
            'conversion_rate' => $courseShare->getConversionRate(),
            'status' => $courseShare->status,
            'expires_at' => $courseShare->expires_at,
            'shared_at' => $courseShare->shared_at,
            'recent_visits' => $courseShare->visits()->latest('visited_at')->take(10)->get(),
            'recent_enrollments' => $courseShare->enrollments()->latest('enrolled_at')->take(10)->get(),
        ];
    }

    /**
     * Get overall course share analytics
     */
    public function getOverallAnalytics(): array
    {
        $totalShares = UserCourseShare::count();
        $activeShares = UserCourseShare::where('status', 'active')->where('expires_at', '>', now())->count();
        $totalClicks = UserCourseShare::sum('click_count');
        $totalEnrollments = UserCourseShare::sum('enrollment_count');
        
        $overallConversionRate = $totalClicks > 0 ? round(($totalEnrollments / $totalClicks) * 100, 2) : 0;

        return [
            'total_shares' => $totalShares,
            'active_shares' => $activeShares,
            'total_clicks' => $totalClicks,
            'total_enrollments' => $totalEnrollments,
            'overall_conversion_rate' => $overallConversionRate,
        ];
    }
}
