@extends('admin.layouts.app')

@section('title', 'Users Management')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Users</h1>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                Manage your platform users, their roles and permissions
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                Export Users
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            <form action="{{ route('admin.users.index') }}" method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-center sm:space-x-4">
                <div class="flex-1">
                    <label for="search" class="sr-only">Search</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="search" 
                            id="search"
                            value="{{ request('search') }}"
                            class="focus:ring-primary focus:border-primary block w-full pl-10 sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md"
                            placeholder="Search users..."
                        >
                    </div>
                </div>

                <div class="sm:flex-shrink-0">
                    <select 
                        name="account_type" 
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md"
                    >
                        <option value="">All Account Types</option>
                        <option value="student" {{ request('account_type') === 'student' ? 'selected' : '' }}>Student</option>
                        <option value="business" {{ request('account_type') === 'business' ? 'selected' : '' }}>Business</option>
                        <option value="professional" {{ request('account_type') === 'professional' ? 'selected' : '' }}>Professional</option>
                    </select>
                </div>

                <div class="sm:flex-shrink-0">
                    <select 
                        name="status" 
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md"
                    >
                        <option value="">All Status</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Filter
                </button>

                @if(request()->hasAny(['search', 'account_type', 'status']))
                    <a href="{{ route('admin.users.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Notification Modal -->
    <div id="notificationModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Send Notification</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Send notification to selected users
                    </p>
                </div>
                
                <form id="notificationForm" class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                        <input type="text" name="title" id="title" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    </div>
                    
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message</label>
                        <textarea name="message" id="message" rows="3" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"></textarea>
                    </div>
                    
                    <div class="mt-5 sm:mt-6 space-x-2 flex justify-end">
                        <button type="button" id="cancelBtn"
                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Cancel
                        </button>
                        <button type="submit"
                            id="sendBtn"
                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-primary border border-transparent rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            <span class="inline-flex items-center">
                                <svg id="loadingIcon" class="hidden w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span id="sendBtnText">Send</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Users List -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <!-- Desktop Table (hidden on mobile) -->
        <div class="hidden md:block overflow-x-auto">
            <form action="{{ route('admin.users.notify-bulk') }}" method="POST" id="bulkForm">
                @csrf
                <div class="p-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-4">
                        <select name="action" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                            <option value="">Bulk Actions</option>
                            <option value="notify">Send Notification</option>
                        </select>
                        <button type="submit" 
                                class="px-3 py-1 bg-primary text-white text-sm font-medium rounded hover:bg-primary-dark disabled:opacity-50"
                                id="applyBtn" 
                                disabled
                        >
                            Apply
                        </button>
                        <div class="ml-auto">
                            <label class="text-sm text-gray-600 dark:text-gray-400">
                                <input type="checkbox" id="selectAll" class="rounded border-gray-300 dark:border-gray-700">
                                Select All
                            </label>
                        </div>
                    </div>
                </div>

                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                <span class="sr-only">Select</span>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                User
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Account Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Joined
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" 
                                           name="selected_users[]" 
                                           value="{{ $user->id }}" 
                                           class="user-checkbox rounded border-gray-300 dark:border-gray-700">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($user->avatar)
                                                <img class="h-10 w-10 rounded-full" src="{{ $user->avatar }}" alt="">
                                            @else
                                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-500">
                                                    <span class="text-sm font-medium leading-none text-white">
                                                        {{ strtoupper(substr($user->first_name, 0, 1)) }}
                                                    </span>
                                                </span>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $user->first_name }} {{ $user->last_name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $user->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
                                        $user->businessProfile ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                        ($user->professional ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200') 
                                    }}">
                                        {{ $user->businessProfile ? 'Business' : ($user->professional ? 'Professional' : 'Student') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
                                        $user->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                    }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $user->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a 
                                        href="{{ route('admin.users.show', $user) }}" 
                                        class="text-primary hover:text-primary-dark inline-flex items-center"
                                    >
                                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($users as $user)
                <div class="p-4 space-y-3">
                    <!-- User Info -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 h-10 w-10">
                                @if($user->avatar)
                                    <img class="h-10 w-10 rounded-full" src="{{ $user->avatar }}" alt="">
                                @else
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-500">
                                        <span class="text-sm font-medium leading-none text-white">
                                            {{ strtoupper(substr($user->first_name, 0, 1)) }}
                                        </span>
                                    </span>
                                @endif
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $user->first_name }} {{ $user->last_name }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $user->email }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.users.show', $user) }}" 
                               class="inline-flex items-center p-2 text-primary hover:text-primary-dark"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                            <button class="inline-flex items-center p-2 text-red-600 hover:text-red-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Status Badges -->
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
                            $user->businessProfile ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                            ($user->professional ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 
                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200') 
                        }}">
                            {{ $user->businessProfile ? 'Business' : ($user->professional ? 'Professional' : 'Student') }}
                        </span>

                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
                            $user->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                        }}">
                            {{ ucfirst($user->status) }}
                        </span>

                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                            Joined {{ $user->created_at->format('M d, Y') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                    No users found.
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection 


@push('scripts')
<!-- Add SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const userCheckboxes = document.querySelectorAll('.user-checkbox');
        const bulkForm = document.getElementById('bulkForm');
        const applyBtn = document.getElementById('applyBtn');
        const notificationForm = document.getElementById('notificationForm');
        const notificationModal = document.getElementById('notificationModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const sendBtn = document.getElementById('sendBtn');
        const loadingIcon = document.getElementById('loadingIcon');
        const sendBtnText = document.getElementById('sendBtnText');

        // Modal functions
        function openModal() {
            notificationModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            notificationModal.classList.add('hidden');
            document.body.style.overflow = '';
            notificationForm.reset();
            // Reset button state
            loadingIcon.classList.add('hidden');
            sendBtnText.textContent = 'Send';
            sendBtn.disabled = false;
        }

        // Show error message
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonColor: '#3085d6'
            });
        }

        // Show success message
        function showSuccess(data) {
            let message = data.message;
            let details = '';
            
            if (data.details) {
                details = `<div class="mt-2 text-sm">
                    <p>Successfully sent: ${data.details.success_count}</p>
                    ${data.details.failed_count > 0 ? `<p>Failed: ${data.details.failed_count}</p>` : ''}
                </div>`;
            }

            Swal.fire({
                icon: 'success',
                title: 'Success',
                html: message + details,
                confirmButtonColor: '#3085d6'
            });
        }

        // Close modal when clicking outside
        notificationModal.addEventListener('click', function(e) {
            if (e.target === notificationModal) {
                closeModal();
            }
        });

        // Close modal with cancel button
        cancelBtn.addEventListener('click', closeModal);

        // Handle select all
        selectAll?.addEventListener('change', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
            updateApplyButton();
        });

        // Handle individual checkbox changes
        userCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateApplyButton);
        });

        // Update apply button state
        function updateApplyButton() {
            const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
            applyBtn.disabled = checkedCount === 0;
        }

        // Handle bulk form submission
        bulkForm?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const action = bulkForm.querySelector('[name="action"]').value;
            if (action === 'notify') {
                openModal();
            }
        });

        // Handle notification form submission
        notificationForm?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Confirm before sending
            const result = await Swal.fire({
                title: 'Send Notifications?',
                text: 'Are you sure you want to send these notifications?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, send them!'
            });

            if (!result.isConfirmed) {
                return;
            }
            
            // Show loading state
            loadingIcon.classList.remove('hidden');
            sendBtnText.textContent = 'Sending...';
            sendBtn.disabled = true;
            
            // Get form data
            const formData = new FormData();
            const selectedUsers = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
            
            try {
                const response = await fetch("{{ route('admin.users.notify-bulk') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        title: notificationForm.querySelector('#title').value,
                        message: notificationForm.querySelector('#message').value,
                        selected_users: JSON.stringify(selectedUsers)
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Something went wrong');
                }

                showSuccess(data);
                closeModal();

            } catch (error) {
                showError(error.message);
                // Reset button state but keep modal open
                loadingIcon.classList.add('hidden');
                sendBtnText.textContent = 'Send';
                sendBtn.disabled = false;
            }
        });
    });
</script>
@endpush