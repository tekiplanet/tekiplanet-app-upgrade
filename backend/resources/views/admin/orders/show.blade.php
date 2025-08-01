@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.orders.index') }}" 
           class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Orders
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="col-span-2">
            <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">
                            Order #{{ $order->id }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Placed on {{ $order->created_at->format('M d, Y H:i') }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <span class="px-3 py-1 text-sm rounded-full 
                            {{ match($order->status) {
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'processing' => 'bg-blue-100 text-blue-800',
                                'shipped' => 'bg-indigo-100 text-indigo-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                'refunded' => 'bg-gray-100 text-gray-800',
                                default => 'bg-gray-100 text-gray-800'
                            } }}">
                            {{ ucfirst($order->status) }}
                        </span>
                        <span class="px-3 py-1 text-sm rounded-full 
                            {{ match($order->payment_status) {
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'paid' => 'bg-green-100 text-green-800',
                                'failed' => 'bg-red-100 text-red-800',
                                'refunded' => 'bg-gray-100 text-gray-800',
                                default => 'bg-gray-100 text-gray-800'
                            } }}">
                            Payment: {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="mt-6">
                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">Order Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Product
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Price
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Quantity
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @foreach($order->items as $item)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                {{ $item->product->name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                ₦{{ number_format($item->price, 2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $item->quantity }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                ₦{{ number_format($item->total, 2) }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                <!-- Summary -->
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-500 dark:text-gray-300">
                                        Subtotal
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            ₦{{ number_format($order->subtotal, 2) }}
                                        </div>
                                    </td>
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-500 dark:text-gray-300">
                                        Shipping
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            ₦{{ number_format($order->shipping_cost, 2) }}
                                        </div>
                                    </td>
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-700 font-bold">
                                    <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-500 dark:text-gray-300">
                                        Total
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            ₦{{ number_format($order->total, 2) }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer and Shipping Info -->
        <div class="space-y-6">
            <!-- Customer Info -->
            <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">Customer Information</h3>
                <div class="space-y-2">
                    <p class="text-sm text-gray-900 dark:text-white font-medium">
                        {{ $order->user->name }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $order->user->email }}
                    </p>
                </div>
            </div>

            <!-- Shipping Info -->
            <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">Shipping Information</h3>
                <div class="space-y-2">
                    <p class="text-sm text-gray-900 dark:text-white font-medium">
                        {{ $order->shippingAddress->first_name }} {{ $order->shippingAddress->last_name }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $order->shippingAddress->address }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state->name }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $order->shippingAddress->phone }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $order->shippingAddress->email }}
                    </p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-900 dark:text-white font-medium">
                        Shipping Method
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $order->shippingMethod->name }}
                    </p>
                </div>
            </div>

            <!-- Update Status -->
            <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">Update Status</h3>
                <form id="updateStatusForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Status
                        </label>
                        <select name="status" 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="refunded" {{ $order->status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Notes
                        </label>
                        <textarea name="notes" 
                                  rows="3"
                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Add notes about this status change..."></textarea>
                    </div>
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2">
                        <span id="submitButtonText">Update Status</span>
                        <span id="loadingSpinner" class="hidden">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tracking Information -->
    <div class="mt-6">
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200">Tracking Information</h3>
                <button onclick="openTrackingModal()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    {{ $order->tracking ? 'Update Tracking' : 'Add Tracking' }}
                </button>
            </div>
            
            @if($order->tracking)
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-1 text-xs rounded-full 
                            {{ match($order->tracking->status) {
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'picked_up' => 'bg-blue-100 text-blue-800',
                                'in_transit' => 'bg-indigo-100 text-indigo-800',
                                'out_for_delivery' => 'bg-purple-100 text-purple-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'failed' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800'
                            } }}">
                            {{ \App\Models\OrderTracking::getStatuses()[$order->tracking->status] }}
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $order->tracking->updated_at->format('M d, Y H:i') }}
                        </span>
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        <p class="font-medium">Location:</p>
                        <p>{{ $order->tracking->location }}</p>
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        <p class="font-medium">Description:</p>
                        <p>{{ $order->tracking->description }}</p>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">No tracking information available</p>
            @endif
        </div>
    </div>

    <!-- Status History -->
    <div class="mt-6">
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">Status History</h3>
            <div class="space-y-4">
                @foreach($order->statusHistory as $history)
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ match($history->status) {
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'processing' => 'bg-blue-100 text-blue-800',
                                    'shipped' => 'bg-indigo-100 text-indigo-800',
                                    'delivered' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    'refunded' => 'bg-gray-100 text-gray-800',
                                    default => 'bg-gray-100 text-gray-800'
                                } }}">
                                {{ ucfirst($history->status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $history->created_at->format('M d, Y H:i') }}
                            </p>
                            @if($history->notes)
                                <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">
                                    {{ $history->notes }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Tracking Modal -->
    <div id="trackingModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        Update Tracking Information
                    </h3>
                    <form id="trackingForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Status
                            </label>
                            <select name="status" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach(\App\Models\OrderTracking::getStatuses() as $value => $label)
                                    <option value="{{ $value }}" 
                                        {{ $order->tracking && $order->tracking->status === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Location
                            </label>
                            <input type="text" 
                                   name="location"
                                   value="{{ $order->tracking?->location }}"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="Enter current location">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Description
                            </label>
                            <textarea name="description" 
                                      rows="3"
                                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="Enter tracking description">{{ $order->tracking?->description }}</textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button"
                                    onclick="closeTrackingModal()"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                                <span id="trackingButtonText">Update Tracking</span>
                                <span id="trackingLoadingSpinner" class="hidden">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const form = document.getElementById('updateStatusForm');
const submitButton = form.querySelector('button[type="submit"]');
const loadingSpinner = document.getElementById('loadingSpinner');
const submitButtonText = document.getElementById('submitButtonText');

form.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    submitButton.disabled = true;
    loadingSpinner.classList.remove('hidden');
    submitButtonText.textContent = 'Updating...';
    
    const formData = new FormData(form);
    
    try {
        const response = await fetch('{{ route("admin.orders.update-status", $order) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || `HTTP error! status: ${response.status}`);
        }

        if (data.success) {
            showNotification('Success', data.message);
            window.location.reload();
        } else {
            throw new Error(data.message || 'Something went wrong');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error', error.message, 'error');
    } finally {
        submitButton.disabled = false;
        loadingSpinner.classList.add('hidden');
        submitButtonText.textContent = 'Update Status';
    }
});

const trackingModal = document.getElementById('trackingModal');
const trackingForm = document.getElementById('trackingForm');

function openTrackingModal() {
    trackingModal.classList.remove('hidden');
}

function closeTrackingModal() {
    trackingModal.classList.add('hidden');
}

trackingForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitButton = trackingForm.querySelector('button[type="submit"]');
    const loadingSpinner = document.getElementById('trackingLoadingSpinner');
    const buttonText = document.getElementById('trackingButtonText');
    
    submitButton.disabled = true;
    loadingSpinner.classList.remove('hidden');
    buttonText.textContent = 'Updating...';
    
    const formData = new FormData(trackingForm);
    
    try {
        const response = await fetch('{{ route("admin.orders.update-tracking", $order) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || `HTTP error! status: ${response.status}`);
        }

        if (data.success) {
            showNotification('Success', data.message);
            window.location.reload();
        } else {
            throw new Error(data.message || 'Something went wrong');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error', error.message, 'error');
    } finally {
        submitButton.disabled = false;
        loadingSpinner.classList.add('hidden');
        buttonText.textContent = 'Update Tracking';
    }
});

// Close modal when clicking outside
trackingModal.addEventListener('click', function(e) {
    if (e.target === trackingModal) {
        closeTrackingModal();
    }
});
</script>
@endpush
@endsection 