@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200 mb-4">
        Professionals
    </h2>

    <!-- Search/Filter Section -->
    <div class="mb-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
        <form action="{{ route('admin.professionals.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                       placeholder="Search professionals...">
            </div>
            <div class="w-full md:w-48">
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
            <div class="w-full md:w-48">
                <select name="status" 
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Search
            </button>
        </form>
    </div>

    <!-- Professionals List -->
    <div class="grid gap-6">
        @forelse($professionals as $professional)
            <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex items-center gap-4">
                        @if($professional->avatar)
                            <img src="{{ Storage::url($professional->avatar) }}" 
                                 alt="Professional Avatar" 
                                 class="h-12 w-12 rounded-full object-cover">
                        @else
                            <div class="h-12 w-12 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <span class="text-lg font-semibold text-gray-600 dark:text-gray-300">
                                    {{ strtoupper(substr($professional->user->first_name, 0, 1)) }}
                                </span>
                            </div>
                        @endif
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                                {{ $professional->user->first_name }} {{ $professional->user->last_name }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $professional->title }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $professional->user->email }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="px-3 py-1 text-sm rounded-full {{ $professional->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($professional->status) }}
                        </span>
                        <a href="{{ route('admin.professionals.show', $professional) }}" 
                           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            View
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6 text-center">
                <p class="text-gray-600 dark:text-gray-400">No professionals found</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $professionals->links() }}
    </div>
</div>
@endsection 