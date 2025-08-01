@extends('admin.layouts.app')

@section('content')
<!-- Debug session data -->
@if(session()->has('success') || session()->has('error'))
    <script>
        console.log('Session data:', @json(session()->all()));
    </script>
@endif

@include('admin.components.notification')
@include('admin.components.delete-confirmation-modal')

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('Success', '{{ session('success') }}');
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('Error', '{{ session('error') }}', 'error');
        });
    </script>
@endif

<div class="container px-6 mx-auto">
    <!-- Debug button -->
    <!-- @if(config('app.debug'))
    <button onclick="showNotification('Test notification', 'success')" 
            class="mb-4 px-4 py-2 bg-gray-200 rounded">
        Test Notification
    </button>
    @endif -->

    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Services
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                Manage your services and their details
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('admin.services.create') }}"
               class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add New Service
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow px-4 py-4 sm:px-6">
        <form action="{{ route('admin.services.index') }}" method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-center sm:space-x-4">
            <div class="flex-1">
                <label for="search" class="sr-only">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           id="search"
                           value="{{ request('search') }}"
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                           placeholder="Search services...">
                </div>
            </div>
            <div>
                <select name="category" 
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-shrink-0">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Filter
                </button>
                <a href="{{ route('admin.services.index') }}"
                   class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Service
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Category
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Starting Price
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Featured
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($services as $service)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                                    <i class="{{ $service->icon_name }} text-gray-600 dark:text-gray-300"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $service->name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ Str::limit($service->short_description, 50) }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $service->category->name }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                ₦{{ number_format($service->starting_price, 2) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button 
                                onclick="toggleFeatured('{{ $service->id }}')"
                                type="button"
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 {{ $service->is_featured ? 'bg-green-500' : 'bg-gray-200' }} dark:bg-gray-700"
                                role="switch"
                                aria-checked="{{ $service->is_featured ? 'true' : 'false' }}">
                                <span 
                                    aria-hidden="true" 
                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $service->is_featured ? 'translate-x-5' : 'translate-x-0' }}"
                                ></span>
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.services.edit', $service) }}" 
                               class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 mr-3">
                                Edit
                            </a>
                            <button 
                                onclick="deleteService('{{ $service->id }}')"
                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                Delete
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $services->withQueryString()->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
async function toggleFeatured(id) {
    try {
        const response = await fetch(`/admin/services/${id}/toggle-featured`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) throw new Error('Failed to toggle featured status');
        
        const data = await response.json();
        if (data.success) {
            window.location.reload();
        } else {
            showNotification('Error', 'Failed to toggle featured status', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error', 'Failed to toggle featured status', 'error');
    }
}

function deleteService(id) {
    showDeleteModal(function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/services/${id}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }, 'Are you sure you want to delete this service? This action cannot be undone.');
}
</script>
@endpush

@endsection 