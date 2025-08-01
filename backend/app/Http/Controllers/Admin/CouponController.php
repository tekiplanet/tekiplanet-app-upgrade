<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::withCount('usages')
            ->when($request->search, function($query, $search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status, function($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->latest()
            ->paginate(10);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code|max:50',
            'value_type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'required|numeric|min:0',
            'max_discount' => 'required_if:value_type,percentage|nullable|numeric|min:0',
            'starts_at' => 'required|date',
            'expires_at' => 'required|date|after:starts_at',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        try {
            $coupon = Coupon::create([
                'id' => Str::uuid(),
                ...$validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Coupon created successfully',
                'coupon' => $coupon
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create coupon: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Coupon $coupon)
    {
        $coupon->load(['usages' => function($query) {
            $query->with('user')->latest();
        }]);

        return view('admin.coupons.show', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,'.$coupon->id,
            'value_type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'required|numeric|min:0',
            'max_discount' => 'required_if:value_type,percentage|nullable|numeric|min:0',
            'starts_at' => 'required|date',
            'expires_at' => 'required|date|after:starts_at',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        try {
            $coupon->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Coupon updated successfully',
                'coupon' => $coupon
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update coupon: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Coupon $coupon)
    {
        try {
            $coupon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Coupon deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete coupon: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggle(Coupon $coupon)
    {
        try {
            $coupon->update(['is_active' => !$coupon->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Coupon status updated successfully',
                'is_active' => $coupon->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update coupon status: ' . $e->getMessage()
            ], 500);
        }
    }
} 