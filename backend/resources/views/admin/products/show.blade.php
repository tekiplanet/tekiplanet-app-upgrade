@php
    use App\Models\Setting;
@endphp

@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.products.index') }}" 
               class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                {{ $product->name }}
            </h2>
        </div>
        <button onclick="openEditModal()" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Edit Product
        </button>
    </div>

    <!-- Product Details -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Images Section -->
        <div class="md:col-span-1 bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Images</h3>
                <button onclick="openImageModal()" 
                        class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                    Add Image
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Image</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($product->images as $image)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-4">
                                <img src="{{ $image->image_url }}" 
                                     data-image-id="{{ $image->id }}"
                                     alt="{{ $product->name }}"
                                     class="w-24 h-24 object-cover rounded-lg">
                            </td>
                            <td class="px-4 py-4">
                                @if($image->is_primary)
                                    <span class="bg-green-500 text-white px-2 py-1 rounded-md text-xs inline-block">
                                        Primary
                                    </span>
                                @else
                                    <button onclick="setPrimaryImage('{{ $image->id }}')"
                                            class="bg-blue-500 text-white px-2 py-1 rounded-md text-xs hover:bg-blue-600">
                                        Set as Primary
                                    </button>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right space-x-2">
                                <button onclick="editImage('{{ $image->id }}')"
                                        class="bg-yellow-500 text-white px-2 py-1 rounded-md text-xs hover:bg-yellow-600">
                                    Edit
                                </button>
                                <button onclick="deleteImage('{{ $image->id }}')"
                                        class="bg-red-500 text-white px-2 py-1 rounded-md text-xs hover:bg-red-600">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                No images available
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Details Section -->
        <div class="md:col-span-2 space-y-6">
            <!-- Basic Details -->
            <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Category</h3>
                        <p class="mt-1 text-lg text-gray-900 dark:text-white">
                            {{ $product->category->name }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Brand</h3>
                        <p class="mt-1 text-lg text-gray-900 dark:text-white">
                            {{ $product->brand ? $product->brand->name : 'N/A' }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Price</h3>
                        <p class="mt-1 text-lg text-gray-900 dark:text-white">
                            {{ Setting::getSetting('currency_symbol', 'Rp') }} {{ number_format($product->price, 2) }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Stock</h3>
                        <p class="mt-1 text-lg text-gray-900 dark:text-white">
                            {{ $product->stock }}
                        </p>
                    </div>

                    <div class="col-span-2">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Short Description</h3>
                        <p class="mt-1 text-gray-900 dark:text-white">
                            {{ $product->short_description }}
                        </p>
                    </div>

                    <div class="col-span-2">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Description</h3>
                        <p class="mt-1 text-gray-900 dark:text-white">
                            {{ $product->description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Features</h3>
                    <button onclick="openFeatureModal()" 
                            class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                        Add Feature
                    </button>
                </div>
                <div class="space-y-2">
                    @forelse($product->features as $feature)
                        <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-gray-700 dark:text-gray-300">{{ $feature->feature }}</span>
                            <button onclick="deleteFeature('{{ $feature->id }}')"
                                    class="text-red-500 hover:text-red-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400">No features added yet</p>
                    @endforelse
                </div>
            </div>

            <!-- Specifications Section -->
            <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Specifications</h3>
                    <button onclick="openSpecificationModal()" 
                            class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                        Add Specification
                    </button>
                </div>
                <div class="space-y-2">
                    @forelse($product->specifications as $spec)
                        <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $spec->key }}:</span>
                                <span class="ml-2 text-gray-600 dark:text-gray-400">{{ $spec->value }}</span>
                            </div>
                            <button onclick="deleteSpecification('{{ $spec->id }}')"
                                    class="text-red-500 hover:text-red-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400">No specifications added yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.products.partials.edit-modal')
@include('admin.products.partials.feature-modal')
@include('admin.products.partials.specification-modal')
@include('admin.products.partials.image-modal')
@endsection 