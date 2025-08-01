@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <!-- Header with back button -->
    <div class="flex flex-col gap-4 mb-6">
        <!-- Title Section -->
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.courses.exams.index', $course) }}" 
               class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                {{ $exam->title }}
            </h2>
        </div>

        <!-- Actions Section -->
        <div class="flex flex-col sm:flex-row gap-3">
            <!-- View Participants Button -->
            <a href="{{ route('admin.courses.exams.participants.index', [$course, $exam]) }}" 
               class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-center">
                View Participants
            </a>

            <!-- Status Form -->
            <form id="statusForm" class="flex flex-1 sm:flex-none gap-2">
                <select id="examStatus" 
                        class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                        {{ $exam->status === 'completed' ? 'disabled' : '' }}>
                    <option value="upcoming" {{ $exam->status === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="ongoing" {{ $exam->status === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="completed" {{ $exam->status === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
                <button type="submit" 
                        id="updateStatusBtn"
                        class="flex-shrink-0 px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2"
                        {{ $exam->status === 'completed' ? 'disabled' : '' }}>
                    <svg id="loadingSpinner" class="animate-spin h-4 w-4 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="updateStatusBtnText">Update Status</span>
                </button>
            </form>

            <!-- Status Badge -->
            <div class="flex justify-center sm:justify-start">
                <span class="px-3 py-1 text-sm rounded-full {{ 
                    $exam->status === 'upcoming' ? 'bg-yellow-100 text-yellow-800' : 
                    ($exam->status === 'ongoing' ? 'bg-green-100 text-green-800' : 
                    ($exam->status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')) 
                }}">
                    {{ ucfirst($exam->status) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Exam Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">Exam Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Date</p>
                    <p class="font-medium">{{ $exam->date->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Duration</p>
                    <p class="font-medium">{{ $exam->duration }} ({{ $exam->duration_minutes }} minutes)</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Type</p>
                    <span class="px-2 py-1 text-xs rounded-full inline-block mt-1
                        {{ $exam->type === 'multiple_choice' ? 'bg-blue-100 text-blue-800' : 
                           ($exam->type === 'true_false' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800') }}">
                        {{ str_replace('_', ' ', ucfirst($exam->type)) }}
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Difficulty</p>
                    <p class="font-medium capitalize">{{ $exam->difficulty }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Questions</p>
                    <p class="font-medium">{{ $exam->total_questions }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pass Percentage</p>
                    <p class="font-medium">{{ $exam->pass_percentage }}%</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Description</p>
                    <p class="font-medium">{{ $exam->description ?: 'No description provided' }}</p>
                </div>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">Exam Statistics</h3>
            <div class="space-y-4">
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Participants</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $userExams->total() }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Passed</p>
                        <p class="text-2xl font-bold text-green-600">
                            {{ $totalPassed }}
                        </p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Failed</p>
                        <p class="text-2xl font-bold text-red-600">
                            {{ $totalFailed }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Section -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Total Participants -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Participants</h3>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $userExams->total() }}</p>
        </div>

        <!-- Passed -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Passed</h3>
            <p class="text-2xl font-bold text-green-600">{{ $totalPassed }}</p>
            <p class="text-sm text-gray-500">
                {{ $totalAttempted > 0 ? round(($totalPassed / $totalAttempted) * 100) : 0 }}% of attempts
            </p>
        </div>

        <!-- Failed -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Failed</h3>
            <p class="text-2xl font-bold text-red-600">{{ $totalFailed }}</p>
            <p class="text-sm text-gray-500">
                {{ $totalAttempted > 0 ? round(($totalFailed / $totalAttempted) * 100) : 0 }}% of attempts
            </p>
        </div>

        <!-- Pass Rate -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Pass Rate</h3>
            <p class="text-2xl font-bold text-blue-600">{{ $passRate }}%</p>
            <p class="text-sm text-gray-500">{{ $totalAttempted }} total attempts</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
const statusForm = document.getElementById('statusForm');
const updateStatusBtn = document.getElementById('updateStatusBtn');
const loadingSpinner = document.getElementById('loadingSpinner');
const updateStatusBtnText = document.getElementById('updateStatusBtnText');
const statusLabel = document.querySelector('span.rounded-full');

function getStatusClasses(status) {
    switch(status) {
        case 'upcoming':
            return 'bg-yellow-100 text-yellow-800';
        case 'ongoing':
            return 'bg-green-100 text-green-800';
        case 'completed':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-red-100 text-red-800';
    }
}

statusForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (updateStatusBtn.disabled) return;
    
    const newStatus = document.getElementById('examStatus').value;
    
    updateStatusBtn.disabled = true;
    loadingSpinner.classList.remove('hidden');
    updateStatusBtnText.textContent = 'Updating...';
    
    fetch(`/admin/courses/{{ $course->id }}/exams/{{ $exam->id }}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            status: newStatus
        })
    })
    .then(async response => {
        if (!response.ok) {
            const text = await response.text();
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
            statusLabel.className = `px-3 py-1 text-sm rounded-full ${getStatusClasses(newStatus)}`;
            statusLabel.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            
            if (newStatus === 'completed') {
                document.getElementById('examStatus').disabled = true;
                updateStatusBtn.disabled = true;
            }
            
            showNotification('', 'Exam status updated successfully', 'success');
            
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            throw new Error(data.message || 'Failed to update exam status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('', error.message || 'An error occurred', 'error');
    })
    .finally(() => {
        if (newStatus !== 'completed') {
            updateStatusBtn.disabled = false;
        }
        loadingSpinner.classList.add('hidden');
        updateStatusBtnText.textContent = 'Update Status';
    });
});
</script>
@endpush
@endsection 