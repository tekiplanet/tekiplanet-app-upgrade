@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Shipping Methods
        </h2>
        <button onclick="openCreateModal()" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Add Method
        </button>
    </div>

    <!-- Add this after the header section and before the methods list -->
    <div class="mb-6">
        <form action="{{ route('admin.shipping.methods.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Search
                </label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search methods..."
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Status
                </label>
                <select name="status" 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- Cost Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Base Cost Range (₦)
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" 
                           name="min_cost" 
                           value="{{ request('min_cost') }}"
                           placeholder="Min"
                           min="0"
                           step="0.01"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <input type="number" 
                           name="max_cost" 
                           value="{{ request('max_cost') }}"
                           placeholder="Max"
                           min="0"
                           step="0.01"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <!-- Max Delivery Days -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Max Delivery Days
                </label>
                <input type="number" 
                       name="max_delivery_days" 
                       value="{{ request('max_delivery_days') }}"
                       placeholder="Max days"
                       min="0"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Filter Buttons -->
            <div class="md:col-span-4 flex justify-end gap-2">
                <button type="submit" 
                        class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'min_cost', 'max_cost', 'max_delivery_days']))
                    <a href="{{ route('admin.shipping.methods.index') }}" 
                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Methods List -->
    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Method Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Base Cost
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Delivery Time
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Priority
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($methods as $method)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $method->name }}
                                </div>
                                @if($method->description)
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $method->description }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    ₦{{ number_format($method->base_cost, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $method->estimated_days_min }}-{{ $method->estimated_days_max }} days
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $method->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $method->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $method->priority }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="openEditModal({{ json_encode($method) }})" 
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">
                                    Edit
                                </button>
                                @if($method->orders()->exists())
                                    <button onclick="toggleMethodStatus('{{ $method->id }}', {{ !$method->is_active }})"
                                            class="text-{{ $method->is_active ? 'yellow' : 'green' }}-600 hover:text-{{ $method->is_active ? 'yellow' : 'green' }}-900 dark:text-{{ $method->is_active ? 'yellow' : 'green' }}-400 dark:hover:text-{{ $method->is_active ? 'yellow' : 'green' }}-300">
                                        {{ $method->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                @else
                                    <button onclick="deleteMethod('{{ $method->id }}')"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        Delete
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No shipping methods found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($methods->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $methods->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Create/Edit Modal -->
@include('admin.shipping.methods.partials.modal')

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Confirm Deletion
                </h3>
                <p class="text-gray-700 dark:text-gray-300 mb-4">
                    Are you sure you want to delete this shipping method? This action cannot be undone.
                </p>
                <div class="flex justify-end gap-2">
                    <button onclick="closeDeleteConfirmModal()" 
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button id="confirmDeleteButton"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-2">
                        <span id="deleteButtonText">Delete</span>
                        <span id="deleteLoadingSpinner" class="hidden">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Change Confirmation Modal -->
<div id="statusConfirmModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Confirm Status Change
                </h3>
                <p id="statusConfirmMessage" class="text-gray-700 dark:text-gray-300 mb-4"></p>
                <div class="flex justify-end gap-2">
                    <button onclick="closeStatusConfirmModal()" 
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button id="confirmStatusButton"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <span id="statusButtonText">Confirm</span>
                        <span id="statusLoadingSpinner" class="hidden">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const methods = @json($methods->items());
const modal = document.getElementById('methodModal');
const form = document.getElementById('methodForm');
const submitButton = form.querySelector('button[type="submit"]');
const loadingSpinner = document.getElementById('loadingSpinner');
const submitButtonText = document.getElementById('submitButtonText');
let isEditMode = false;
let methodToDelete = null;
let statusChangeData = null;

function openCreateModal() {
    isEditMode = false;
    form.reset();
    document.getElementById('modalTitle').textContent = 'Create Shipping Method';
    submitButtonText.textContent = 'Create Method';
    modal.classList.remove('hidden');
}

function openEditModal(method) {
    isEditMode = true;
    document.getElementById('methodId').value = method.id;
    document.getElementById('methodName').value = method.name;
    document.getElementById('methodDescription').value = method.description || '';
    document.getElementById('baseCost').value = method.base_cost;
    document.getElementById('estimatedDaysMin').value = method.estimated_days_min;
    document.getElementById('estimatedDaysMax').value = method.estimated_days_max;
    document.getElementById('isActive').checked = method.is_active;
    document.getElementById('priority').value = method.priority;

    // Set zone rates
    method.zone_rates.forEach(rate => {
        document.querySelector(`[name="zone_rates[${rate.zone_id}][rate]"]`).value = rate.rate;
        document.querySelector(`[name="zone_rates[${rate.zone_id}][estimated_days]"]`).value = rate.estimated_days;
    });

    document.getElementById('modalTitle').textContent = 'Edit Shipping Method';
    submitButtonText.textContent = 'Update Method';
    modal.classList.remove('hidden');
}

function closeModal() {
    modal.classList.add('hidden');
    form.reset();
    // Clear zone rates
    document.querySelectorAll('[name^="zone_rates["]').forEach(input => input.value = '');
}

async function handleSubmit(e) {
    e.preventDefault();
    
    submitButton.disabled = true;
    loadingSpinner.classList.remove('hidden');
    submitButtonText.textContent = isEditMode ? 'Updating...' : 'Creating...';
    
    const formData = new FormData(form);
    const methodId = document.getElementById('methodId').value;
    
    // Convert form data to object and handle boolean
    const formObject = Object.fromEntries(formData);
    formObject.is_active = form.elements.is_active.checked;
    
    try {
        const url = isEditMode 
            ? `{{ url('admin/shipping/methods') }}/${methodId}`
            : '{{ route('admin.shipping.methods.store') }}';
        
        const response = await fetch(url, {
            method: isEditMode ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formObject)
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || `HTTP error! status: ${response.status}`);
        }

        if (data.success) {
            showNotification('Success', data.message);
            window.location.reload();
        } else {
            throw new Error(data.message || 'Something went wrong');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error', error.message, 'error');
    } finally {
        submitButton.disabled = false;
        loadingSpinner.classList.add('hidden');
        submitButtonText.textContent = isEditMode ? 'Update Method' : 'Create Method';
    }
}

function deleteMethod(methodId) {
    methodToDelete = methodId;
    deleteConfirmModal.classList.remove('hidden');
}

function closeDeleteConfirmModal() {
    deleteConfirmModal.classList.add('hidden');
    methodToDelete = null;
}

async function confirmDelete() {
    const button = document.getElementById('confirmDeleteButton');
    const loadingSpinner = document.getElementById('deleteLoadingSpinner');
    const buttonText = document.getElementById('deleteButtonText');
    
    button.disabled = true;
    loadingSpinner.classList.remove('hidden');
    buttonText.textContent = 'Deleting...';

    try {
        const response = await fetch(`{{ url('admin/shipping/methods') }}/${methodToDelete}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || `HTTP error! status: ${response.status}`);
        }

        if (data.success) {
            showNotification('Success', data.message);
            window.location.reload();
        } else {
            throw new Error(data.message || 'Something went wrong');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error', error.message, 'error');
    } finally {
        button.disabled = false;
        loadingSpinner.classList.add('hidden');
        buttonText.textContent = 'Delete';
        closeDeleteConfirmModal();
    }
}

function toggleMethodStatus(methodId, newStatus) {
    statusChangeData = { methodId, newStatus };
    const method = methods.find(m => m.id === methodId);
    document.getElementById('statusConfirmMessage').textContent = 
        `Are you sure you want to ${newStatus ? 'activate' : 'deactivate'} the shipping method "${method.name}"?`;
    statusConfirmModal.classList.remove('hidden');
}

function closeStatusConfirmModal() {
    statusConfirmModal.classList.add('hidden');
    statusChangeData = null;
}

async function confirmStatusChange() {
    const button = document.getElementById('confirmStatusButton');
    const loadingSpinner = document.getElementById('statusLoadingSpinner');
    const buttonText = document.getElementById('statusButtonText');
    
    button.disabled = true;
    loadingSpinner.classList.remove('hidden');
    buttonText.textContent = 'Processing...';

    try {
        const currentData = getMethodCurrentData(statusChangeData.methodId);
        const response = await fetch(`{{ url('admin/shipping/methods') }}/${statusChangeData.methodId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                ...currentData,
                is_active: statusChangeData.newStatus ? 1 : 0,
            })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || `HTTP error! status: ${response.status}`);
        }

        if (data.success) {
            showNotification('Success', data.message);
            window.location.reload();
        } else {
            throw new Error(data.message || 'Something went wrong');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error', error.message, 'error');
    } finally {
        button.disabled = false;
        loadingSpinner.classList.add('hidden');
        buttonText.textContent = 'Confirm';
        closeStatusConfirmModal();
    }
}

// Helper function to get current method data
function getMethodCurrentData(methodId) {
    const method = methods.find(m => m.id === methodId);
    return {
        name: method.name,
        description: method.description,
        base_cost: method.base_cost,
        estimated_days_min: method.estimated_days_min,
        estimated_days_max: method.estimated_days_max,
        priority: method.priority,
        is_active: method.is_active ? 1 : 0,
        zone_rates: method.zone_rates.reduce((acc, rate) => {
            acc[rate.zone_id] = {
                rate: rate.rate,
                estimated_days: rate.estimated_days
            };
            return acc;
        }, {})
    };
}

// Close modal when clicking outside
modal.addEventListener('click', function(e) {
    if (e.target === modal) {
        closeModal();
    }
});

// Add event listeners for clicking outside modals
deleteConfirmModal.addEventListener('click', function(e) {
    if (e.target === deleteConfirmModal) {
        closeDeleteConfirmModal();
    }
});

statusConfirmModal.addEventListener('click', function(e) {
    if (e.target === statusConfirmModal) {
        closeStatusConfirmModal();
    }
});

// Add event listeners for confirm buttons
document.getElementById('confirmDeleteButton').addEventListener('click', confirmDelete);
document.getElementById('confirmStatusButton').addEventListener('click', confirmStatusChange);
</script>
@endpush
@endsection 