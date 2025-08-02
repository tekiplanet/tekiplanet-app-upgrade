@extends('admin.layouts.app')

@section('title', 'Edit Currency')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Currency</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Update currency settings and exchange rate.
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('admin.currencies.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to Currencies
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <form method="POST" action="{{ route('admin.currencies.update', $currency) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Currency Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $currency->name) }}" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                               placeholder="e.g., Nigerian Naira">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Currency Code -->
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency Code</label>
                        <input type="text" name="code" id="code" value="{{ old('code', $currency->code) }}" required maxlength="3"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm uppercase"
                               placeholder="e.g., NGN">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Currency Symbol -->
                    <div>
                        <label for="symbol" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency Symbol</label>
                        <input type="text" name="symbol" id="symbol" value="{{ old('symbol', $currency->symbol) }}" required maxlength="10"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                               placeholder="e.g., ₦">
                        @error('symbol')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Exchange Rate -->
                    <div>
                        <label for="rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Exchange Rate</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="number" name="rate" id="rate" value="{{ old('rate', $currency->rate) }}" required step="0.000001" min="0.000001"
                                   class="block w-full pr-12 border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                   placeholder="1.000000">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">to base</span>
                            </div>
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rate relative to the base currency (1.0 = equal to base)</p>
                        @error('rate')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Decimal Places -->
                    <div>
                        <label for="decimal_places" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Decimal Places</label>
                        <select name="decimal_places" id="decimal_places" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                            <option value="0" {{ old('decimal_places', $currency->decimal_places) == '0' ? 'selected' : '' }}>0 (e.g., 1000)</option>
                            <option value="2" {{ old('decimal_places', $currency->decimal_places) == '2' ? 'selected' : '' }}>2 (e.g., 1000.00)</option>
                            <option value="3" {{ old('decimal_places', $currency->decimal_places) == '3' ? 'selected' : '' }}>3 (e.g., 1000.000)</option>
                            <option value="4" {{ old('decimal_places', $currency->decimal_places) == '4' ? 'selected' : '' }}>4 (e.g., 1000.0000)</option>
                        </select>
                        @error('decimal_places')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Is Base Currency -->
                    <div class="sm:col-span-2">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_base" id="is_base" value="1" {{ old('is_base', $currency->is_base) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_base" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Set as Base Currency
                            </label>
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            This currency will be used as the reference for all exchange rates. Only one currency can be the base currency.
                        </p>
                        @error('is_base')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Is Active -->
                    <div class="sm:col-span-2">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $currency->is_active) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Active
                            </label>
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Active currencies are available for use throughout the system.
                        </p>
                        @error('is_active')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 text-right sm:px-6 rounded-b-lg">
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.currencies.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Update Currency
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Currency Info -->
    <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-gray-800 dark:text-gray-200">Currency Information</h3>
                <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    <p><strong>Created:</strong> {{ $currency->created_at->format('M d, Y H:i') }}</p>
                    <p><strong>Last Updated:</strong> {{ $currency->updated_at->format('M d, Y H:i') }}</p>
                    @if($currency->is_base)
                        <p class="text-yellow-600 dark:text-yellow-400"><strong>Note:</strong> This is currently the base currency</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-uppercase currency code
    document.getElementById('code').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Auto-format exchange rate
    document.getElementById('rate').addEventListener('input', function() {
        let value = parseFloat(this.value);
        if (!isNaN(value) && value > 0) {
            this.value = value.toFixed(6);
        }
    });
</script>
@endpush
@endsection 