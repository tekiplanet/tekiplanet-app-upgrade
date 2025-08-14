@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.grits.index') }}" 
               class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                GRIT Details
            </h2>
        </div>
        <div class="flex gap-2">
            @if($grit->admin_approval_status === 'pending')
                <button onclick="approveGrit('{{ $grit->id }}')" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Approve GRIT
                </button>
                <button onclick="rejectGrit('{{ $grit->id }}')" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Reject GRIT
                </button>
            @endif
            <a href="{{ route('admin.grits.edit', $grit) }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Edit
            </a>
            <form action="{{ route('admin.grits.destroy', $grit) }}" 
                  method="POST" 
                  onsubmit="return confirm('Are you sure you want to delete this GRIT?');"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <!-- GRIT Information -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                Basic Information
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">Title</label>
                    <p class="text-gray-900 dark:text-gray-100">{{ $grit->title }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">Category</label>
                    <p class="text-gray-900 dark:text-gray-100">{{ $grit->category->name }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">Budget</label>
                    <p class="text-gray-900 dark:text-gray-100">
                        @php
                            $displayBudget = $grit->owner_budget ?? $grit->budget;
                            $displayCurrency = $grit->owner_currency ?? '₦';
                        @endphp
                        {{ $displayCurrency }}{{ number_format($displayBudget, 2) }}
                    </p>
                </div>
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">Deadline</label>
                    <p class="text-gray-900 dark:text-gray-100">{{ optional($grit->deadline)->format('M d, Y') }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">Status</label>
                    @php
                        $statusClass = $grit->status === 'open' ? 'bg-green-100 text-green-800' : 
                                     ($grit->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                     ($grit->status === 'completed' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800'));
                    @endphp
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                        {{ ucfirst($grit->status) }}
                    </span>
                </div>
                @if($grit->admin_approval_status)
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">Approval Status</label>
                    @php
                        $approvalClass = $grit->admin_approval_status === 'approved' ? 'bg-green-100 text-green-800' : 
                                       ($grit->admin_approval_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                    @endphp
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $approvalClass }}">
                        {{ ucfirst($grit->admin_approval_status) }}
                    </span>
                </div>
                @endif
            </div>
        </div>

        <!-- Creator Information -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                Creator Information
            </h3>
            <div class="space-y-4">
                @if($grit->user)
                <!-- User Details -->
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">User Account</label>
                    <div class="space-y-2">
                        <div>
                            <a href="{{ route('admin.users.show', $grit->user->id) }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium">
                                {{ $grit->user->name }}
                            </a>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $grit->user->email }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Phone: {{ $grit->user->phone ?? 'Not provided' }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Member Since: {{ $grit->user->created_at->format('M d, Y') }}
                        </div>
                    </div>
                </div>

                <!-- Business Profile Details -->
                @if($grit->user->businessProfile)
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">Business Profile</label>
                    <div class="space-y-2">
                        <div>
                            <a href="{{ route('admin.businesses.show', $grit->user->businessProfile->id) }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium">
                                {{ $grit->user->businessProfile->business_name ?? $grit->user->name }}
                            </a>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Industry: {{ $grit->user->businessProfile->industry ?? 'Not specified' }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Location: {{ $grit->user->businessProfile->city ?? '' }}{{ $grit->user->businessProfile->state ? ', ' . $grit->user->businessProfile->state : '' }}{{ $grit->user->businessProfile->country ? ', ' . $grit->user->businessProfile->country : '' }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Website: 
                            @if($grit->user->businessProfile->website)
                                <a href="{{ $grit->user->businessProfile->website }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                    {{ $grit->user->businessProfile->website }}
                                </a>
                            @else
                                Not provided
                            @endif
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Business Size: {{ $grit->user->businessProfile->business_size ?? 'Not specified' }}
                        </div>
                    </div>
                </div>
                @else
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">Business Profile</label>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No business profile created yet</p>
                </div>
                @endif

                <!-- GRIT Creation Info -->
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">GRIT Creation</label>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Created: {{ $grit->created_at->format('M d, Y H:i') }}
                    </div>
                    @if($grit->admin_approval_status)
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Approval Status: 
                        @php
                            $creationApprovalClass = $grit->admin_approval_status === 'approved' ? 'bg-green-100 text-green-800' : 
                                                   ($grit->admin_approval_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $creationApprovalClass }}">
                            {{ ucfirst($grit->admin_approval_status) }}
                        </span>
                    </div>
                    @endif
                </div>
                @else
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Created by Admin</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Description and Requirements -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">
                        Description
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $grit->description }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">
                        Requirements
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $grit->requirements }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications Section -->
    <div class="mt-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                Applications ({{ $grit->applications->count() }})
            </h3>
            <a href="{{ route('admin.grits.applications.index', $grit) }}" 
               class="text-blue-600 hover:text-blue-900">
                View All
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-left bg-gray-50 dark:bg-gray-700">
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Professional
                            </th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Applied At
                            </th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($grit->applications->take(5) as $application)
                            <tr>
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $application->professional->user->name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $application->professional->user->email }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $application->professional->category->name }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $appStatusClass = $application->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                                        ($application->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                        ($application->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'));
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $appStatusClass }}">
                                        {{ ucfirst($application->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    {{ $application->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.grits.applications.show', [$grit, $application]) }}" 
                                       class="text-blue-600 hover:text-blue-900">
                                        View
                                    </a>
                                    @if($application->status === 'pending')
                                        <form action="{{ route('admin.grits.applications.update-status', [$grit, $application]) }}" 
                                              method="POST" 
                                              class="inline ml-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" 
                                                    class="text-green-600 hover:text-green-900">
                                                Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.grits.applications.update-status', [$grit, $application]) }}" 
                                              method="POST" 
                                              class="inline ml-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900">
                                                Reject
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    No applications yet
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function approveGrit(gritId) {
    Swal.fire({
        title: 'Approve GRIT?',
        text: 'Are you sure you want to approve this GRIT?',
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
                        text: data.message || 'An error occurred while approving the GRIT.',
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
                        text: data.message || 'An error occurred while rejecting the GRIT.',
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