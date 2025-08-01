@extends('admin.layouts.app')

@section('content')
<div x-data="{ loading: false }" class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Create Exam - {{ $course->title }}
        </h2>
        <a href="{{ url()->previous() }}" 
           class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            Back
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <form action="{{ route('admin.courses.exams.store', $course) }}" 
              method="POST" 
              class="space-y-6"
              @submit="loading = true">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Exam Title
                    </label>
                    <input type="text" 
                           name="title" 
                           id="title"
                           value="{{ old('title') }}"
                           required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    @error('title')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Exam Date
                    </label>
                    <input type="date" 
                           name="date" 
                           id="date"
                           value="{{ old('date') }}"
                           required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    @error('date')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Duration -->
                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Duration
                    </label>
                    <input type="text" 
                           name="duration" 
                           id="duration"
                           value="{{ old('duration') }}"
                           placeholder="e.g., 2 hours"
                           required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    @error('duration')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Duration in Minutes -->
                <div>
                    <label for="duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Duration (minutes)
                    </label>
                    <input type="number" 
                           name="duration_minutes" 
                           id="duration_minutes"
                           value="{{ old('duration_minutes') }}"
                           required
                           min="1"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    @error('duration_minutes')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Exam Type
                    </label>
                    <select name="type" 
                            id="type"
                            required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        <option value="">Select Type</option>
                        <option value="multiple_choice" {{ old('type') === 'multiple_choice' ? 'selected' : '' }}>
                            Multiple Choice
                        </option>
                        <option value="true_false" {{ old('type') === 'true_false' ? 'selected' : '' }}>
                            True/False
                        </option>
                        <option value="mixed" {{ old('type') === 'mixed' ? 'selected' : '' }}>
                            Mixed
                        </option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Difficulty -->
                <div>
                    <label for="difficulty" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Difficulty Level
                    </label>
                    <select name="difficulty" 
                            id="difficulty"
                            required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        <option value="">Select Difficulty</option>
                        <option value="beginner" {{ old('difficulty') === 'beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="intermediate" {{ old('difficulty') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="advanced" {{ old('difficulty') === 'advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                    @error('difficulty')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Total Questions -->
                <div>
                    <label for="total_questions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Total Questions
                    </label>
                    <input type="number" 
                           name="total_questions" 
                           id="total_questions"
                           value="{{ old('total_questions') }}"
                           required
                           min="1"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    @error('total_questions')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pass Percentage -->
                <div>
                    <label for="pass_percentage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Pass Percentage
                    </label>
                    <input type="number" 
                           name="pass_percentage" 
                           id="pass_percentage"
                           value="{{ old('pass_percentage', 60) }}"
                           required
                           min="1"
                           max="100"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    @error('pass_percentage')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description
                </label>
                <textarea name="description" 
                          id="description"
                          rows="4"
                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Is Mandatory -->
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" 
                           name="is_mandatory" 
                           value="1"
                           {{ old('is_mandatory') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">This exam is mandatory</span>
                </label>
                @error('is_mandatory')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        :disabled="loading">
                    <span x-show="loading" class="inline-block animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                    <span x-text="loading ? 'Creating...' : 'Create Exam'"></span>
                </button>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 4000)"
             class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 4000)"
             class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            {{ session('error') }}
        </div>
    @endif
</div>
@endsection 