<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductFeatureController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'feature' => 'required|string|max:255',
        ]);

        try {
            $feature = $product->features()->create([
                'id' => Str::uuid(),
                'feature' => $validated['feature']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Feature added successfully',
                'feature' => $feature
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add feature: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(ProductFeature $feature)
    {
        try {
            $feature->delete();

            return response()->json([
                'success' => true,
                'message' => 'Feature deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete feature: ' . $e->getMessage()
            ], 500);
        }
    }
} 