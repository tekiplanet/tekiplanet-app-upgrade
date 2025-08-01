@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.courses.exams.show', [$course, $exam]) }}" 
               class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                    Exam Participants
                </h2>
                <p class="text-sm text-gray-500">{{ $exam->title }}</p>
            </div>
        </div>
    </div>

    <!-- Search/Filter Section -->
    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-4 mb-6">
        <form action="{{ route('admin.courses.exams.participants.index', [$course, $exam]) }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="col-span-2">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search by name or email..."
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <select name="status" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="not_started" {{ request('status') === 'not_started' ? 'selected' : '' }}>Not Started</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="missed" {{ request('status') === 'missed' ? 'selected' : '' }}>Missed</option>
                    </select>
                </div>
                <div>
                    <select name="result" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Results</option>
                        <option value="passed" {{ request('result') === 'passed' ? 'selected' : '' }}>Passed</option>
                        <option value="failed" {{ request('result') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="pending" {{ request('result') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-4 mb-6">
        <form id="bulkActionForm" class="flex flex-wrap gap-4">
            <select id="bulkAction" 
                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Select Bulk Action</option>
                <option value="status">Change Status</option>
                <option value="score">Set Score</option>
            </select>
            
            <select id="bulkStatus" 
                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 hidden">
                <option value="not_started">Not Started</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="missed">Missed</option>
            </select>
            
            <div id="bulkScoreInputs" class="flex gap-2 hidden">
                <input type="number" id="bulkScore" placeholder="Score" 
                       class="w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <span class="self-center">/</span>
                <input type="number" id="bulkTotalScore" placeholder="Total" 
                       class="w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <button type="submit" 
                    id="applyBulkAction"
                    disabled
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2">
                <svg id="bulkLoadingSpinner" class="animate-spin h-4 w-4 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span id="bulkActionBtnText">Apply to Selected</span>
            </button>
        </form>
    </div>

    <!-- Participants List -->
    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase bg-gray-50 border-b dark:border-gray-700">
                        <th class="px-4 py-3">
                            <input type="checkbox" id="selectAll" 
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </th>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Score</th>
                        <th class="px-4 py-3">Result</th>
                        <th class="px-4 py-3">Attempt Date</th>
                        <th class="px-4 py-3">Time Taken</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @forelse($participants as $participant)
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="px-4 py-3">
                                <input type="checkbox" 
                                       name="selected_users[]" 
                                       value="{{ $participant->id }}" 
                                       class="user-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <img class="h-8 w-8 rounded-full" 
                                         src="{{ $participant->user->avatar_url }}" 
                                         alt="{{ $participant->user->name }}">
                                    <div class="ml-3">
                                        <p class="font-semibold">{{ $participant->user->first_name }} {{ $participant->user->last_name }}</p>
                                        <p class="text-sm text-gray-500">{{ $participant->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ 
                                    $participant->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                    ($participant->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                    ($participant->status === 'missed' ? 'bg-red-100 text-red-800' : 
                                    'bg-yellow-100 text-yellow-800')) }}">
                                    {{ str_replace('_', ' ', ucfirst($participant->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($participant->score !== null)
                                    <span class="font-medium">
                                        {{ $participant->score }}/{{ $participant->total_score }}
                                        ({{ round(($participant->score / $participant->total_score) * 100) }}%)
                                    </span>
                                @else
                                    <span class="text-gray-500">Not attempted</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($participant->score !== null)
                                    @php
                                        $percentage = ($participant->score / $participant->total_score) * 100;
                                        $passed = $percentage >= $exam->pass_percentage;
                                    @endphp
                                    <span class="px-2 py-1 text-xs rounded-full {{ 
                                        $passed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' 
                                    }}">
                                        {{ $passed ? 'Passed' : 'Failed' }}
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($participant->started_at)
                                    {{ $participant->started_at->format('M d, Y H:i') }}
                                @else
                                    <span class="text-gray-500">Not started</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($participant->started_at && $participant->completed_at)
                                    {{ $participant->started_at->diffForHumans($participant->completed_at, true) }}
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <button onclick="openActionModal('{{ $participant->id }}')" 
                                        class="text-blue-600 hover:text-blue-900">
                                    Update
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-3 text-center text-gray-500">
                                No participants found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $participants->links() }}
        </div>
    </div>
</div>

<!-- Individual Action Modal -->
@include('admin.courses.exams.participants._action_modal')

@endsection

@push('scripts')
@include('admin.courses.exams.participants._scripts')
@endpush 