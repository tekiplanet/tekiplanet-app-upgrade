@extends('admin.layouts.app')

@section('content')
@php
    $currency = App\Models\Setting::getSetting('currency_symbol', '$');
@endphp

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <h1 class="text-2xl font-semibold text-gray-900">Sales Dashboard</h1>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Stats Overview Cards -->
        <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Sales Card -->
            <div class="relative group bg-white overflow-hidden rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-purple-100 rounded-lg">
                            <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                                    Total Sales
                                </dt>
                                <dd class="mt-1 text-2xl font-extrabold text-gray-900">
                                    {{ $currency }}{{ number_format($totalSales, 2) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Orders -->
            <div class="relative group bg-white overflow-hidden rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-emerald-100 rounded-lg">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                                    Today's Orders
                                </dt>
                                <dd class="mt-1 text-2xl font-extrabold text-gray-900">
                                    {{ $recentOrders->count() }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Count -->
            <div class="relative group bg-white overflow-hidden rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-orange-100 rounded-lg">
                            <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                                    Total Products
                                </dt>
                                <dd class="mt-1 text-2xl font-extrabold text-gray-900">
                                    {{ $productStats->count() }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-medium text-gray-900">Recent Orders</h2>
                        <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                            View all
                        </a>
                    </div>
                    <div class="flow-root">
                        <ul role="list" class="-my-5 divide-y divide-gray-200">
                            @forelse($recentOrders as $order)
                            <li class="py-5">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">
                                            Order #{{ $order->id }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $order->created_at->format('M d, Y H:i') }}
                                        </p>
                                        <p class="mt-1 text-sm font-medium text-gray-900">
                                            {{ $currency }}{{ number_format($order->total, 2) }}
                                        </p>
                                    </div>
                                    <div>
                                        <span @class([
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            'bg-green-100 text-green-800' => $order->status === 'completed',
                                            'bg-yellow-100 text-yellow-800' => $order->status === 'pending',
                                            'bg-blue-100 text-blue-800' => $order->status === 'processing',
                                            'bg-red-100 text-red-800' => $order->status === 'cancelled',
                                        ])>
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="py-5">
                                <p class="text-sm text-gray-500 text-center">No recent orders</p>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Recent Products -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-medium text-gray-900">Recent Products</h2>
                        <a href="{{ route('admin.products.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                            View all
                        </a>
                    </div>
                    <div class="flow-root">
                        <ul role="list" class="-my-5 divide-y divide-gray-200">
                            @forelse($productStats as $product)
                            <li class="py-5">
                                <div class="flex items-center space-x-4">
                                    @if($product->image)
                                    <div class="flex-shrink-0">
                                        <img class="h-12 w-12 rounded-lg object-cover" src="{{ $product->image }}" alt="{{ $product->name }}">
                                    </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ $product->name }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            SKU: {{ $product->sku }}
                                        </p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900">
                                            {{ $currency }}{{ number_format($product->price, 2) }}
                                        </p>
                                    </div>
                                    <div>
                                        <span @class([
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            'bg-green-100 text-green-800' => $product->in_stock,
                                            'bg-red-100 text-red-800' => !$product->in_stock,
                                        ])>
                                            {{ $product->in_stock ? 'In Stock' : 'Out of Stock' }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="py-5">
                                <p class="text-sm text-gray-500 text-center">No products found</p>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add any JavaScript for interactivity here
</script>
@endpush 