<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FileCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminFileCategoryController extends Controller
{
    /**
     * Display the file categories management page
     */
    public function index()
    {
        return view('admin.file-management.partials.categories');
    }

    /**
     * Get all file categories (API)
     */
    public function list(): JsonResponse
    {
        $categories = FileCategory::ordered()->get();
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get a specific file category
     */
    public function show(string $id): JsonResponse
    {
        $category = FileCategory::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Create a new file category
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:file_categories,name',
            'description' => 'nullable|string',
            'allowed_extensions' => 'required|array|min:1',
            'allowed_extensions.*' => 'string|max:10',
            'max_file_size' => 'required|integer|min:1024', // Minimum 1KB
            'resource_type' => 'required|in:image,video,raw',
            'requires_optimization' => 'boolean',
            'cloudinary_options' => 'nullable|array',
            'sort_order' => 'integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category = FileCategory::create([
            'id' => Str::uuid(),
            'name' => $request->name,
            'description' => $request->description,
            'allowed_extensions' => array_map('strtolower', $request->allowed_extensions),
            'max_file_size' => $request->max_file_size,
            'resource_type' => $request->resource_type,
            'requires_optimization' => $request->boolean('requires_optimization', false),
            'cloudinary_options' => $request->cloudinary_options,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Update a file category
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $category = FileCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:file_categories,name,' . $id,
            'description' => 'nullable|string',
            'allowed_extensions' => 'required|array|min:1',
            'allowed_extensions.*' => 'string|max:10',
            'max_file_size' => 'required|integer|min:1024',
            'resource_type' => 'required|in:image,video,raw',
            'requires_optimization' => 'boolean',
            'cloudinary_options' => 'nullable|array',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'allowed_extensions' => array_map('strtolower', $request->allowed_extensions),
            'max_file_size' => $request->max_file_size,
            'resource_type' => $request->resource_type,
            'requires_optimization' => $request->boolean('requires_optimization', false),
            'cloudinary_options' => $request->cloudinary_options,
            'sort_order' => $request->sort_order ?? $category->sort_order,
            'is_active' => $request->boolean('is_active', $category->is_active)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File category updated successfully',
            'data' => $category
        ]);
    }

    /**
     * Delete a file category
     */
    public function destroy(string $id): JsonResponse
    {
        $category = FileCategory::findOrFail($id);

        // Check if category has files
        if ($category->files()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category that has files. Please move or delete the files first.'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'File category deleted successfully'
        ]);
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(string $id): JsonResponse
    {
        $category = FileCategory::findOrFail($id);
        
        $category->update([
            'is_active' => !$category->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category status updated successfully',
            'data' => [
                'id' => $category->id,
                'is_active' => $category->is_active
            ]
        ]);
    }

    /**
     * Get category statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_categories' => FileCategory::count(),
            'active_categories' => FileCategory::where('is_active', true)->count(),
            'categories_with_files' => FileCategory::whereHas('files')->count(),
            'categories_by_type' => [
                'image' => FileCategory::where('resource_type', 'image')->count(),
                'video' => FileCategory::where('resource_type', 'video')->count(),
                'raw' => FileCategory::where('resource_type', 'raw')->count()
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Reorder categories
     */
    public function reorder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'categories' => 'required|array',
            'categories.*.id' => 'required|uuid|exists:file_categories,id',
            'categories.*.sort_order' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->categories as $item) {
            FileCategory::where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Categories reordered successfully'
        ]);
    }
}
