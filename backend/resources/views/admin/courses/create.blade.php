@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Create New Course
        </h2>
        <a href="{{ route('admin.courses.index') }}" 
           class="px-4 py-2 text-sm bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            Back to Courses
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
        <form id="createCourseForm" action="{{ route('admin.courses.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course Title</label>
                    <input type="text" name="title" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Course Image -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course Image</label>
                    <input type="url" name="image_url" 
                           placeholder="https://example.com/course-image.jpg"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">
                        Enter the URL of the course image (recommended size: 1280x720 pixels)
                    </p>
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                    <select name="category_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @foreach($category->children as $child)
                                <option value="{{ $child->id }}">&nbsp;&nbsp;- {{ $child->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <!-- Instructor -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Instructor</label>
                    <select name="instructor_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Instructor</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}">
                                {{ $instructor->full_name }} - {{ $instructor->expertise }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Level -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Level</label>
                    <select name="level" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Level</option>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>

                <!-- Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price (₦)</label>
                    <input type="number" name="price" step="0.01" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Enrollment Fee -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Enrollment Fee (₦)</label>
                    <input type="number" name="enrollment_fee" step="0.01" required min="0"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">Set to 0 for free courses or specify a fee</p>
                </div>

                <!-- Duration -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration (months)</label>
                    <input type="number" name="duration_hours" min="1" max="24" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">Enter duration in months (1-24)</p>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea name="description" rows="4" required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>

            <!-- Prerequisites -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prerequisites</label>
                <textarea name="prerequisites" rows="3"
                        placeholder="Enter each prerequisite on a new line"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                <p class="mt-1 text-sm text-gray-500">Enter each prerequisite on a new line</p>
            </div>

            <!-- Learning Outcomes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Learning Outcomes</label>
                <textarea name="learning_outcomes" rows="3"
                        placeholder="Enter each learning outcome on a new line"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                <p class="mt-1 text-sm text-gray-500">Enter each learning outcome on a new line</p>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <select name="status" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="draft">Draft</option>
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span>Create Course</span>
                    <span class="hidden loading-spinner">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('createCourseForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitButton = this.querySelector('button[type="submit"]');
    const loadingSpinner = submitButton.querySelector('.loading-spinner');
    
    // Disable button and show spinner
    submitButton.disabled = true;
    loadingSpinner.classList.remove('hidden');
    
    try {
        const form = e.target;
        const formData = new FormData(form);
        
        // Convert newline-separated strings to arrays
        const arrayFields = ['prerequisites', 'learning_outcomes'];
        const formObject = {};
        
        // First convert FormData to a regular object
        for (const [key, value] of formData.entries()) {
            if (arrayFields.includes(key)) {
                // Convert textarea values to arrays
                formObject[key] = value
                    .split('\n')
                    .map(item => item.trim())
                    .filter(item => item.length > 0);
            } else {
                formObject[key] = value;
            }
        }

        // console.log('Sending data:', formObject); // Debug log

        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(formObject)
        });

        if (!response.ok) {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const errorData = await response.json();
                if (errorData.errors) {
                    // Format validation errors
                    const errorMessages = Object.values(errorData.errors)
                        .flat()
                        .join('\n');
                    throw new Error(errorMessages);
                } else {
                    throw new Error(errorData.message || 'Failed to create course');
                }
            } else {
                throw new Error('Server error occurred');
            }
        }

        const data = await response.json();

        if (data.success) {
            showNotification('Success', 'Course created successfully');
            setTimeout(() => {
                window.location.href = '{{ route("admin.courses.index") }}';
            }, 2000);
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error:', error); // Debug log
        showNotification('Error', error.message, 'error');
    } finally {
        // Re-enable button and hide spinner
        submitButton.disabled = false;
        loadingSpinner.classList.add('hidden');
    }
});
</script>
@endpush
@endsection 