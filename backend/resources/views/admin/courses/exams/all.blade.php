@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            All Course Exams
        </h2>
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Exam
            </button>

            <!-- Course Selection Dropdown -->
            <div x-show="open" 
                 @click.away="open = false"
                 class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-lg shadow-xl z-50 max-h-96 overflow-y-auto">
                <div class="p-4">
                    <input type="text" 
                           x-ref="searchInput"
                           placeholder="Search courses..."
                           class="w-full px-3 py-2 border rounded-lg mb-2 focus:outline-none focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600"
                           @input="searchCourses($event.target.value)">
                    
                    <div id="coursesList" class="space-y-2">
                        @foreach($courses as $course)
                            <a href="{{ route('admin.courses.exams.create', $course) }}" 
                               class="block p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-gray-700 dark:text-gray-200">
                                {{ $course->title }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search/Filter Section -->
    <div class="mb-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
        <form action="{{ route('admin.courses.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <input type="hidden" name="view" value="exams">
            
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                       placeholder="Search exams...">
            </div>

            <div class="w-full md:w-48">
                <select name="type" 
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="">All Types</option>
                    <option value="multiple_choice" {{ request('type') === 'multiple_choice' ? 'selected' : '' }}>
                        Multiple Choice
                    </option>
                    <option value="true_false" {{ request('type') === 'true_false' ? 'selected' : '' }}>
                        True/False
                    </option>
                    <option value="mixed" {{ request('type') === 'mixed' ? 'selected' : '' }}>
                        Mixed
                    </option>
                </select>
            </div>

            <div class="w-full md:w-48">
                <select name="status" 
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="missed" {{ request('status') === 'missed' ? 'selected' : '' }}>Missed</option>
                </select>
            </div>

            <div class="w-full md:w-48">
                <select name="sort_by" 
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="date" {{ request('sort_by') === 'date' ? 'selected' : '' }}>Sort by Date</option>
                    <option value="title" {{ request('sort_by') === 'title' ? 'selected' : '' }}>Sort by Title</option>
                    <option value="type" {{ request('sort_by') === 'type' ? 'selected' : '' }}>Sort by Type</option>
                    <option value="status" {{ request('sort_by') === 'status' ? 'selected' : '' }}>Sort by Status</option>
                </select>
            </div>

            <div class="w-full md:w-48">
                <select name="sort_order" 
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Ascending</option>
                    <option value="desc" {{ request('sort_order', 'desc') === 'desc' ? 'selected' : '' }}>Descending</option>
                </select>
            </div>

            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Search
            </button>
        </form>
    </div>

    <!-- Exams Table -->
    <div class="w-full overflow-hidden rounded-lg shadow-md">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase bg-gray-50 border-b">
                        <th class="px-4 py-3">Course</th>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Duration</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    @forelse($exams as $exam)
                        <tr class="text-gray-700">
                            <td class="px-4 py-3">
                                {{ $exam->course->title }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $exam->title }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $exam->date->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $exam->duration }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $exam->type === 'multiple_choice' ? 'bg-blue-100 text-blue-800' : 
                                       ($exam->type === 'true_false' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800') }}">
                                    {{ str_replace('_', ' ', ucfirst($exam->type)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $exam->status === 'upcoming' ? 'bg-yellow-100 text-yellow-800' : 
                                       ($exam->status === 'ongoing' ? 'bg-green-100 text-green-800' : 
                                       ($exam->status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')) }}">
                                    {{ ucfirst($exam->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-4">
                                    <a href="{{ route('admin.courses.exams.show', [$exam->course, $exam]) }}" 
                                       class="text-blue-600 hover:text-blue-900">
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-3 text-center text-gray-500">
                                No exams found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t">
            {{ $exams->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    function searchCourses(query) {
        const coursesList = document.getElementById('coursesList');
        const courses = coursesList.getElementsByTagName('a');
        
        query = query.toLowerCase();
        
        for (let course of courses) {
            const title = course.textContent.toLowerCase();
            course.style.display = title.includes(query) ? 'block' : 'none';
        }
    }
</script>
@endpush
@endsection 