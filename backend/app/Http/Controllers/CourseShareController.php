<?php

namespace App\Http\Controllers;

use App\Services\CourseShareService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CourseShareController extends Controller
{
    protected $courseShareService;

    public function __construct(CourseShareService $courseShareService)
    {
        $this->courseShareService = $courseShareService;
    }

    /**
     * Track a course share link visit (public endpoint)
     */
    public function trackVisit(Request $request): JsonResponse
    {
        $request->validate([
            'share_link' => 'required|string',
            'visitor_ip' => 'nullable|string',
            'user_agent' => 'nullable|string',
            'referrer' => 'nullable|string',
        ]);

        try {
            $success = $this->courseShareService->trackShareClick(
                $request->share_link,
                $request->visitor_ip,
                $request->user_agent,
                $request->referrer
            );

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Visit tracked successfully' : 'Visit tracking failed'
            ]);
        } catch (\Exception $e) {
            Log::error('Course share visit tracking error', [
                'share_link' => $request->share_link,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Visit tracking failed'
            ], 500);
        }
    }

    /**
     * Get analytics for a specific course share
     */
    public function getShareAnalytics(string $shareId): JsonResponse
    {
        try {
            $analytics = $this->courseShareService->getShareAnalytics($shareId);

            if (empty($analytics)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Share link not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            Log::error('Course share analytics error', [
                'share_id' => $shareId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch analytics'
            ], 500);
        }
    }

    /**
     * Get overall course share analytics
     */
    public function getOverallAnalytics(): JsonResponse
    {
        try {
            $analytics = $this->courseShareService->getOverallAnalytics();

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            Log::error('Course share overall analytics error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch overall analytics'
            ], 500);
        }
    }
}
