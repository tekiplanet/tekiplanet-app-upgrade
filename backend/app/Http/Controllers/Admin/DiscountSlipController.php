<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DiscountSlip;
use App\Models\User;
use App\Models\ConversionTask;
use Carbon\Carbon;

class DiscountSlipController extends Controller
{
    public function index()
    {
        $query = DiscountSlip::with(['user', 'userConversionTask.task']);

        // Search functionality
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('discount_code', 'like', "%{$search}%")
                  ->orWhere('service_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if (request('status')) {
            $status = request('status');
            if ($status === 'active') {
                $query->where('is_used', false)->where('expires_at', '>', now());
            } elseif ($status === 'used') {
                $query->where('is_used', true);
            } elseif ($status === 'expired') {
                $query->where('expires_at', '<', now());
            }
        }

        $discountSlips = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.discount-slips.index', compact('discountSlips'));
    }

    public function show(DiscountSlip $discountSlip)
    {
        $discountSlip->load(['user', 'userConversionTask.task']);
        return view('admin.discount-slips.show', compact('discountSlip'));
    }

    public function edit(DiscountSlip $discountSlip)
    {
        $discountSlip->load(['user', 'userConversionTask.task']);
        return view('admin.discount-slips.edit', compact('discountSlip'));
    }

    public function update(Request $request, DiscountSlip $discountSlip)
    {
        $validated = $request->validate([
            'service_name' => 'required|string|max:255',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'expires_at' => 'required|date|after:now',
            'terms_conditions' => 'nullable|string',
            'is_used' => 'boolean',
        ]);

        // If marking as used, set used_at timestamp
        if ($validated['is_used'] && !$discountSlip->is_used) {
            $validated['used_at'] = now();
        } elseif (!$validated['is_used'] && $discountSlip->is_used) {
            // If unmarking as used, clear used_at
            $validated['used_at'] = null;
        }

        $discountSlip->update($validated);

        return redirect()->route('admin.discount-slips.index')
            ->with('success', 'Discount slip updated successfully.');
    }

    public function toggleUsed(DiscountSlip $discountSlip)
    {
        if ($discountSlip->is_used) {
            // Unmark as used
            $discountSlip->update([
                'is_used' => false,
                'used_at' => null
            ]);
            $message = 'Discount slip marked as unused.';
        } else {
            // Mark as used
            $discountSlip->update([
                'is_used' => true,
                'used_at' => now()
            ]);
            $message = 'Discount slip marked as used.';
        }

        return redirect()->route('admin.discount-slips.index')
            ->with('success', $message);
    }

    public function extendExpiration(Request $request, DiscountSlip $discountSlip)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $newExpiration = Carbon::parse($discountSlip->expires_at)->addDays($validated['days']);
        
        $discountSlip->update([
            'expires_at' => $newExpiration
        ]);

        return redirect()->route('admin.discount-slips.index')
            ->with('success', "Discount slip expiration extended by {$validated['days']} days.");
    }

    public function destroy(DiscountSlip $discountSlip)
    {
        $discountSlip->delete();
        return redirect()->route('admin.discount-slips.index')
            ->with('success', 'Discount slip deleted successfully.');
    }
}
