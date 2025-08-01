@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                    {{ $business->business_name }}
                </h2>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ $business->business_email }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button onclick="openEditModal()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Edit Details
                </button>
                <button onclick="toggleStatus()" 
                        class="px-4 py-2 {{ $business->status === 'active' ? 'bg-red-600' : 'bg-green-600' }} text-white rounded-lg hover:{{ $business->status === 'active' ? 'bg-red-700' : 'bg-green-700' }}">
                    {{ $business->status === 'active' ? 'Deactivate' : 'Activate' }}
                </button>
            </div>
        </div>
    </div>

    <!-- Business Information -->
    <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                Business Information
            </h3>
            <div class="space-y-4">
                <!-- Basic Information -->
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Business Type</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ $business->business_type }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Phone Number</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ $business->phone_number ?? 'N/A' }}</p>
                </div>
                
                <!-- Registration Details -->
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Registration Number</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ $business->registration_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Tax Number</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ $business->tax_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Website</label>
                    <p class="text-gray-800 dark:text-gray-200">
                        @if($business->website)
                            <a href="{{ $business->website }}" target="_blank" class="text-blue-600 hover:underline">
                                {{ $business->website }}
                            </a>
                        @else
                            N/A
                        @endif
                    </p>
                </div>
                
                <!-- Location Information -->
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Location</label>
                    <p class="text-gray-800 dark:text-gray-200">
                        {{ $business->city }}, {{ $business->state }}, {{ $business->country }}
                    </p>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Address</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ $business->address ?? 'N/A' }}</p>
                </div>
                
                <!-- Additional Information -->
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Description</label>
                    <p class="text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $business->description ?? 'N/A' }}</p>
                </div>
                
                <!-- Status Information -->
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Status</label>
                    <p class="mt-1">
                        <span class="px-2 py-1 text-sm rounded-full {{ $business->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($business->status) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                Statistics
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('admin.businesses.customers.index', $business) }}" 
                   class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Customers</p>
                    <p class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
                        {{ $business->business_customers()->count() }}
                    </p>
                </a>
                <a href="{{ route('admin.businesses.invoices.index', $business) }}" 
                   class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Invoices</p>
                    <p class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
                        {{ $business->business_invoices()->count() }}
                    </p>
                </a>
            </div>
        </div>

        <!-- Owner Information Card -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                Business Owner
            </h3>
            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    @if($business->user->avatar)
                        <img src="{{ Storage::url($business->user->avatar) }}" 
                             alt="Owner Avatar" 
                             class="h-12 w-12 rounded-full object-cover">
                    @else
                        <div class="h-12 w-12 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                            <span class="text-lg font-semibold text-gray-600 dark:text-gray-300">
                                {{ strtoupper(substr($business->user->name, 0, 1)) }}
                            </span>
                        </div>
                    @endif
                    <div>
                        <h4 class="text-base font-medium text-gray-900 dark:text-gray-100">
                            {{ $business->user->name }}
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $business->user->email }}
                        </p>
                    </div>
                </div>
                
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Phone</label>
                            <p class="text-gray-800 dark:text-gray-200">{{ $business->user->phone ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Account Status</label>
                            <p class="mt-1">
                                <span class="px-2 py-1 text-sm rounded-full {{ $business->user->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $business->user->status ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="text-sm text-gray-600 dark:text-gray-400">Member Since</label>
                        <p class="text-gray-800 dark:text-gray-200">
                            {{ $business->user->created_at->format('M d, Y') }}
                        </p>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('admin.users.show', $business->user) }}" 
                           class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            <span>View Full Profile</span>
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-[60]">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl dark:bg-gray-800 w-full max-w-2xl max-h-[90vh] flex flex-col">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                    Edit Business Details
                </h3>
                <div class="overflow-y-auto max-h-[calc(90vh-12rem)] pr-2">
                    <form id="editForm" class="space-y-4">
                        <!-- Basic Information -->
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Business Name</label>
                            <input type="text" name="business_name" value="{{ $business->business_name }}"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Business Email</label>
                            <input type="email" name="business_email" value="{{ $business->business_email }}"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Phone Number</label>
                            <input type="text" name="phone_number" value="{{ $business->phone_number }}"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        
                        <!-- Registration Details -->
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Registration Number</label>
                            <input type="text" name="registration_number" value="{{ $business->registration_number }}"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Tax Number</label>
                            <input type="text" name="tax_number" value="{{ $business->tax_number }}"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Website</label>
                            <input type="url" name="website" value="{{ $business->website }}"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        
                        <!-- Business Type -->
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Business Type</label>
                            <p class="text-gray-800 dark:text-gray-200">{{ $business->business_type }}</p>
                        </div>
                        
                        <!-- Address Information -->
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Address</label>
                            <textarea name="address" rows="2"
                                      class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">{{ $business->address }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">City</label>
                                <input type="text" name="city" value="{{ $business->city }}"
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">State</label>
                                <select name="state" 
                                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                                    @foreach([
                                        'Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 
                                        'Benue', 'Borno', 'Cross River', 'Delta', 'Ebonyi', 'Edo', 'Ekiti', 
                                        'Enugu', 'FCT', 'Gombe', 'Imo', 'Jigawa', 'Kaduna', 'Kano', 'Katsina', 
                                        'Kebbi', 'Kogi', 'Kwara', 'Lagos', 'Nasarawa', 'Niger', 'Ogun', 'Ondo', 
                                        'Osun', 'Oyo', 'Plateau', 'Rivers', 'Sokoto', 'Taraba', 'Yobe', 'Zamfara'
                                    ] as $state)
                                        <option value="{{ $state }}" {{ $business->state === $state ? 'selected' : '' }}>
                                            {{ $state }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Country</label>
                                <p class="text-gray-800 dark:text-gray-200">Nigeria</p>
                                <input type="hidden" name="country" value="Nigeria">
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Description</label>
                            <textarea name="description" rows="3"
                                      class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">{{ $business->description }}</textarea>
                        </div>
                    </form>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 rounded-b-lg flex justify-end gap-4 mt-auto">
                <button onclick="closeEditModal()" 
                        class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-700">
                    Cancel
                </button>
                <button onclick="submitEdit()" 
                        id="saveButton"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <span class="inline-flex items-center">
                        <svg id="saveSpinner" class="w-4 h-4 mr-2 animate-spin hidden" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="saveButtonText">Save Changes</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Status Confirmation Modal -->
<div id="confirmStatusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-[60]">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl dark:bg-gray-800 w-full max-w-md">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                    Confirm Status Change
                </h3>
                <p class="text-gray-600 dark:text-gray-400" id="confirmStatusMessage"></p>
                <div id="deactivationReasonContainer" class="mt-4 hidden">
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                        Reason for Deactivation
                    </label>
                    <textarea id="deactivationReason" rows="3"
                              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600"
                              placeholder="Please provide a reason for deactivation..."></textarea>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 rounded-b-lg flex justify-end gap-4">
                <button onclick="closeConfirmStatusModal()" 
                        class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-700">
                    Cancel
                </button>
                <button onclick="confirmToggleStatus()"
                        id="confirmStatusButton"
                        class="px-4 py-2 text-white rounded-lg">
                    <span class="inline-flex items-center">
                        <svg id="statusSpinner" class="w-4 h-4 mr-2 animate-spin hidden" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="confirmStatusButtonText">Confirm</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let isProcessing = false;

function showNotification(title, message, type = 'success') {
    const event = new CustomEvent('notify', {
        detail: {
            title: title,
            message: message,
            type: type
        }
    });
    window.dispatchEvent(event);
}

function openEditModal() {
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

async function submitEdit() {
    if (isProcessing) return;
    
    try {
        isProcessing = true;
        const saveSpinner = document.getElementById('saveSpinner');
        const saveButtonText = document.getElementById('saveButtonText');
        saveSpinner.classList.remove('hidden');
        saveButtonText.textContent = 'Saving...';

        const form = document.getElementById('editForm');
        const response = await fetch('{{ route("admin.businesses.update", ["business" => $business]) }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(Object.fromEntries(new FormData(form)))
        });

        const data = await response.json();

        if (response.ok) {
            showNotification('Success', data.message);
            closeEditModal();
            location.reload();
        } else {
            throw new Error(data.message || 'Something went wrong');
        }
    } catch (error) {
        showNotification('Error', error.message, 'error');
    } finally {
        isProcessing = false;
        const saveSpinner = document.getElementById('saveSpinner');
        const saveButtonText = document.getElementById('saveButtonText');
        saveSpinner.classList.add('hidden');
        saveButtonText.textContent = 'Save Changes';
    }
}

function toggleStatus() {
    const isCurrentlyActive = '{{ $business->status }}' === 'active';
    const confirmStatusModal = document.getElementById('confirmStatusModal');
    const confirmStatusMessage = document.getElementById('confirmStatusMessage');
    const confirmStatusButton = document.getElementById('confirmStatusButton');
    const deactivationReasonContainer = document.getElementById('deactivationReasonContainer');

    confirmStatusMessage.textContent = `Are you sure you want to ${isCurrentlyActive ? 'deactivate' : 'activate'} this business?`;
    confirmStatusButton.className = `px-4 py-2 text-white rounded-lg ${isCurrentlyActive ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'}`;
    
    // Show/hide reason field based on action
    if (isCurrentlyActive) {
        deactivationReasonContainer.classList.remove('hidden');
    } else {
        deactivationReasonContainer.classList.add('hidden');
    }
    
    confirmStatusModal.classList.remove('hidden');
}

function closeConfirmStatusModal() {
    document.getElementById('confirmStatusModal').classList.add('hidden');
}

async function confirmToggleStatus() {
    if (isProcessing) return;

    const isCurrentlyActive = '{{ $business->status }}' === 'active';
    const deactivationReason = document.getElementById('deactivationReason').value;

    // Validate reason if deactivating
    if (isCurrentlyActive && !deactivationReason.trim()) {
        showNotification('Error', 'Please provide a reason for deactivation', 'error');
        return;
    }

    try {
        isProcessing = true;
        const statusSpinner = document.getElementById('statusSpinner');
        const confirmStatusButtonText = document.getElementById('confirmStatusButtonText');
        statusSpinner.classList.remove('hidden');
        confirmStatusButtonText.textContent = 'Processing...';

        const response = await fetch('{{ route("admin.businesses.toggle-status", ["business" => $business]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ reason: deactivationReason })
        });

        const data = await response.json();

        if (response.ok) {
            showNotification('Success', data.message);
            closeConfirmStatusModal();
            location.reload();
        } else {
            throw new Error(data.message || 'Something went wrong');
        }
    } catch (error) {
        showNotification('Error', error.message, 'error');
    } finally {
        isProcessing = false;
        const statusSpinner = document.getElementById('statusSpinner');
        const confirmStatusButtonText = document.getElementById('confirmStatusButtonText');
        statusSpinner.classList.add('hidden');
        confirmStatusButtonText.textContent = 'Confirm';
    }
}
</script>
@endpush 