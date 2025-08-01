@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Hustles
        </h2>
        <a href="{{ route('admin.hustles.create') }}" 
           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Create Hustle
        </a>
    </div>

    <!-- Filters -->
    <div class="mb-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
        <form action="{{ route('admin.hustles.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Search hustles..."
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">

            <select name="status" 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                <option value="">All Status</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>

            <select name="category" 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Filter
            </button>
        </form>
    </div>

    <!-- Hustles List -->
    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap">
                <thead>
                    <tr class="text-left bg-gray-50 dark:bg-gray-700">
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Title
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Category
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Budget
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Applications
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($hustles as $hustle)
                        <tr>
                            <td class="px-6 py-4">
                                {{ $hustle->title }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $hustle->category->name }}
                            </td>
                            <td class="px-6 py-4">
                                ₦{{ number_format($hustle->budget, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $hustle->status === 'open' ? 'bg-green-100 text-green-800' : 
                                       ($hustle->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                       ($hustle->status === 'completed' ? 'bg-gray-100 text-gray-800' : 
                                        'bg-red-100 text-red-800')) }}">
                                    {{ ucfirst($hustle->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.hustles.applications.index', $hustle) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    {{ $hustle->applications_count ?? $hustle->applications->count() }} Applications
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.hustles.show', $hustle) }}" 
                                   class="text-blue-600 hover:text-blue-900">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No hustles found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t">
            {{ $hustles->links() }}
        </div>
    </div>
</div>
@endsection 