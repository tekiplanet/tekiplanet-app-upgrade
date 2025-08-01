@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Products
        </h2>
        <a href="{{ route('admin.products.create') }}" 
           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Create Product
        </a>
    </div>

    <!-- Filters -->
    <div class="mb-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
        <form action="{{ route('admin.products.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="col-span-1 md:col-span-2">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search products..."
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>

            <!-- Category Filter -->
            <div>
                <select name="category" 
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Brand Filter -->
            <div>
                <select name="brand" 
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="">All Brands</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sort -->
            <div class="col-span-1 md:col-span-3">
                <select name="sort" 
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" 
                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($products as $product)
            <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 overflow-hidden">
                <!-- Product Image -->
                <div class="aspect-w-16 aspect-h-9">
                    <img src="{{ $product->images->where('is_primary', true)->first()?->image_url ?? 'https://via.placeholder.com/300' }}" 
                         alt="{{ $product->name }}"
                         class="object-cover w-full h-48">
                </div>

                <!-- Product Details -->
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                        {{ $product->name }}
                    </h3>
                    
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        {{ Str::limit($product->short_description, 100) }}
                    </p>

                    <!-- Category and Brand -->
                    <div class="flex gap-2 mb-2">
                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                            {{ $product->category->name }}
                        </span>
                        @if($product->brand)
                            <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded-full">
                                {{ $product->brand->name }}
                            </span>
                        @endif
                    </div>

                    <!-- Price and Stock -->
                    <div class="flex justify-between items-center mt-4">
                        <span class="text-lg font-bold text-gray-700 dark:text-gray-200">
                            ₦{{ number_format($product->price, 2) }}
                        </span>
                        <span class="text-sm {{ $product->stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $product->stock > 0 ? 'In Stock' : 'Out of Stock' }}
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="mt-4">
                        <a href="{{ route('admin.products.show', $product) }}" 
                           class="block w-full px-4 py-2 bg-blue-600 text-white text-center rounded-lg hover:bg-blue-700">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="text-center py-8 bg-white dark:bg-gray-800 rounded-lg">
                    <p class="text-gray-500 dark:text-gray-400">No products found</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $products->links() }}
    </div>
</div>
@endsection 