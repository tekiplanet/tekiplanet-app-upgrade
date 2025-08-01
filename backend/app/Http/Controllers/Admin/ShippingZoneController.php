<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShippingZoneController extends Controller
{
    public function index(Request $request)
    {
        $query = ShippingZone::withCount(['rates', 'addresses']);

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by has shipping methods
        if ($request->filled('has_methods')) {
            if ($request->has_methods === 'yes') {
                $query->has('rates');
            } elseif ($request->has_methods === 'no') {
                $query->doesntHave('rates');
            }
        }

        // Filter by has addresses
        if ($request->filled('has_addresses')) {
            if ($request->has_addresses === 'yes') {
                $query->has('addresses');
            } elseif ($request->has_addresses === 'no') {
                $query->doesntHave('addresses');
            }
        }

        $zones = $query->latest()->paginate(10)->withQueryString();
        $zones->appends($request->all());

        return view('admin.shipping.zones.index', compact('zones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:shipping_zones,name',
        ]);

        try {
            $zone = ShippingZone::create([
                'id' => Str::uuid(),
                'name' => $validated['name']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Shipping zone created successfully',
                'zone' => $zone
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create shipping zone: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, ShippingZone $zone)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:shipping_zones,name,' . $zone->id,
        ]);

        try {
            $zone->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Shipping zone updated successfully',
                'zone' => $zone
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shipping zone: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(ShippingZone $zone)
    {
        try {
            if ($zone->addresses()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete zone with associated addresses'
                ], 422);
            }

            $zone->delete();

            return response()->json([
                'success' => true,
                'message' => 'Shipping zone deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete shipping zone: ' . $e->getMessage()
            ], 500);
        }
    }
} 