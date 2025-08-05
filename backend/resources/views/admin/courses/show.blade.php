@extends('admin.layouts.app')

@section('content')
<div x-data="{ open: false, courseId: null }">
    <div class="container px-6 mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <!-- Title Section -->
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.courses.index') }}" 
                   class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                    {{ $course->title }}
                </h2>
            </div>

            <!-- Action Buttons Section -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                <span class="px-3 py-1 text-sm rounded-full text-center {{ 
                    $course->status === 'active' ? 'bg-green-100 text-green-800' : 
                    ($course->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') 
                }}">
                    {{ ucfirst($course->status) }}
                </span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 w-full">
                    <a href="{{ route('admin.courses.enrollments', $course->id) }}"
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-center">
                        View Enrollments
                    </a>
                    <a href="{{ route('admin.courses.exams.index', $course) }}"
                       class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Course Exams
                    </a>
                    <button @click="open = true; courseId = '{{ $course->id }}'"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-center">
                        Edit Course
                    </button>
                </div>
            </div>
        </div>

        <!-- Course Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Course Image and Basic Info -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-md dark:bg-gray-800 overflow-hidden">
                <img src="{{ $course->image_url }}" alt="{{ $course->title }}" class="w-full h-64 object-cover">
                <div class="p-6">
                    <div class="flex flex-wrap gap-2 mb-4">
                        @if($course->category)
                            <span class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full">
                                {{ $course->category }}
                            </span>
                        @endif
                        @if($course->category_id && $course->category()->exists())
                            <span class="px-3 py-1 text-sm bg-purple-100 text-purple-800 rounded-full">
                                {{ $course->category()->first()->name }}
                            </span>
                        @endif
                        <span class="px-3 py-1 text-sm bg-purple-100 text-purple-800 rounded-full">
                            {{ ucfirst($course->level) }}
                        </span>
                        <span class="px-3 py-1 text-sm bg-gray-100 text-gray-800 rounded-full">
                            {{ $course->duration_hours }} months
                        </span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        {{ $course->description }}
                    </p>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <h4 class="font-semibold mb-2">Prerequisites:</h4>
                            <ul class="list-disc list-inside space-y-1 text-gray-600 dark:text-gray-400">
                                @php
                                    $prerequisites = is_string($course->prerequisites) 
                                        ? json_decode($course->prerequisites, true) 
                                        : $course->prerequisites;
                                @endphp
                                @forelse($prerequisites ?? [] as $prerequisite)
                                    <li>{{ $prerequisite }}</li>
                                @empty
                                    <li class="text-gray-500">No prerequisites specified</li>
                                @endforelse
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-2">Learning Outcomes:</h4>
                            <ul class="list-disc list-inside space-y-1 text-gray-600 dark:text-gray-400">
                                @php
                                    $learningOutcomes = is_string($course->learning_outcomes) 
                                        ? json_decode($course->learning_outcomes, true) 
                                        : $course->learning_outcomes;
                                @endphp
                                @forelse($learningOutcomes ?? [] as $outcome)
                                    <li>{{ $outcome }}</li>
                                @empty
                                    <li class="text-gray-500">No learning outcomes specified</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Stats -->
            <div class="space-y-6">
                <!-- Instructor Info -->
                <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
                    <h3 class="text-lg font-semibold mb-4">Instructor</h3>
                    <div class="flex items-center gap-4">
                        <img src="{{ $course->instructor->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($course->instructor->full_name) }}" 
                             alt="{{ $course->instructor->full_name }}"
                             class="w-16 h-16 rounded-full object-cover">
                        <div>
                            <h4 class="font-semibold">{{ $course->instructor->full_name }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $course->instructor->expertise }}</p>
                        </div>
                    </div>
                </div>

                <!-- Course Statistics -->
                <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
                    <h3 class="text-lg font-semibold mb-4">Course Statistics</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $course->total_students }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Students</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-600">
                                {{ number_format($course->rating, 1) }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Rating ({{ $course->total_reviews }})
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price Info -->
                <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
                    <h3 class="text-lg font-semibold mb-4">Price Information</h3>
                    <div class="text-3xl font-bold text-gray-700 dark:text-gray-200">
                        ₦{{ number_format($course->price, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div x-data="{ activeTab: 'modules' }" class="bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="flex space-x-4 px-4" aria-label="Tabs">
                    <button @click="activeTab = 'modules'"
                            :class="{ 'border-blue-500 text-blue-600': activeTab === 'modules' }"
                            class="px-3 py-2 text-sm font-medium border-b-2 border-transparent hover:border-gray-300">
                        Modules & Lessons
                    </button>
                    <button @click="activeTab = 'reviews'"
                            :class="{ 'border-blue-500 text-blue-600': activeTab === 'reviews' }"
                            class="px-3 py-2 text-sm font-medium border-b-2 border-transparent hover:border-gray-300">
                        Reviews
                    </button>
                    <button @click="activeTab = 'schedules'"
                            :class="{ 'border-blue-500 text-blue-600': activeTab === 'schedules' }"
                            class="px-3 py-2 text-sm font-medium border-b-2 border-transparent hover:border-gray-300">
                        Schedules
                    </button>
                </nav>
            </div>

            <div class="p-6">
                <!-- Modules Tab -->
                <div x-show="activeTab === 'modules'">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Course Modules</h3>
                        <button onclick="openModuleModal()" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Add Module
                        </button>
                    </div>

                    @if($course->modules->isEmpty())
                        <p class="text-gray-500 text-center py-4">No modules added yet.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($course->modules as $module)
                                <div class="border rounded-lg p-4 relative">
                                    <div class="absolute top-4 right-4 flex items-center gap-2">
                                        <button onclick="openModuleModal('{{ $module->id }}')"
                                                class="text-blue-600 hover:text-blue-800">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button onclick="deleteModule('{{ $module->id }}')"
                                                class="text-red-600 hover:text-red-800">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>

                                    <h3 class="font-semibold mb-2">{{ $module->title }}</h3>
                                    <p class="text-sm text-gray-600 mb-4">{{ $module->description }}</p>
                                    <div class="flex justify-between items-center mb-4">
                                        <span class="text-sm text-gray-500">{{ $module->duration_hours }} hours</span>
                                        <button onclick="openLessonModal('{{ $module->id }}')"
                                                class="text-sm text-blue-600 hover:text-blue-800">
                                            + Add Lesson
                                        </button>
                                    </div>

                                    @if($module->lessons->isEmpty())
                                        <p class="text-sm text-gray-500">No lessons added yet.</p>
                                    @else
                                        <ul class="space-y-2 border-t pt-4 mb-4">
                                            @foreach($module->lessons as $lesson)
                                                <li class="text-sm text-gray-600 dark:text-gray-400 flex justify-between items-center">
                                                    {{ $lesson->title }}
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs text-gray-500">{{ $lesson->duration_minutes }}min</span>
                                                        <button onclick="openLessonModal('{{ $module->id }}', '{{ $lesson->id }}')"
                                                                class="text-blue-600 hover:text-blue-800">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                        </button>
                                                        <button onclick="deleteLesson('{{ $lesson->id }}')"
                                                                class="text-red-600 hover:text-red-800">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    <!-- Topics Section -->
                                    <div class="mt-6 border-t pt-4">
                                        <div class="flex justify-between items-center mb-4">
                                            <h4 class="font-semibold">Topics</h4>
                                            <button onclick="openTopicModal('{{ $module->id }}')"
                                                    class="text-sm text-blue-600 hover:text-blue-800">
                                                + Add Topic
                                            </button>
                                        </div>

                                        @if($module->topics->isEmpty())
                                            <p class="text-sm text-gray-500">No topics added yet.</p>
                                        @else
                                            <div class="space-y-4">
                                                @foreach($module->topics as $topic)
                                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                                        <div class="flex justify-between items-start mb-2">
                                                            <h5 class="font-medium">{{ $topic->title }}</h5>
                                                            <div class="flex items-center gap-2">
                                                                <button onclick="openTopicModal('{{ $module->id }}', '{{ $topic->id }}')"
                                                                        class="text-blue-600 hover:text-blue-800">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                    </svg>
                                                                </button>
                                                                <button onclick="deleteTopic('{{ $topic->id }}')"
                                                                        class="text-red-600 hover:text-red-800">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $topic->description }}</p>
                                                        @if($topic->learning_outcomes)
                                                            <div class="space-y-1">
                                                                <h6 class="text-sm font-medium text-gray-700 dark:text-gray-300">Learning Outcomes:</h6>
                                                                <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400">
                                                                    @foreach(json_decode($topic->learning_outcomes) as $outcome)
                                                                        <li>{{ $outcome }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Reviews Tab -->
                <div x-show="activeTab === 'reviews'">
                    @if($course->reviews->isEmpty())
                        <p class="text-gray-500 text-center py-4">No reviews yet.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($course->reviews as $review)
                                <div class="border-b pb-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold">{{ $review->user->name }}</span>
                                            <span class="text-yellow-400">
                                                {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                                            </span>
                                        </div>
                                        <span class="text-sm text-gray-500">
                                            {{ $review->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="text-gray-600 dark:text-gray-400">{{ $review->comment }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Schedules Tab -->
                <div x-show="activeTab === 'schedules'">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Course Schedules</h3>
                        <button onclick="openScheduleModal()"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Add Schedule
                        </button>
                    </div>

                    @if($course->schedules->isEmpty())
                        <p class="text-gray-500 text-center py-4">No schedules added yet.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($course->schedules as $schedule)
                                <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4">
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex-1">
                                            <h4 class="font-semibold mb-2">Schedule #{{ $loop->iteration }}</h4>
                                            <div class="space-y-1 text-sm">
                                                <p class="text-gray-600 dark:text-gray-300">
                                                    <span class="font-medium">Period:</span><br>
                                                    {{ date('M d, Y', strtotime($schedule->start_date)) }} - 
                                                    {{ date('M d, Y', strtotime($schedule->end_date)) }}
                                                </p>
                                                <p class="text-gray-600 dark:text-gray-300">
                                                    <span class="font-medium">Time:</span><br>
                                                    {{ date('h:i A', strtotime($schedule->start_time)) }} - 
                                                    {{ date('h:i A', strtotime($schedule->end_time)) }}
                                                </p>
                                                <p class="text-gray-600 dark:text-gray-300">
                                                    <span class="font-medium">Days:</span><br>
                                                    {{ $schedule->days_of_week }}
                                                </p>
                                                @if($schedule->location)
                                                    <p class="text-gray-600 dark:text-gray-300">
                                                        <span class="font-medium">Location:</span><br>
                                                        {{ $schedule->location }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex gap-2">
                                            <button onclick="openScheduleModal('{{ $schedule->id }}')"
                                                    class="text-blue-600 hover:text-blue-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button onclick="deleteSchedule('{{ $schedule->id }}')"
                                                    class="text-red-600 hover:text-red-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('admin.courses.partials.edit-modal')
    @include('admin.courses.partials.module-modal')
    @include('admin.courses.partials.lesson-modal')
    @include('admin.courses.partials.topic-modal')
    @include('admin.courses.partials.schedule-modal')
</div>

@push('scripts')
<script>
let currentLessonId = null;
let isEditingLesson = false;

function openLessonModal(moduleId, lessonId = null) {
    const modal = document.getElementById('lessonModal');
    const form = document.getElementById('lessonForm');
    const modalTitle = document.getElementById('lessonModalTitle');
    const modalAction = document.getElementById('lessonModalAction');
    
    currentLessonId = lessonId;
    isEditingLesson = !!lessonId;
    
    // Set module ID
    document.getElementById('lessonModuleId').value = moduleId;
    
    // Update modal title and action button
    modalTitle.textContent = isEditingLesson ? 'Edit Lesson' : 'Add New Lesson';
    modalAction.textContent = isEditingLesson ? 'Update Lesson' : 'Create Lesson';
    
    // Reset form
    form.reset();
    
    if (lessonId) {
        loadLesson(lessonId);
    }
    
    modal.classList.remove('hidden');
}

function closeLessonModal() {
    const modal = document.getElementById('lessonModal');
    modal.classList.add('hidden');
}

function loadLesson(lessonId) {
    fetch(`/admin/courses/{{ $course->id }}/lessons/${lessonId}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const form = document.getElementById('lessonForm');
                form.title.value = data.lesson.title;
                form.description.value = data.lesson.description;
                form.content_type.value = data.lesson.content_type;
                form.duration_minutes.value = data.lesson.duration_minutes;
                form.order.value = data.lesson.order;
                form.resource_url.value = data.lesson.resource_url || '';
                form.is_preview.checked = data.lesson.is_preview;
            }
        })
        .catch(error => {
            console.error('Error loading lesson:', error);
            showNotification('Error', 'Failed to load lesson data', 'error');
        });
}

function handleLessonSubmit(event) {
    const form = event.target;
    const moduleId = document.getElementById('lessonModuleId').value;
    // console.log('Module ID:', moduleId);

    const url = isEditingLesson
        ? `/admin/courses/{{ $course->id }}/lessons/${currentLessonId}`
        : `/admin/courses/{{ $course->id }}/modules/${moduleId}/lessons`;
    // console.log('Request URL:', url);

    const method = isEditingLesson ? 'PUT' : 'POST';
    // console.log('Request Method:', method);

    const submitButton = form.querySelector('button[type="submit"]');
    const loadingSpinner = submitButton.querySelector('.loading-spinner');
    
    submitButton.disabled = true;
    loadingSpinner.classList.remove('hidden');

    const formData = new FormData(form);
    const data = {
        module_id: moduleId,
        title: formData.get('title'),
        description: formData.get('description'),
        content_type: formData.get('content_type'),
        duration_minutes: formData.get('duration_minutes'),
        order: formData.get('order'),
        resource_url: formData.get('resource_url'),
        is_preview: formData.get('is_preview') === 'on'
    };

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Response:', text);
                try {
                    const json = JSON.parse(text);
                    throw new Error(json.message || 'Network response was not ok');
                } catch (e) {
                    throw new Error(`HTTP ${response.status}: ${text || 'Network response was not ok'}`);
                }
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Success', `Lesson ${isEditingLesson ? 'updated' : 'created'} successfully`);
            closeLessonModal();
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', `Failed to ${isEditingLesson ? 'update' : 'create'} lesson`, 'error');
    })
    .finally(() => {
        submitButton.disabled = false;
        loadingSpinner.classList.add('hidden');
    });
}

function loadCourse(courseId) {
    fetch(`/admin/courses/${courseId}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate form fields
                const form = document.getElementById('editCourseForm');
                form.action = `/admin/courses/${courseId}`;

                // Set basic fields
                ['title', 'description', 'category_id', 'instructor_id', 'level', 
                 'price', 'duration_hours', 'image_url', 'status'].forEach(field => {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) input.value = data.course[field];
                });

                // Handle arrays (prerequisites and learning outcomes)
                const prerequisites = data.course.prerequisites;
                const learningOutcomes = data.course.learning_outcomes;

                form.querySelector('[name="prerequisites"]').value = 
                    Array.isArray(prerequisites) 
                        ? prerequisites.join('\n')
                        : JSON.parse(prerequisites).join('\n');

                form.querySelector('[name="learning_outcomes"]').value = 
                    Array.isArray(learningOutcomes)
                        ? learningOutcomes.join('\n')
                        : JSON.parse(learningOutcomes).join('\n');
            }
        })
        .catch(error => {
            console.error('Error loading course:', error);
            showNotification('Error', 'Failed to load course data', 'error');
        });
}

function updateCourse(e) {
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const loadingSpinner = submitButton.querySelector('.loading-spinner');
    
    // Disable button and show spinner
    submitButton.disabled = true;
    loadingSpinner.classList.remove('hidden');
    
    // Prepare form data
    const formData = new FormData(form);
    const jsonData = {};
    
    // Convert prerequisites and learning outcomes to arrays
    for (const [key, value] of formData.entries()) {
        if (key === '_method' || key === '_token') continue;
        if (key === 'prerequisites' || key === 'learning_outcomes') {
            jsonData[key] = value.split('\n')
                .map(item => item.trim())
                .filter(item => item.length > 0);
        } else {
            jsonData[key] = value;
        }
    }

    fetch(form.action, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-HTTP-Method-Override': 'PUT'
        },
        body: JSON.stringify(jsonData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Failed to update course');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Success', 'Course updated successfully');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            throw new Error(data.message || 'Failed to update course');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', error.message, 'error');
    })
    .finally(() => {
        submitButton.disabled = false;
        loadingSpinner.classList.add('hidden');
    });
}

function deleteModule(moduleId) {
    if (!confirm('Are you sure you want to delete this module? This will also delete all lessons within this module.')) {
        return;
    }

    fetch(`/admin/courses/{{ $course->id }}/modules/${moduleId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Success', 'Module deleted successfully');
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', 'Failed to delete module', 'error');
    });
}

function deleteLesson(lessonId) {
    if (!confirm('Are you sure you want to delete this lesson?')) {
        return;
    }

    fetch(`/admin/courses/{{ $course->id }}/lessons/${lessonId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Success', 'Lesson deleted successfully');
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', 'Failed to delete lesson', 'error');
    });
}

// Topic Management Variables
let currentTopicId = null;
let isEditingTopic = false;

function openTopicModal(moduleId, topicId = null) {
    const modal = document.getElementById('topicModal');
    const form = document.getElementById('topicForm');
    const modalTitle = document.getElementById('topicModalTitle');
    const modalAction = document.getElementById('topicModalAction');
    
    currentTopicId = topicId;
    isEditingTopic = !!topicId;
    
    // Set module ID
    document.getElementById('topicModuleId').value = moduleId;
    
    // Update modal title and action button
    modalTitle.textContent = isEditingTopic ? 'Edit Topic' : 'Add New Topic';
    modalAction.textContent = isEditingTopic ? 'Update Topic' : 'Create Topic';
    
    // Reset form
    form.reset();
    
    if (topicId) {
        loadTopic(topicId);
    }
    
    modal.classList.remove('hidden');
}

function closeTopicModal() {
    const modal = document.getElementById('topicModal');
    modal.classList.add('hidden');
}

function loadTopic(topicId) {
    fetch(`/admin/courses/{{ $course->id }}/topics/${topicId}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const form = document.getElementById('topicForm');
                form.title.value = data.topic.title;
                form.description.value = data.topic.description;
                form.order.value = data.topic.order;
                
                // Handle learning outcomes (convert JSON array to newline-separated string)
                const learningOutcomes = JSON.parse(data.topic.learning_outcomes);
                form.learning_outcomes.value = learningOutcomes.join('\n');
            }
        })
        .catch(error => {
            console.error('Error loading topic:', error);
            showNotification('Error', 'Failed to load topic data', 'error');
        });
}

function handleTopicSubmit(event) {
    const form = event.target;
    const moduleId = document.getElementById('topicModuleId').value;
    const submitButton = form.querySelector('button[type="submit"]');
    const loadingSpinner = submitButton.querySelector('.loading-spinner');
    
    submitButton.disabled = true;
    loadingSpinner.classList.remove('hidden');

    const url = isEditingTopic
        ? `/admin/courses/{{ $course->id }}/topics/${currentTopicId}`
        : `/admin/courses/{{ $course->id }}/modules/${moduleId}/topics`;

    const method = isEditingTopic ? 'PUT' : 'POST';

    const formData = new FormData(form);
    const data = {
        module_id: moduleId,
        title: formData.get('title'),
        description: formData.get('description'),
        order: parseInt(formData.get('order')),
        learning_outcomes: formData.get('learning_outcomes')
    };

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Response:', text);
                try {
                    const json = JSON.parse(text);
                    throw new Error(json.message || 'Network response was not ok');
                } catch (e) {
                    throw new Error(`HTTP ${response.status}: ${text || 'Network response was not ok'}`);
                }
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Success', `Topic ${isEditingTopic ? 'updated' : 'created'} successfully`);
            closeTopicModal();
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', `Failed to ${isEditingTopic ? 'update' : 'create'} topic`, 'error');
    })
    .finally(() => {
        submitButton.disabled = false;
        loadingSpinner.classList.add('hidden');
    });
}

function deleteTopic(topicId) {
    if (!confirm('Are you sure you want to delete this topic?')) {
        return;
    }

    fetch(`/admin/courses/{{ $course->id }}/topics/${topicId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Success', 'Topic deleted successfully');
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', 'Failed to delete topic', 'error');
    });
}

let currentScheduleId = null;
let isEditingSchedule = false;

function openScheduleModal(scheduleId = null) {
    const modal = document.getElementById('scheduleModal');
    const form = document.getElementById('scheduleForm');
    const modalTitle = document.getElementById('scheduleModalTitle');
    const modalAction = document.getElementById('scheduleModalAction');
    
    currentScheduleId = scheduleId;
    isEditingSchedule = !!scheduleId;
    
    modalTitle.textContent = isEditingSchedule ? 'Edit Schedule' : 'Add New Schedule';
    modalAction.textContent = isEditingSchedule ? 'Update Schedule' : 'Create Schedule';
    
    form.reset();
    
    if (scheduleId) {
        loadSchedule(scheduleId);
    }
    
    modal.classList.remove('hidden');
}

function closeScheduleModal() {
    const modal = document.getElementById('scheduleModal');
    modal.classList.add('hidden');
}

function loadSchedule(scheduleId) {
    fetch(`/admin/courses/{{ $course->id }}/schedules/${scheduleId}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const form = document.getElementById('scheduleForm');
                form.start_date.value = data.schedule.start_date;
                form.end_date.value = data.schedule.end_date;
                form.start_time.value = data.schedule.start_time;
                form.end_time.value = data.schedule.end_time;
                form.location.value = data.schedule.location || '';
                
                // Handle days of week
                const days = data.schedule.days_of_week.split(',');
                form.querySelectorAll('input[name="days[]"]').forEach(checkbox => {
                    checkbox.checked = days.includes(checkbox.value);
                });
            }
        })
        .catch(error => {
            console.error('Error loading schedule:', error);
            showNotification('Error', 'Failed to load schedule data', 'error');
        });
}

function handleScheduleSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const loadingSpinner = submitButton.querySelector('.loading-spinner');
    
    submitButton.disabled = true;
    loadingSpinner.classList.remove('hidden');

    const selectedDays = Array.from(form.querySelectorAll('input[name="days[]"]:checked'))
        .map(checkbox => checkbox.value)
        .join(',');

    const formData = {
        start_date: form.start_date.value,
        end_date: form.end_date.value,
        start_time: form.start_time.value,
        end_time: form.end_time.value,
        days_of_week: selectedDays,
        location: form.location.value
    };

    const url = isEditingSchedule
        ? `/admin/courses/{{ $course->id }}/schedules/${currentScheduleId}`
        : `/admin/courses/{{ $course->id }}/schedules`;

    fetch(url, {
        method: isEditingSchedule ? 'PUT' : 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Success', `Schedule ${isEditingSchedule ? 'updated' : 'created'} successfully`);
            closeScheduleModal();
            window.location.reload();
        } else {
            throw new Error(data.message || `Failed to ${isEditingSchedule ? 'update' : 'create'} schedule`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', error.message, 'error');
    })
    .finally(() => {
        submitButton.disabled = false;
        loadingSpinner.classList.add('hidden');
    });
}

function deleteSchedule(scheduleId) {
    if (!confirm('Are you sure you want to delete this schedule?')) {
        return;
    }

    fetch(`/admin/courses/{{ $course->id }}/schedules/${scheduleId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Success', 'Schedule deleted successfully');
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to delete schedule');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', error.message, 'error');
    });
}
</script>
@endpush
@endsection 