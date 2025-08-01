@extends('admin.layouts.app')

@section('title', 'Workstation Plans')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Workstation Plans') }}
        </h2>
        <a href="{{ route('admin.workstation.plans.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Create New Plan
        </a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Search and Filters -->
                    <div class="mb-4">
                        <form action="{{ route('admin.workstation.plans.index') }}" method="GET" class="flex gap-4">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Search plans..." 
                                class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            
                            <select name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>

                            <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Filter
                            </button>
                        </form>
                    </div>

                    <!-- Plans Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscriptions</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($plans as $plan)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $plan->name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $plan->slug }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            ₦{{ number_format($plan->price, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $plan->duration_days }} days
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $plan->subscriptions_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.workstation.plans.show', $plan) }}" 
                                               class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                            <a href="{{ route('admin.workstation.plans.edit', $plan) }}" 
                                               class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                            <button type="button" 
                                                data-plan-id="{{ $plan->id }}"
                                                data-is-active="{{ $plan->is_active }}"
                                                class="toggle-plan-status text-{{ $plan->is_active ? 'red' : 'green' }}-600 hover:text-{{ $plan->is_active ? 'red' : 'green' }}-900">
                                                {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No plans found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $plans->links() }}
                    </div>

                    <!-- Confirmation Modal -->
                    @include('admin.components.confirmation-modal')
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const modal = document.getElementById('confirmationModal');
const modalTitle = document.getElementById('modalTitle');
const modalMessage = document.getElementById('modalMessage');
const modalIcon = document.getElementById('modalIcon');
const confirmButton = document.getElementById('confirmButton');
const confirmButtonText = document.getElementById('confirmButtonText');
const confirmButtonLoader = document.getElementById('confirmButtonLoader');
const cancelButton = document.getElementById('cancelButton');

function showModal(options) {
    modalTitle.textContent = options.title;
    modalMessage.textContent = options.message;
    confirmButtonText.textContent = options.confirmText;
    
    // Set modal type (danger/warning)
    if (options.type === 'danger') {
        modalIcon.classList.add('bg-red-100');
        modalIcon.querySelector('svg').classList.add('text-red-600');
        confirmButton.classList.add('bg-red-600', 'hover:bg-red-700', 'focus:ring-red-500');
    } else {
        modalIcon.classList.add('bg-yellow-100');
        modalIcon.querySelector('svg').classList.add('text-yellow-600');
        confirmButton.classList.add('bg-yellow-600', 'hover:bg-yellow-700', 'focus:ring-yellow-500');
    }
    
    confirmButton.onclick = async () => {
        try {
            confirmButton.disabled = true;
            confirmButtonLoader.classList.remove('hidden');
            await options.onConfirm();
        } finally {
            hideModal();
        }
    };
    
    cancelButton.onclick = hideModal;
    
    modal.classList.remove('hidden');
}

function hideModal() {
    modal.classList.add('hidden');
    modalIcon.classList.remove('bg-red-100', 'bg-yellow-100');
    modalIcon.querySelector('svg').classList.remove('text-red-600', 'text-yellow-600');
    confirmButton.classList.remove(
        'bg-red-600', 'hover:bg-red-700', 'focus:ring-red-500',
        'bg-yellow-600', 'hover:bg-yellow-700', 'focus:ring-yellow-500'
    );
    confirmButton.disabled = false;
    confirmButtonLoader.classList.add('hidden');
}

// Add event listeners to all toggle buttons
document.querySelectorAll('.toggle-plan-status').forEach(button => {
    button.addEventListener('click', function() {
        const planId = this.dataset.planId;
        const isActive = this.dataset.isActive === "1";
        const actionType = isActive ? 'deactivate' : 'activate';
        
        showModal({
            title: `${isActive ? 'Deactivate' : 'Activate'} Plan`,
            message: `Are you sure you want to ${actionType} this plan?`,
            confirmText: isActive ? 'Deactivate' : 'Activate',
            type: isActive ? 'danger' : 'warning',
            onConfirm: async () => {
                try {
                    const response = await fetch(`/admin/workstation/plans/${planId}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (!response.ok) {
                        const errorData = await response.json().catch(() => null);
                        throw new Error(errorData?.message || 'Failed to update status');
                    }

                    const result = await response.json();

                    // Show success message
                    await Swal.fire({
                        title: 'Success!',
                        text: result.message || `Plan ${actionType}d successfully`,
                        icon: 'success',
                        confirmButtonText: 'OK',
                        timer: 2000,
                        timerProgressBar: true
                    });

                    window.location.reload();
                } catch (error) {
                    console.error('Error:', error);
                    
                    // Show error message
                    await Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while updating the plan status',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }
        });
    });
});

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target === modal) {
        hideModal();
    }
}
</script>
@endpush 