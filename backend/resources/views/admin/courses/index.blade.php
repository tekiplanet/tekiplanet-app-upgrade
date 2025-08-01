@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Courses
        </h2>
        <a href="{{ route('admin.courses.create') }}" 
           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Create Course
        </a>
    </div>

    <!-- Search/Filter Section -->
    <div class="mb-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
        <form action="{{ route('admin.courses.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                       placeholder="Search courses...">
            </div>
            <div class="w-full md:w-48">
                <select name="category" 
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @foreach($category->children as $child)
                            <option value="{{ $child->id }}" {{ request('category') == $child->id ? 'selected' : '' }}>
                                &nbsp;&nbsp;- {{ $child->name }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-48">
                <select name="status" 
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Search
            </button>
        </form>
    </div>

    <!-- Courses Grid -->
    <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-3">
        @forelse($courses as $course)
            <div class="bg-white rounded-lg shadow-md dark:bg-gray-800">
                <!-- Course Image -->
                <img class="object-cover w-full h-48 rounded-t-lg" 
                     src="{{ $course->image_url ?? asset('images/course-placeholder.jpg') }}" 
                     alt="{{ $course->title }}">
                
                <div class="p-4">
                    <!-- Title and Status -->
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                            {{ $course->title }}
                        </h3>
                        <span class="px-2 py-1 text-xs rounded-full {{ 
                            $course->status === 'published' ? 'bg-green-100 text-green-800' : 
                            ($course->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') 
                        }}">
                            {{ ucfirst($course->status) }}
                        </span>
                    </div>

                    <!-- Category and Level -->
                    <div class="flex gap-2 mb-2">
                        @if(is_object($course->category))
                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                {{ $course->category->name }}
                            </span>
                            @if($course->category->parent)
                                <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded-full">
                                    {{ $course->category->parent->name }}
                                </span>
                            @endif
                        @else
                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                {{ ucfirst($course->category) }}
                            </span>
                        @endif
                        <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded-full">
                            {{ ucfirst($course->level) }}
                        </span>
                    </div>

                    <!-- Course Stats -->
                    <div class="grid grid-cols-3 gap-2 mb-4 text-sm text-gray-600 dark:text-gray-400">
                        <div>
                            <i class="fas fa-users"></i>
                            {{ $course->total_students ?? 0 }} Students
                        </div>
                        <div>
                            <i class="fas fa-clock"></i>
                            {{ $course->duration_hours ?? 0 }}h
                        </div>
                        <div>
                            <i class="fas fa-star text-yellow-400"></i>
                            {{ number_format($course->rating ?? 0, 1) }} ({{ $course->total_reviews ?? 0 }})
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="mb-4">
                        <span class="text-2xl font-bold text-gray-700 dark:text-gray-200">
                            ₦{{ number_format($course->price ?? 0, 2) }}
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.courses.show', $course) }}" 
                           class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-lg shadow-md dark:bg-gray-800 p-6 text-center">
                <p class="text-gray-600 dark:text-gray-400">No courses found</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $courses->links() }}
    </div>
</div>
@endsection 