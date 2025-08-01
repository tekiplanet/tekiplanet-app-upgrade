<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingAddress;
use App\Models\ShippingZone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShippingAddressController extends Controller
{
    public function index(Request $request)
    {
        $query = ShippingAddress::with(['user', 'state']);

        // Search by name, email, phone, or address
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('address', 'like', '%' . $request->search . '%')
                  ->orWhere('city', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($query) use ($request) {
                      $query->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Filter by zone
        if ($request->filled('zone')) {
            $query->where('state_id', $request->zone);
        }

        // Filter by default status
        if ($request->filled('is_default')) {
            $query->where('is_default', $request->is_default === 'yes');
        }

        // Filter by user
        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }

        $addresses = $query->latest()->paginate(10);
        $addresses->appends($request->all());
        $zones = ShippingZone::all();
        $users = User::all();
        return view('admin.shipping.addresses.index', compact('addresses', 'zones', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:255',
            'state_id' => 'required|exists:shipping_zones,id',
            'is_default' => 'boolean'
        ]);

        try {
            // If this is set as default, unset other default addresses for this user
            if ($validated['is_default']) {
                ShippingAddress::where('user_id', $validated['user_id'])
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $address = ShippingAddress::create([
                'id' => Str::uuid(),
                ...$validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Shipping address created successfully',
                'address' => $address->load(['user', 'state'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create shipping address: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, ShippingAddress $address)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:255',
            'state_id' => 'required|exists:shipping_zones,id',
            'is_default' => 'boolean'
        ]);

        try {
            // If setting as default, unset other default addresses for this user
            if ($validated['is_default'] && !$address->is_default) {
                ShippingAddress::where('user_id', $validated['user_id'])
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $address->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Shipping address updated successfully',
                'address' => $address->load(['user', 'state'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shipping address: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(ShippingAddress $address)
    {
        try {
            // Check if address is used in any orders
            if ($address->orders()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete address that is associated with orders'
                ], 422);
            }

            $address->delete();

            return response()->json([
                'success' => true,
                'message' => 'Shipping address deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete shipping address: ' . $e->getMessage()
            ], 500);
        }
    }
} 