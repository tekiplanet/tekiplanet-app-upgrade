@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Edit Discount Slip
        </h2>
        <a href="{{ route('admin.discount-slips.show', $discountSlip) }}" 
           class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            Back to Details
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
        <form action="{{ route('admin.discount-slips.update', $discountSlip) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Service Name -->
                <div>
                    <label for="service_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Service Name *
                    </label>
                    <input type="text" 
                           id="service_name" 
                           name="service_name" 
                           value="{{ old('service_name', $discountSlip->service_name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                           required>
                    @error('service_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Discount Percentage -->
                <div>
                    <label for="discount_percent" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Discount Percentage *
                    </label>
                    <input type="number" 
                           id="discount_percent" 
                           name="discount_percent" 
                           value="{{ old('discount_percent', $discountSlip->discount_percent) }}"
                           min="0" 
                           max="100" 
                           step="0.01"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                           required>
                    @error('discount_percent')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Expiration Date -->
                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Expiration Date *
                    </label>
                    <input type="datetime-local" 
                           id="expires_at" 
                           name="expires_at" 
                           value="{{ old('expires_at', $discountSlip->expires_at->format('Y-m-d\TH:i')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                           required>
                    @error('expires_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Used -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="is_used" 
                               value="1"
                               {{ old('is_used', $discountSlip->is_used) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Mark as Used</span>
                    </label>
                    @error('is_used')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Terms and Conditions -->
            <div class="mt-6">
                <label for="terms_conditions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Terms & Conditions
                </label>
                <textarea id="terms_conditions" 
                          name="terms_conditions" 
                          rows="6"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('terms_conditions', $discountSlip->terms_conditions) }}</textarea>
                @error('terms_conditions')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Read-only Information -->
            <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-200">Read-only Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Discount Code:</span>
                        <div class="font-mono text-sm bg-gray-100 px-2 py-1 rounded mt-1">{{ $discountSlip->discount_code }}</div>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">User:</span>
                        <div class="mt-1">{{ $discountSlip->user->name ?? 'N/A' }} ({{ $discountSlip->user->email ?? 'N/A' }})</div>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Created:</span>
                        <div class="mt-1">{{ $discountSlip->created_at->format('M j, Y g:i A') }}</div>
                    </div>
                    @if($discountSlip->is_used)
                    <div>
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Used At:</span>
                        <div class="mt-1">{{ $discountSlip->used_at->format('M j, Y g:i A') }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex gap-3">
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Update Discount Slip
                </button>
                <a href="{{ route('admin.discount-slips.show', $discountSlip) }}" 
                   class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
