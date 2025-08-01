<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSpecification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductSpecificationController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        try {
            $specification = $product->specifications()->create([
                'id' => Str::uuid(),
                'key' => $validated['key'],
                'value' => $validated['value']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Specification added successfully',
                'specification' => $specification
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add specification: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(ProductSpecification $specification)
    {
        try {
            $specification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Specification deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete specification: ' . $e->getMessage()
            ], 500);
        }
    }
} 