<?php

namespace App\Services;

use App\Models\UserConversionTask;
use App\Models\UserProductShare;
use App\Models\ProductSharePurchase;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ProductShareService
{
    /**
     * Generate a unique share link for a user's product task.
     */
    public function generateShareLink(UserConversionTask $userTask, Product $product): string
    {
        // Check if a share record already exists
        $existingShare = UserProductShare::where('user_conversion_task_id', $userTask->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingShare) {
            return $existingShare->share_link;
        }

        // Create a new share record
        $share = UserProductShare::create([
            'user_id' => $userTask->user_id,
            'user_conversion_task_id' => $userTask->id,
            'product_id' => $product->id,
            'share_link' => $userTask->generateProductShareLink($product->id),
            'shared_at' => now(),
            'status' => 'active'
        ]);

        // Set expiration date (7 days from now)
        $share->setExpiration(7);

        return $share->share_link;
    }

    /**
     * Track a share link click with detailed analytics.
     */
    public function trackShareClick(string $shareLink, string $visitorIp = null, string $userAgent = null, string $referrer = null): ?UserProductShare
    {
        $share = UserProductShare::where('share_link', $shareLink)
            ->where('status', 'active')
            ->first();

        if ($share && $share->isActive()) {
            // Record the visit with analytics
            $share->recordVisit($visitorIp, $userAgent, $referrer);
            
            Log::info('Share link clicked', [
                'share_id' => $share->id,
                'user_id' => $share->user_id,
                'product_id' => $share->product_id,
                'visitor_ip' => $visitorIp,
                'click_count' => $share->click_count,
                'conversion_rate' => $share->getConversionRate()
            ]);
        }

        return $share;
    }

    /**
     * Track a purchase made through a share link.
     */
    public function trackPurchase(string $shareLink, Order $order, User $purchaser): bool
    {
        $share = UserProductShare::where('share_link', $shareLink)
            ->where('status', 'active')
            ->first();

        if (!$share) {
            Log::warning('Share link not found for purchase tracking', [
                'share_link' => $shareLink,
                'order_id' => $order->id
            ]);
            return false;
        }

        // Check if this order already has a purchase record
        $existingPurchase = ProductSharePurchase::where('order_id', $order->id)->first();
        if ($existingPurchase) {
            Log::info('Purchase already tracked for this order', [
                'order_id' => $order->id,
                'share_id' => $share->id
            ]);
            return true;
        }

        // Create purchase record
        $purchase = ProductSharePurchase::create([
            'user_product_share_id' => $share->id,
            'order_id' => $order->id,
            'purchaser_user_id' => $purchaser->id,
            'purchased_at' => now(),
            'order_amount' => $order->total,
            'status' => 'completed'
        ]);

        // Mark the purchase as completed
        $purchase->markAsCompleted();

        Log::info('Purchase tracked through share link', [
            'share_id' => $share->id,
            'order_id' => $order->id,
            'purchaser_id' => $purchaser->id,
            'amount' => $order->total
        ]);

        return true;
    }

    /**
     * Check if a user conversion task is completed based on share targets.
     */
    public function checkTaskCompletion(UserConversionTask $userTask): bool
    {
        $share = $userTask->productShare;
        
        if (!$share) {
            return false;
        }

        $target = $userTask->task->share_target ?? 1;
        $completed = $share->purchase_count;

        if ($completed >= $target) {
            if ($userTask->status !== 'completed') {
                $userTask->status = 'completed';
                $userTask->completed_at = now();
                $userTask->share_count = $completed;
                $userTask->save();

                Log::info('Share task completed', [
                    'user_task_id' => $userTask->id,
                    'user_id' => $userTask->user_id,
                    'target' => $target,
                    'completed' => $completed
                ]);
            }
            return true;
        }

        return false;
    }

    /**
     * Get share statistics for a user.
     */
    public function getUserShareStats(User $user): array
    {
        $shares = UserProductShare::where('user_id', $user->id)->get();
        
        $totalShares = $shares->count();
        $activeShares = $shares->where('status', 'active')->count();
        $completedShares = $shares->where('status', 'completed')->count();
        $totalPurchases = $shares->sum('purchase_count');

        return [
            'total_shares' => $totalShares,
            'active_shares' => $activeShares,
            'completed_shares' => $completedShares,
            'total_purchases' => $totalPurchases
        ];
    }

    /**
     * Get share statistics for a product.
     */
    public function getProductShareStats(Product $product): array
    {
        $shares = UserProductShare::where('product_id', $product->id)->get();
        
        $totalShares = $shares->count();
        $activeShares = $shares->where('status', 'active')->count();
        $completedShares = $shares->where('status', 'completed')->count();
        $totalPurchases = $shares->sum('purchase_count');

        return [
            'total_shares' => $totalShares,
            'active_shares' => $activeShares,
            'completed_shares' => $completedShares,
            'total_purchases' => $totalPurchases
        ];
    }
}
