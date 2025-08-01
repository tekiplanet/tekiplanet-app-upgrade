<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::withCount('products')->get();
        return view('admin.products.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_categories,name',
            'description' => 'nullable|string',
            'icon_name' => 'nullable|string|max:50'
        ]);

        try {
            $category = ProductCategory::create([
                'id' => Str::uuid(),
                'name' => $validated['name'],
                'description' => $validated['description'],
                'icon_name' => $validated['icon_name'],
                'count' => 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, ProductCategory $category)
    {
        \Log::info('Category Update Request:', [
            'category_id' => $category->id,
            'request_data' => $request->all()
        ]);

        try {
            $validated = $request->validate([
                'name' => "required|string|max:255|unique:product_categories,name,{$category->id},id",
                'description' => 'nullable|string',
                'icon_name' => 'nullable|string|max:50'
            ]);

            $category->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'category' => $category
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['name'][0] ?? 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Category update failed:', [
                'category_id' => $category->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category: ' . $e->getMessage()
            ], 500);
        }
    }
} 