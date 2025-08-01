<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceCategoryController extends Controller
{
    public function index()
    {
        $categories = ServiceCategory::withCount('services')
            ->when(request('search'), function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return view('admin.services.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.services.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:service_categories',
            'description' => 'required|string',
            'icon_name' => 'required|string',
            'is_featured' => 'boolean'
        ]);

        ServiceCategory::create($validated);

        return redirect()
            ->route('admin.service-categories.index')
            ->with('success', 'Service category created successfully');
    }

    public function edit(ServiceCategory $serviceCategory)
    {
        return view('admin.services.categories.edit', compact('serviceCategory'));
    }

    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:service_categories,name,' . $serviceCategory->id,
            'description' => 'required|string',
            'icon_name' => 'required|string',
            'is_featured' => 'boolean'
        ]);

        $serviceCategory->update($validated);

        return redirect()
            ->route('admin.service-categories.index')
            ->with('success', 'Service category updated successfully');
    }

    public function destroy(ServiceCategory $serviceCategory)
    {
        if ($serviceCategory->services()->exists()) {
            return back()->with('error', 'Cannot delete category with associated services');
        }

        $serviceCategory->delete();

        return back()->with('success', 'Service category deleted successfully');
    }

    public function toggleFeatured(ServiceCategory $serviceCategory)
    {
        $serviceCategory->update([
            'is_featured' => !$serviceCategory->is_featured
        ]);

        return response()->json([
            'success' => true,
            'featured' => $serviceCategory->is_featured
        ]);
    }
} 