<x-mail.layout>
    <div>
        <h2 class="text-xl font-bold mb-4">{{ $courseNotice->title }}</h2>
        
        <div class="text-gray-600 dark:text-gray-400">
            {!! $courseNotice->content !!}
        </div>

        @if($courseNotice->is_important)
            <div class="mt-4 p-4 bg-red-50 dark:bg-red-900 rounded-lg">
                <strong class="text-red-700 dark:text-red-200">Important Notice:</strong>
                <p class="mt-1 text-red-600 dark:text-red-300">
                    Please read this notice carefully as it contains important information.
                </p>
            </div>
        @endif

        <div class="mt-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Priority: <span class="font-semibold">{{ ucfirst($courseNotice->priority) }}</span>
            </p>
        </div>

        <div class="mt-6">
            <a href="{{ config('app.url') }}/dashboard/courses/{{ $courseNotice->course_id }}" 
               class="button">
                View Course
            </a>
        </div>
    </div>
</x-mail.layout> 