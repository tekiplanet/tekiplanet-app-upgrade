<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'images'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->category, function ($query, $category) {
                $query->where('category_id', $category);
            })
            ->when($request->brand, function ($query, $brand) {
                $query->where('brand_id', $brand);
            })
            ->when($request->sort, function ($query, $sort) {
                switch ($sort) {
                    case 'price_asc':
                        $query->orderBy('price', 'asc');
                        break;
                    case 'price_desc':
                        $query->orderBy('price', 'desc');
                        break;
                    case 'newest':
                        $query->latest();
                        break;
                    case 'name':
                        $query->orderBy('name');
                        break;
                }
            }, function ($query) {
                $query->latest();
            });

        $products = $query->paginate(12)->withQueryString();
        $categories = ProductCategory::all();
        $brands = Brand::all();

        return view('admin.products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $categories = ProductCategory::all();
        $brands = Brand::all();
        return view('admin.products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:product_categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'stock' => 'required|integer|min:0',
            'is_featured' => 'boolean',
            'image_url' => 'required|url' // For primary image
        ]);

        try {
            $product = Product::create([
                'id' => Str::uuid(),
                'name' => $validated['name'],
                'description' => $validated['description'],
                'short_description' => $validated['short_description'],
                'price' => $validated['price'],
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'],
                'stock' => $validated['stock'],
                'is_featured' => $request->has('is_featured'),
                'rating' => 0,
                'reviews_count' => 0
            ]);

            // Create primary image
            $product->images()->create([
                'image_url' => $validated['image_url'],
                'is_primary' => true,
                'order' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'redirect' => route('admin.products.show', $product)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'images']);
        $categories = ProductCategory::all();
        $brands = Brand::all();
        return view('admin.products.show', compact('product', 'categories', 'brands'));
    }

    public function edit(Product $product)
    {
        $categories = ProductCategory::all();
        $brands = Brand::all();
        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:product_categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'stock' => 'required|integer|min:0',
            'is_featured' => 'boolean'
        ]);

        try {
            $product->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'redirect' => route('admin.products.show', $product)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }
} 