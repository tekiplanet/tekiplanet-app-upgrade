<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Cart;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        // Get authenticated user
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $coupon = Coupon::where('code', $request->code)->first();
        
        if (!$coupon) {
            return response()->json([
                'message' => 'Invalid coupon code'
            ], 422);
        }

        if (!$coupon->isValid()) {
            return response()->json([
                'message' => 'This coupon has expired or is no longer valid'
            ], 422);
        }

        if (!$coupon->canBeUsedByUser($user)) {
            return response()->json([
                'message' => 'You have exceeded the usage limit for this coupon'
            ], 422);
        }

        // Get cart total
        $cart = Cart::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json([
                'message' => 'No active cart found'
            ], 422);
        }

        if ($cart->current_total < $coupon->min_order_amount) {
            return response()->json([
                'message' => "This coupon requires a minimum order amount of " . number_format($coupon->min_order_amount)
            ], 422);
        }

        $discount = $coupon->calculateDiscount($cart->current_total);

        return response()->json([
            'message' => 'Coupon applied successfully',
            'coupon' => [
                'code' => $coupon->code,
                'discount' => $discount,
                'type' => $coupon->value_type,
                'value' => $coupon->value
            ]
        ]);
    }
} 