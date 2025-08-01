@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.courses.show', $course->id) }}" 
               class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                {{ $course->title }} - Enrollments
            </h2>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <!-- Search and Filters -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <form action="{{ route('admin.courses.enrollments', $course->id) }}" method="GET" 
                  class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search students..." 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Status Filter -->
                <select name="status" 
                        class="px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="dropped" {{ request('status') === 'dropped' ? 'selected' : '' }}>Dropped</option>
                </select>

                <!-- Payment Status Filter -->
                <select name="payment_status" 
                        class="px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Payment Status</option>
                    <option value="fully_paid" {{ request('payment_status') === 'fully_paid' ? 'selected' : '' }}>Fully Paid</option>
                    <option value="partially_paid" {{ request('payment_status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                </select>

                <!-- Sort -->
                <div class="flex gap-2">
                    <select name="sort_by" 
                            class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="enrolled_at" {{ request('sort_by') === 'enrolled_at' ? 'selected' : '' }}>Date</option>
                        <option value="progress" {{ request('sort_by') === 'progress' ? 'selected' : '' }}>Progress</option>
                    </select>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Bulk Actions -->
        <div class="p-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <label class="flex items-center">
                    <input type="checkbox" 
                           id="selectAll"
                           class="form-checkbox rounded border-gray-300">
                    <span class="ml-2 text-sm">Select All</span>
                </label>
                
                <div id="bulkActionsContainer" class="flex flex-wrap items-center gap-2" style="display: none;">
                    <span class="text-sm text-gray-600 dark:text-gray-300 w-full md:w-auto mb-2 md:mb-0">
                        <span id="selectedCount">0</span> selected
                    </span>
                    
                    <div class="flex flex-wrap gap-2 w-full md:w-auto">
                        <!-- Status Update -->
                        <div class="relative w-full sm:w-auto">
                            <button onclick="toggleDropdown('statusDropdown')"
                                    class="w-full sm:w-auto px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                                Status
                            </button>
                            <div id="statusDropdown" 
                                 class="hidden absolute left-0 sm:right-0 sm:left-auto mt-2 w-full sm:w-48 bg-white rounded-lg shadow-lg z-10">
                                <div class="py-1">
                                    <button onclick="updateBulkEnrollments('status', 'active', this)"
                                            class="status-btn block w-full px-4 py-2 text-sm text-left hover:bg-gray-100">
                                        <span class="inline-flex items-center">
                                            <span class="status-text">Set Active</span>
                                            <svg class="hidden loading-spinner ml-2 w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                    <button onclick="updateBulkEnrollments('status', 'pending', this)"
                                            class="status-btn block w-full px-4 py-2 text-sm text-left hover:bg-gray-100">
                                        <span class="inline-flex items-center">
                                            <span class="status-text">Set Pending</span>
                                            <svg class="hidden loading-spinner ml-2 w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                    <button onclick="updateBulkEnrollments('status', 'completed', this)"
                                            class="status-btn block w-full px-4 py-2 text-sm text-left hover:bg-gray-100">
                                        <span class="inline-flex items-center">
                                            <span class="status-text">Set Completed</span>
                                            <svg class="hidden loading-spinner ml-2 w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                    <button onclick="updateBulkEnrollments('status', 'dropped', this)"
                                            class="status-btn block w-full px-4 py-2 text-sm text-left hover:bg-gray-100">
                                        <span class="inline-flex items-center">
                                            <span class="status-text">Set Dropped</span>
                                            <svg class="hidden loading-spinner ml-2 w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Status Update -->
                        <div class="relative w-full sm:w-auto">
                            <button onclick="toggleDropdown('paymentStatusDropdown')"
                                    class="w-full sm:w-auto px-3 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">
                                Payment
                            </button>
                            <div id="paymentStatusDropdown" 
                                 class="hidden absolute left-0 sm:right-0 sm:left-auto mt-2 w-full sm:w-48 bg-white rounded-lg shadow-lg z-10">
                                <div class="py-1">
                                    <button onclick="updateBulkEnrollments('payment_status', 'fully_paid', this)"
                                            class="payment-btn block w-full px-4 py-2 text-sm text-left hover:bg-gray-100">
                                        <span class="inline-flex items-center">
                                            <span class="payment-text">Fully Paid</span>
                                            <svg class="hidden loading-spinner ml-2 w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                    <button onclick="updateBulkEnrollments('payment_status', 'partially_paid', this)"
                                            class="payment-btn block w-full px-4 py-2 text-sm text-left hover:bg-gray-100">
                                        <span class="inline-flex items-center">
                                            <span class="payment-text">Partially Paid</span>
                                            <svg class="hidden loading-spinner ml-2 w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                    <button onclick="updateBulkEnrollments('payment_status', 'unpaid', this)"
                                            class="payment-btn block w-full px-4 py-2 text-sm text-left hover:bg-gray-100">
                                        <span class="inline-flex items-center">
                                            <span class="payment-text">Unpaid</span>
                                            <svg class="hidden loading-spinner ml-2 w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Update -->
                        <div class="relative w-full sm:w-auto">
                            <button onclick="toggleDropdown('progressDropdown')"
                                    class="w-full sm:w-auto px-3 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">
                                Progress
                            </button>
                            <div id="progressDropdown" 
                                 class="hidden absolute left-0 sm:right-0 sm:left-auto mt-2 w-full sm:w-48 bg-white rounded-lg shadow-lg z-10">
                                <div class="p-3">
                                    <input type="number" 
                                           min="0" 
                                           max="100" 
                                           class="w-full px-3 py-2 text-sm border rounded-lg"
                                           placeholder="Enter progress %"
                                           id="progressInput">
                                    <button id="progressUpdateBtn"
                                            onclick="updateBulkEnrollments('progress', document.getElementById('progressInput').value)"
                                            class="w-full mt-2 px-3 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">
                                        <span class="inline-flex items-center">
                                            <span class="update-text">Update</span>
                                            <svg class="hidden loading-spinner ml-2 w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Send Notice -->
                        <div class="relative w-full sm:w-auto">
                            <button onclick="toggleDropdown('noticeDropdown')"
                                    class="w-full sm:w-auto px-3 py-2 bg-orange-600 text-white text-sm rounded-lg hover:bg-orange-700">
                                Send Notice
                            </button>
                            <div id="noticeDropdown" 
                                 class="hidden absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg z-50" 
                                 style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                               <div class="p-3">
                                   <div class="mb-3">
                                       <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                       <input type="text" 
                                              id="noticeTitle"
                                              class="w-full px-3 py-2 text-sm border rounded-lg"
                                              placeholder="Enter notice title">
                                   </div>
                                   <div class="mb-3">
                                       <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                                       <textarea id="noticeContent"
                                                class="w-full px-3 py-2 text-sm border rounded-lg"
                                                rows="4"
                                                placeholder="Enter notice content"></textarea>
                                   </div>
                                   <div class="mb-3">
                                       <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                       <select id="noticePriority"
                                               class="w-full px-3 py-2 text-sm border rounded-lg">
                                           <option value="low">Low</option>
                                           <option value="medium">Medium</option>
                                           <option value="high">High</option>
                                       </select>
                                   </div>
                                   <div class="mb-3">
                                       <label class="flex items-center">
                                           <input type="checkbox" 
                                                  id="noticeImportant"
                                                  class="form-checkbox rounded border-gray-300">
                                           <span class="ml-2 text-sm text-gray-700">Mark as Important</span>
                                       </label>
                                   </div>
                                   <button onclick="sendBulkNotices(this)"
                                           class="w-full px-3 py-2 bg-orange-600 text-white text-sm rounded-lg hover:bg-orange-700">
                                       <span class="inline-flex items-center">
                                           <span class="notice-text">Send Notice</span>
                                           <svg class="hidden loading-spinner ml-2 w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                               <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                               <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                           </svg>
                                       </span>
                                   </button>
                               </div>
                           </div>
                       </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Enrollments List -->
        @if($enrollments->isEmpty())
            <div class="p-4 text-center">
                <p class="text-gray-500 dark:text-gray-400">No students enrolled yet.</p>
            </div>
        @else
            <!-- Mobile View (Card Layout) -->
            <div class="block md:hidden">
                @foreach($enrollments as $enrollment)
                    <div class="p-4 border-b border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-3 mb-3">
                            <!-- Checkbox without propagation -->
                            <div onclick="event.stopPropagation()">
                                <input type="checkbox" 
                                       class="enrollment-checkbox form-checkbox rounded border-gray-300"
                                       value="{{ $enrollment->id }}">
                            </div>
                            
                            <!-- Clickable content -->
                            <div class="flex-1 cursor-pointer" 
                                 onclick="window.location.href='{{ route('admin.courses.enrollments.show', [$course, $enrollment]) }}'">
                                <img class="h-10 w-10 rounded-full" 
                                     src="{{ $enrollment->user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($enrollment->user->first_name.' '.$enrollment->user->last_name) }}" 
                                     alt="{{ $enrollment->user->first_name }}">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $enrollment->user->first_name }} {{ $enrollment->user->last_name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $enrollment->user->email }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 pl-8">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Enrolled:</span>
                                <span class="text-sm">{{ $enrollment->enrolled_at ? date('M d, Y', strtotime($enrollment->enrolled_at)) : 'N/A' }}</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Progress:</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-24 h-2 bg-gray-200 rounded">
                                        <div class="h-full bg-blue-600 rounded" 
                                             style="width: {{ $enrollment->progress }}%">
                                        </div>
                                    </div>
                                    <span class="text-sm">{{ number_format($enrollment->progress, 1) }}%</span>
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Status:</span>
                                <span class="px-2 text-xs font-semibold rounded-full 
                                    {{ $enrollment->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($enrollment->status === 'active' ? 'bg-blue-100 text-blue-800' : 
                                       ($enrollment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                       'bg-red-100 text-red-800')) }}">
                                    {{ ucfirst($enrollment->status) }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Payment:</span>
                                <span class="px-2 text-xs font-semibold rounded-full 
                                    {{ $enrollment->payment_status === 'fully_paid' ? 'bg-green-100 text-green-800' : 
                                       ($enrollment->payment_status === 'partially_paid' ? 'bg-yellow-100 text-yellow-800' : 
                                       'bg-red-100 text-red-800') }}">
                                    {{ str_replace('_', ' ', ucfirst($enrollment->payment_status)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Desktop View (Table Layout) -->
            <div class="hidden md:block">
                <div class="overflow-x-auto">
                    <div class="inline-block min-w-full align-middle">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="w-12 px-6 py-3">
                                            <span class="sr-only">Select</span>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Student
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Enrolled Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Progress
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Payment Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    @foreach($enrollments as $enrollment)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <!-- Checkbox cell with stopPropagation -->
                                            <td class="px-6 py-4" onclick="event.stopPropagation()">
                                                <input type="checkbox" 
                                                       class="enrollment-checkbox form-checkbox rounded border-gray-300"
                                                       value="{{ $enrollment->id }}">
                                            </td>
                                            
                                            <!-- Make other cells clickable -->
                                            <td class="px-6 py-4 whitespace-nowrap cursor-pointer"
                                                onclick="window.location.href='{{ route('admin.courses.enrollments.show', [$course, $enrollment]) }}'">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full" 
                                                             src="{{ $enrollment->user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($enrollment->user->first_name.' '.$enrollment->user->last_name) }}" 
                                                             alt="{{ $enrollment->user->first_name }}">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            {{ $enrollment->user->first_name }} {{ $enrollment->user->last_name }}
                                                        </div>
                                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ $enrollment->user->email }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap cursor-pointer"
                                                onclick="window.location.href='{{ route('admin.courses.enrollments.show', [$course, $enrollment]) }}'">
                                                {{ $enrollment->enrolled_at ? date('M d, Y', strtotime($enrollment->enrolled_at)) : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap cursor-pointer"
                                                onclick="window.location.href='{{ route('admin.courses.enrollments.show', [$course, $enrollment]) }}'">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-24 h-2 bg-gray-200 rounded">
                                                        <div class="h-full bg-blue-600 rounded" 
                                                             style="width: {{ $enrollment->progress }}%">
                                                        </div>
                                                    </div>
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ number_format($enrollment->progress, 1) }}%
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap cursor-pointer"
                                                onclick="window.location.href='{{ route('admin.courses.enrollments.show', [$course, $enrollment]) }}'">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $enrollment->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                                       ($enrollment->status === 'active' ? 'bg-blue-100 text-blue-800' : 
                                                       ($enrollment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                       'bg-red-100 text-red-800')) }}">
                                                    {{ ucfirst($enrollment->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap cursor-pointer"
                                                onclick="window.location.href='{{ route('admin.courses.enrollments.show', [$course, $enrollment]) }}'">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $enrollment->payment_status === 'fully_paid' ? 'bg-green-100 text-green-800' : 
                                                       ($enrollment->payment_status === 'partially_paid' ? 'bg-yellow-100 text-yellow-800' : 
                                                       'bg-red-100 text-red-800') }}">
                                                    {{ str_replace('_', ' ', ucfirst($enrollment->payment_status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $enrollments->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const enrollmentCheckboxes = document.querySelectorAll('.enrollment-checkbox');
    const selectedCountElement = document.getElementById('selectedCount');
    const bulkActionsContainer = document.getElementById('bulkActionsContainer');
    let selectedEnrollments = [];

    // Handle "Select All" checkbox
    selectAllCheckbox.addEventListener('change', function() {
        enrollmentCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            handleCheckboxChange(checkbox);
        });
    });

    // Handle individual checkboxes
    enrollmentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            handleCheckboxChange(this);
            updateSelectAllCheckbox();
        });
    });

    function handleCheckboxChange(checkbox) {
        if (checkbox.checked) {
            if (!selectedEnrollments.includes(checkbox.value)) {
                selectedEnrollments.push(checkbox.value);
            }
        } else {
            selectedEnrollments = selectedEnrollments.filter(id => id !== checkbox.value);
        }
        updateSelectedCount();
    }

    function updateSelectAllCheckbox() {
        selectAllCheckbox.checked = selectedEnrollments.length === enrollmentCheckboxes.length;
    }

    function updateSelectedCount() {
        selectedCountElement.textContent = selectedEnrollments.length;
        bulkActionsContainer.style.display = selectedEnrollments.length > 0 ? 'flex' : 'none';
    }

    // Toggle dropdowns
    window.toggleDropdown = function(dropdownId) {
        const dropdown = document.getElementById(dropdownId);
        document.querySelectorAll('.relative > div').forEach(el => {
            if (el.id !== dropdownId) el.classList.add('hidden');
        });
        dropdown.classList.toggle('hidden');
    };

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.relative')) {
            document.querySelectorAll('.relative > div').forEach(el => {
                el.classList.add('hidden');
            });
        }
    });

    // Bulk update function
    window.updateBulkEnrollments = function(action, value, buttonElement = null) {
        if (selectedEnrollments.length === 0) {
            showNotification('Error', 'Please select enrollments to update', 'error');
            return;
        }

        // Get the button and show loading state
        const button = action === 'progress' ? 
            document.getElementById('progressUpdateBtn') : 
            (buttonElement || null);

        if (button) {
            const updateText = button.querySelector('.status-text, .payment-text, .update-text');
            const loadingSpinner = button.querySelector('.loading-spinner');
            const originalText = updateText.textContent;
            updateText.textContent = 'Updating...';
            loadingSpinner.classList.remove('hidden');
            button.disabled = true;

            // Disable all related buttons while updating
            if (action === 'status') {
                document.querySelectorAll('.status-btn').forEach(btn => {
                    btn.disabled = true;
                });
            } else if (action === 'payment_status') {
                document.querySelectorAll('.payment-btn').forEach(btn => {
                    btn.disabled = true;
                });
            }
        }

        fetch(`{{ route('admin.courses.enrollments.bulk-update', $course->id) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                enrollment_ids: selectedEnrollments,
                action: action,
                value: value
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Failed to update enrollments');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification('Success', `Updated ${data.count} enrollment(s) successfully`);
                setTimeout(() => window.location.reload(), 1500);
            } else {
                throw new Error(data.message || 'Failed to update enrollments');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error', error.message, 'error');
        })
        .finally(() => {
            // Reset button state
            if (button) {
                const updateText = button.querySelector('.status-text, .payment-text, .update-text');
                const loadingSpinner = button.querySelector('.loading-spinner');
                updateText.textContent = originalText;
                loadingSpinner.classList.add('hidden');
                button.disabled = false;

                // Re-enable all related buttons
                if (action === 'status') {
                    document.querySelectorAll('.status-btn').forEach(btn => {
                        btn.disabled = false;
                    });
                } else if (action === 'payment_status') {
                    document.querySelectorAll('.payment-btn').forEach(btn => {
                        btn.disabled = false;
                    });
                }
            }
        });
    };

    window.sendBulkNotices = function(button) {
        if (selectedEnrollments.length === 0) {
            showNotification('Error', 'Please select enrollments to send notice to', 'error');
            return;
        }

        const title = document.getElementById('noticeTitle').value;
        const content = document.getElementById('noticeContent').value;
        const priority = document.getElementById('noticePriority').value;
        const isImportant = document.getElementById('noticeImportant').checked;

        if (!title || !content) {
            showNotification('Error', 'Please fill in all required fields', 'error');
            return;
        }

        // Show loading state
        const updateText = button.querySelector('.notice-text');
        const loadingSpinner = button.querySelector('.loading-spinner');
        const originalText = updateText.textContent;
        updateText.textContent = 'Sending...';
        loadingSpinner.classList.remove('hidden');
        button.disabled = true;

        fetch(`{{ route('admin.courses.enrollments.send-notices', $course->id) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                enrollment_ids: selectedEnrollments,
                title: title,
                content: content,
                priority: priority,
                is_important: isImportant
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Success', data.message);
                document.getElementById('noticeDropdown').classList.add('hidden');
                document.getElementById('noticeTitle').value = '';
                document.getElementById('noticeContent').value = '';
                document.getElementById('noticePriority').value = 'low';
                document.getElementById('noticeImportant').checked = false;
            } else {
                throw new Error(data.message || 'Failed to send notices');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error', error.message, 'error');
        })
        .finally(() => {
            // Reset button state
            updateText.textContent = originalText;
            loadingSpinner.classList.add('hidden');
            button.disabled = false;
        });
    };
});
</script>
@endpush
@endsection 