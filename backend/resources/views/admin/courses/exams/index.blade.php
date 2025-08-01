@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Course Exams - {{ $course->title }}
        </h2>
        <a href="{{ route('admin.courses.exams.create', $course) }}" 
           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Create Exam
        </a>
    </div>

    <!-- Search/Filter Section -->
    <div class="mb-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
        <form action="{{ route('admin.courses.exams.index', $course) }}" method="GET" class="flex flex-col md:flex-row gap-4">
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

    <!-- Exams List -->
    <div class="w-full rounded-lg shadow-md">
        <!-- Desktop Table (hidden on mobile) -->
        <div class="hidden md:block w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase bg-gray-50 border-b">
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
                            <td class="px-4 py-3">{{ $exam->title }}</td>
                            <td class="px-4 py-3">{{ $exam->date->format('M d, Y') }}</td>
                            <td class="px-4 py-3">{{ $exam->duration }}</td>
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
                                    <a href="{{ route('admin.courses.exams.show', [$course, $exam]) }}" 
                                       class="text-blue-600 hover:text-blue-900">View</a>
                                    <button onclick="initializeExam({{ $exam }})" 
                                            class="text-yellow-600 hover:text-yellow-900">Edit</button>
                                    <form action="{{ route('admin.courses.exams.destroy', [$course, $exam]) }}" 
                                          method="POST" 
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('Are you sure you want to delete this exam?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-3 text-center text-gray-500">
                                No exams found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards (hidden on desktop) -->
        <div class="md:hidden">
            @forelse($exams as $exam)
                <div class="bg-white p-4 border-b border-gray-200 space-y-3">
                    <div class="flex justify-between items-start">
                        <h3 class="font-semibold text-gray-900">{{ $exam->title }}</h3>
                        <span class="px-2 py-1 text-xs rounded-full 
                            {{ $exam->status === 'upcoming' ? 'bg-yellow-100 text-yellow-800' : 
                               ($exam->status === 'ongoing' ? 'bg-green-100 text-green-800' : 
                               ($exam->status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')) }}">
                            {{ ucfirst($exam->status) }}
                        </span>
                    </div>

                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex justify-between">
                            <span>Date:</span>
                            <span>{{ $exam->date->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Duration:</span>
                            <span>{{ $exam->duration }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Type:</span>
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $exam->type === 'multiple_choice' ? 'bg-blue-100 text-blue-800' : 
                                   ($exam->type === 'true_false' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800') }}">
                                {{ str_replace('_', ' ', ucfirst($exam->type)) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                        <a href="{{ route('admin.courses.exams.show', [$course, $exam]) }}" 
                           class="px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            View
                        </a>
                        <button onclick="initializeExam({{ $exam }})" 
                                class="px-3 py-1 text-sm bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                            Edit
                        </button>
                        <form action="{{ route('admin.courses.exams.destroy', [$course, $exam]) }}" 
                              method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="px-3 py-1 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700"
                                    onclick="return confirm('Are you sure you want to delete this exam?')">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-gray-500">
                    No exams found
                </div>
            @endforelse
        </div>

        <div class="px-4 py-3 border-t">
            {{ $exams->links() }}
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800">
                <form id="editForm" class="p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Edit Exam</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                            <input type="text" 
                                   id="examTitle"
                                   name="title"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                            <input type="date" 
                                   id="examDate"
                                   name="date"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration</label>
                                <input type="text" 
                                       id="examDuration"
                                       name="duration"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Minutes</label>
                                <input type="number" 
                                       id="examDurationMinutes"
                                       name="duration_minutes"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                            <select id="examType"
                                    name="type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="true_false">True/False</option>
                                <option value="mixed">Mixed</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Difficulty</label>
                            <select id="examDifficulty"
                                    name="difficulty"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Questions</label>
                                <input type="number" 
                                       id="examTotalQuestions"
                                       name="total_questions"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pass Percentage</label>
                                <input type="number" 
                                       id="examPassPercentage"
                                       name="pass_percentage"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea id="examDescription"
                                      name="description"
                                      rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="examIsMandatory"
                                   name="is_mandatory"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Mandatory Exam</label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button"
                                onclick="closeEditModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                id="submitButton"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 flex items-center gap-2">
                            <svg id="loadingSpinner" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="submitButtonText">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentExamId = null;
const editModal = document.getElementById('editModal');
const editForm = document.getElementById('editForm');
const submitButton = document.getElementById('submitButton');
const loadingSpinner = document.getElementById('loadingSpinner');
const submitButtonText = document.getElementById('submitButtonText');

function initializeExam(exam) {
    // console.log('Exam data received:', exam);
    
    if (!exam || !exam.id) {
        showNotification('', 'Invalid exam data received', 'error');
        return;
    }

    currentExamId = exam.id;
    // console.log('Setting examId to:', currentExamId);
    
    // Set form values
    editForm.elements.title.value = exam.title;
    editForm.elements.description.value = exam.description || '';
    editForm.elements.date.value = new Date(exam.date).toISOString().split('T')[0];
    editForm.elements.duration.value = exam.duration;
    editForm.elements.duration_minutes.value = exam.duration_minutes;
    editForm.elements.type.value = exam.type;
    editForm.elements.difficulty.value = exam.difficulty;
    editForm.elements.total_questions.value = exam.total_questions;
    editForm.elements.pass_percentage.value = exam.pass_percentage;
    editForm.elements.is_mandatory.checked = exam.is_mandatory;
    
    // Show modal
    editModal.classList.remove('hidden');
}

function closeEditModal() {
    editModal.classList.add('hidden');
}

editForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (submitButton.disabled) return;
    
    if (!currentExamId) {
        showNotification('', 'Invalid exam ID', 'error');
        return;
    }
    
    // Set loading state
    submitButton.disabled = true;
    loadingSpinner.classList.remove('hidden');
    submitButtonText.textContent = 'Saving...';
    
    // Collect form data
    const formData = {
        title: editForm.elements.title.value,
        description: editForm.elements.description.value,
        date: editForm.elements.date.value,
        duration: editForm.elements.duration.value,
        duration_minutes: editForm.elements.duration_minutes.value,
        type: editForm.elements.type.value,
        difficulty: editForm.elements.difficulty.value,
        total_questions: editForm.elements.total_questions.value,
        pass_percentage: editForm.elements.pass_percentage.value,
        is_mandatory: editForm.elements.is_mandatory.checked
    };
    
    // console.log('Submitting data:', formData);
    
    fetch(`/admin/courses/{{ $course->id }}/exams/${currentExamId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-HTTP-Method-Override': 'PUT'
        },
        body: JSON.stringify(formData)
    })
    .then(async response => {
        if (!response.ok) {
            const text = await response.text();
            // console.log('Error response:', text);
            try {
                const json = JSON.parse(text);
                throw new Error(json.message || `HTTP error! status: ${response.status}`);
            } catch (e) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('', 'Exam updated successfully', 'success');
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to update exam');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('', error.message || 'An error occurred', 'error');
    })
    .finally(() => {
        // Reset loading state
        submitButton.disabled = false;
        loadingSpinner.classList.add('hidden');
        submitButtonText.textContent = 'Save Changes';
        closeEditModal();
    });
});
</script>
@endpush
@endsection 