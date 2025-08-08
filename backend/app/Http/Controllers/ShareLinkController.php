<?php

namespace App\Http\Controllers;

use App\Models\UserProductShare;
use App\Services\ProductShareService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ShareLinkController extends Controller
{
    protected $productShareService;

    public function __construct(ProductShareService $productShareService)
    {
        $this->productShareService = $productShareService;
    }

    /**
     * Track a share link visit and return product details.
     */
    public function trackVisit(Request $request): JsonResponse
    {
        // Accept either full share link URL in 'share_link' or the share id in 'share_id'
        $shareLink = $request->input('share_link');
        $shareId = $request->input('share_id');

        if (!$shareLink && !$shareId) {
            return response()->json([
                'success' => false,
                'message' => 'share_link or share_id is required'
            ], 422);
        }
        $visitorIp = $request->ip();
        $userAgent = $request->userAgent();
        $referrer = $request->header('referer');

        try {
            // Track the visit using either identifier
            $identifier = $shareId ?: $shareLink;
            $share = $this->productShareService->trackShareClick($identifier, $visitorIp, $userAgent, $referrer);

            if (!$share) {
                return response()->json([
                    'success' => false,
                    'message' => 'Share link not found or expired'
                ], 404);
            }

            // Load product details
            $product = $share->product;

            return response()->json([
                'success' => true,
                'data' => [
                    'share_id' => $share->id,
                    'product' => $product,
                    'analytics' => [
                        'click_count' => $share->click_count,
                        'purchase_count' => $share->purchase_count,
                        'conversion_rate' => $share->getConversionRate(),
                        'expires_at' => $share->expires_at,
                        'is_active' => $share->isActive()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to track share link visit', [
                'identifier' => $shareId ?: $shareLink,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to track share link visit'
            ], 500);
        }
    }

    /**
     * Get analytics for a share link.
     */
    public function getAnalytics(Request $request, string $shareId): JsonResponse
    {
        try {
            $share = UserProductShare::with(['product', 'visits', 'purchases'])
                ->findOrFail($shareId);

            $analytics = [
                'share_id' => $share->id,
                'product' => $share->product,
                'click_count' => $share->click_count,
                'purchase_count' => $share->purchase_count,
                'conversion_rate' => $share->getConversionRate(),
                'expires_at' => $share->expires_at,
                'is_active' => $share->isActive(),
                'has_reached_target' => $share->hasReachedTarget(),
                'target' => $share->userConversionTask->task->share_target ?? 1,
                'recent_visits' => $share->visits()
                    ->orderBy('visited_at', 'desc')
                    ->limit(10)
                    ->get(),
                'recent_purchases' => $share->purchases()
                    ->with('purchaser')
                    ->orderBy('purchased_at', 'desc')
                    ->limit(10)
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get share link analytics', [
                'share_id' => $shareId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get analytics'
            ], 500);
        }
    }

    /**
     * Get overall share analytics for admin.
     */
    public function getOverallAnalytics(Request $request): JsonResponse
    {
        try {
            $totalShares = UserProductShare::count();
            $activeShares = UserProductShare::where('status', 'active')->count();
            $expiredShares = UserProductShare::where('expires_at', '<', now())->count();
            $completedShares = UserProductShare::where('status', 'completed')->count();

            $totalClicks = UserProductShare::sum('click_count');
            $totalPurchases = UserProductShare::sum('purchase_count');
            $overallConversionRate = $totalClicks > 0 ? round(($totalPurchases / $totalClicks) * 100, 2) : 0;

            $topProducts = UserProductShare::with('product')
                ->selectRaw('product_id, COUNT(*) as share_count, SUM(click_count) as total_clicks, SUM(purchase_count) as total_purchases')
                ->groupBy('product_id')
                ->orderBy('total_purchases', 'desc')
                ->limit(10)
                ->get();

            $analytics = [
                'summary' => [
                    'total_shares' => $totalShares,
                    'active_shares' => $activeShares,
                    'expired_shares' => $expiredShares,
                    'completed_shares' => $completedShares,
                    'total_clicks' => $totalClicks,
                    'total_purchases' => $totalPurchases,
                    'overall_conversion_rate' => $overallConversionRate
                ],
                'top_products' => $topProducts
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get overall share analytics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get overall analytics'
            ], 500);
        }
    }
}
