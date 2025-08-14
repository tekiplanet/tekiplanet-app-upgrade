@extends('admin.layouts.app')

@section('content')
@include('admin.components.notification')

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('{{ session('success') }}');
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('{{ session('error') }}', 'error');
        });
    </script>
@endif
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            GRITs Management
        </h2>
        <a href="{{ route('admin.grits.create') }}" 
           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Create GRIT
        </a>
    </div>

    <!-- Tabs for Hustles vs GRITs -->
    <div class="mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex items-center gap-6">
                <a href="{{ route('admin.grits.index', ['type' => 'all']) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ !request('type') || request('type') === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    All Hustles & GRITs
                </a>
                <a href="{{ route('admin.grits.index', ['type' => 'admin_created']) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('type') === 'admin_created' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Admin Created
                </a>
                <a href="{{ route('admin.grits.index', ['type' => 'business_created']) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ request('type') === 'business_created' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Business GRITs
                    @if(isset($pendingGritsCount) && $pendingGritsCount > 0)
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ $pendingGritsCount }} Pending
                        </span>
                    @endif
                </a>
            </nav>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
        <form action="{{ route('admin.grits.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Search hustles..."
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">

            <select name="status" 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                <option value="">All Status</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>

            @if(request('type') === 'business_created' || !request('type'))
            <select name="approval_status" 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                <option value="">All Approval Status</option>
                <option value="pending" {{ request('approval_status') === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                <option value="approved" {{ request('approval_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('approval_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            @endif

            <select name="category" 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Filter
            </button>
        </form>
    </div>

    <!-- Hustles List -->
    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap">
                <thead>
                    <tr class="text-left bg-gray-50 dark:bg-gray-700">
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Title
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Category
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Budget
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        @if(request('type') === 'business_created' || !request('type'))
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Approval Status
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Created By
                        </th>
                        @endif
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Applications
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($grits as $grit)
                        <tr>
                            <td class="px-6 py-4">
                                {{ $grit->title }}
                                @if($grit->created_by_user_id)
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                        GRIT
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ $grit->category->name }}
                            </td>
                            <td class="px-6 py-4">
                                @if($grit->created_by_user_id && $grit->owner_budget)
                                    {{ $grit->owner_currency ?? '₦' }}{{ number_format($grit->owner_budget, 2) }}
                                @else
                                    ₦{{ number_format($grit->budget, 2) }}
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $grit->status === 'open' ? 'bg-green-100 text-green-800' : 
                                       ($grit->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                       ($grit->status === 'completed' ? 'bg-gray-100 text-gray-800' : 
                                        'bg-red-100 text-red-800')) }}">
                                    {{ ucfirst($grit->status) }}
                                </span>
                            </td>
                            @if(request('type') === 'business_created' || !request('type'))
                            <td class="px-6 py-4">
                                @if($grit->created_by_user_id)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $grit->admin_approval_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($grit->admin_approval_status === 'approved' ? 'bg-green-100 text-green-800' : 
                                            'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($grit->admin_approval_status ?? 'pending') }}
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Admin Created
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($grit->created_by_user_id)
                                    <span class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $grit->user->name ?? 'Unknown' }}
                                    </span>
                                    <span class="text-xs text-gray-500 block">{{ $grit->user->email ?? '' }}</span>
                                @else
                                    <span class="text-sm text-gray-500">Admin</span>
                                @endif
                            </td>
                            @endif
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.grits.applications.index', $grit) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    {{ $grit->applications_count ?? $grit->applications->count() }} Applications
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.grits.show', $grit) }}" 
                                       class="text-blue-600 hover:text-blue-900">View</a>
                                    @if($grit->created_by_user_id && ($grit->admin_approval_status === 'pending' || !$grit->admin_approval_status))
                                        <button onclick="approveGrit('{{ $grit->id }}')" 
                                                class="text-green-600 hover:text-green-900">Approve</button>
                                        <button onclick="rejectGrit('{{ $grit->id }}')" 
                                                class="text-red-600 hover:text-red-900">Reject</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ request('type') === 'business_created' || !request('type') ? '8' : '6' }}" class="px-6 py-4 text-center text-gray-500">
                                No hustles found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t">
            {{ $grits->links() }}
        </div>
    </div>
</div>

<!-- JavaScript for GRIT approval/rejection -->
<script>
function approveGrit(gritId) {
    Swal.fire({
        title: 'Approve GRIT?',
        text: 'Are you sure you want to approve this GRIT? It will become visible to professionals.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, Approve!',
        cancelButtonText: 'Cancel',
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Approving...',
                text: 'Please wait while we approve the GRIT.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/admin/grits/${gritId}/approval`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    status: 'approved'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Approved!',
                        text: data.message || 'GRIT has been approved successfully.',
                        icon: 'success',
                        confirmButtonColor: '#10B981',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to approve GRIT.',
                        icon: 'error',
                        confirmButtonColor: '#EF4444'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while approving the GRIT.',
                    icon: 'error',
                    confirmButtonColor: '#EF4444'
                });
            });
        }
    });
}

function rejectGrit(gritId) {
    Swal.fire({
        title: 'Reject GRIT',
        text: 'Please provide a reason for rejection:',
        input: 'text',
        inputPlaceholder: 'Enter rejection reason...',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Reject',
        cancelButtonText: 'Cancel',
        allowOutsideClick: false,
        allowEscapeKey: false,
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a reason for rejection!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Rejecting...',
                text: 'Please wait while we reject the GRIT.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/admin/grits/${gritId}/approval`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    status: 'rejected',
                    reason: result.value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Rejected!',
                        text: data.message || 'GRIT has been rejected successfully.',
                        icon: 'success',
                        confirmButtonColor: '#EF4444',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to reject GRIT.',
                        icon: 'error',
                        confirmButtonColor: '#EF4444'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while rejecting the GRIT.',
                    icon: 'error',
                    confirmButtonColor: '#EF4444'
                });
            });
        }
    });
}
</script>
@endsection 