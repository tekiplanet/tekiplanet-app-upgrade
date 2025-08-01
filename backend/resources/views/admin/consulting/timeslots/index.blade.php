@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Consulting Time Slots
        </h2>
        <div class="flex gap-2">
            <button id="bulkDeleteBtn" 
                    type="button"
                    onclick="openBulkDeleteModal()"
                    disabled
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                Delete Selected
            </button>
            <button onclick="openCreateModal()" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Add Time Slot
            </button>
            <button onclick="openBulkCreateModal()" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Bulk Create
            </button>
        </div>
    </div>

    <!-- Search/Filter Section -->
    <div class="mb-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
        <form action="{{ route('admin.consulting.timeslots.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="date" 
                       name="date" 
                       value="{{ request('date') }}"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            <div class="w-full md:w-48">
                <select name="availability" 
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="">All Availability</option>
                    <option value="1" {{ request('availability') === '1' ? 'selected' : '' }}>Available</option>
                    <option value="0" {{ request('availability') === '0' ? 'selected' : '' }}>Not Available</option>
                </select>
            </div>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Filter
            </button>
        </form>
    </div>

    <!-- Time Slots List -->
    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap">
                <thead>
                    <tr class="text-left bg-gray-50 dark:bg-gray-700">
                        <th class="px-6 py-3">
                            <input type="checkbox" 
                                   id="selectAll"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Time</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Capacity</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Booked</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($timeSlots as $slot)
                        <tr>
                            <td class="px-6 py-4">
                                <input type="checkbox" 
                                       value="{{ $slot->id }}"
                                       class="slot-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4">{{ $slot->date->format('M d, Y') }}</td>
                            <td class="px-6 py-4">{{ $slot->time->format('h:i A') }}</td>
                            <td class="px-6 py-4">{{ $slot->capacity }}</td>
                            <td class="px-6 py-4">{{ $slot->booked_slots }}/{{ $slot->capacity }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $slot->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $slot->is_available ? 'Available' : 'Not Available' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <button onclick="openEditModal('{{ $slot->id }}')" 
                                            class="text-blue-600 hover:text-blue-900">Edit</button>
                                    <button onclick="openDeleteModal('{{ $slot->id }}')" 
                                            class="text-red-600 hover:text-red-900">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No time slots found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 overflow-x-auto">
            <div class="min-w-full flex justify-center sm:justify-between items-center flex-wrap gap-4">
                {{ $timeSlots->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="timeSlotModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4" id="modalTitle">Add Time Slot</h3>
                <form id="timeSlotForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="date" required
                               min="{{ date('Y-m-d') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Time</label>
                        <input type="time" name="time" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Capacity</label>
                        <input type="number" name="capacity" required min="1"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_available" id="is_available"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <label for="is_available" class="ml-2 block text-sm text-gray-900">
                            Available for booking
                        </label>
                    </div>
                </form>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end gap-2">
                <button onclick="closeModal()" 
                        class="px-4 py-2 text-gray-700 hover:text-gray-900">
                    Cancel
                </button>
                <button onclick="saveTimeSlot()" 
                        id="saveTimeSlotButton"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <div class="flex items-center gap-2">
                        <svg id="saveTimeSlotSpinner" class="animate-spin h-4 w-4 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="saveTimeSlotButtonText">Save</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Create Modal -->
<div id="bulkCreateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full dark:bg-gray-800">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Bulk Create Time Slots</h3>
                <form id="bulkCreateForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                        <input type="date" name="start_date" required min="{{ date('Y-m-d') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                        <input type="date" name="end_date" required min="{{ date('Y-m-d') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Days</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7] as $day => $value)
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="days[]" value="{{ $value }}"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $day }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Capacity</label>
                        <input type="number" name="capacity" required min="1"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Times</label>
                        <div class="flex gap-2">
                            <input type="time" name="times[]" required
                                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <button type="button" onclick="addTimeInput(this)"
                                    class="px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">+</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 flex justify-end gap-2">
                <button onclick="closeBulkCreateModal()" 
                        class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900">
                    Cancel
                </button>
                <button onclick="saveBulkCreate()" 
                        id="bulkCreateButton"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <div class="flex items-center gap-2">
                        <svg id="bulkCreateSpinner" class="animate-spin h-4 w-4 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="bulkCreateButtonText">Create</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full dark:bg-gray-800">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Edit Time Slot</h3>
                <form id="editForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                        <input type="date" name="date" required
                               min="{{ date('Y-m-d') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Time</label>
                        <input type="time" name="time" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Capacity</label>
                        <input type="number" name="capacity" required min="1"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p id="capacityHint" class="mt-1 text-sm text-gray-500 hidden">
                            Cannot reduce capacity below current bookings
                        </p>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_available" id="edit_is_available"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <label for="edit_is_available" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                            Available
                        </label>
                    </div>
                </form>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 flex justify-end gap-2">
                <button onclick="closeEditModal()" 
                        class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900">
                    Cancel
                </button>
                <button onclick="saveEdit()" 
                        id="saveEditButton"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <div class="flex items-center gap-2">
                        <svg id="editSpinner" class="animate-spin h-4 w-4 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="editButtonText">Save Changes</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full dark:bg-gray-800">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Confirm Delete</h3>
                <p class="text-gray-700 dark:text-gray-300" id="deleteModalMessage">
                    Are you sure you want to delete this time slot?
                </p>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 flex justify-end gap-2">
                <button onclick="closeDeleteModal()" 
                        class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900">
                    Cancel
                </button>
                <button onclick="confirmDelete()" 
                        id="confirmDeleteButton"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-2">
                    <svg id="deleteSpinner" class="animate-spin h-4 w-4 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="deleteButtonText">Delete</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentTimeSlotId = null;

function openCreateModal() {
    currentTimeSlotId = null;
    document.getElementById('modalTitle').textContent = 'Add Time Slot';
    document.getElementById('timeSlotForm').reset();
    document.getElementById('timeSlotModal').classList.remove('hidden');
}

function openEditModal(id) {
    currentTimeSlotId = id;
    fetch(`{{ route('admin.consulting.timeslots.edit', ['timeSlot' => ':id']) }}`.replace(':id', id))
        .then(response => response.json())
        .then(data => {
            const form = document.getElementById('editForm');
            form.date.value = data.date;
            form.time.value = data.time;
            form.capacity.value = data.capacity;
            form.is_available.checked = data.is_available;
            
            // Handle fields based on booking status
            if (data.has_bookings) {
                form.date.disabled = true;
                form.time.disabled = true;
                form.capacity.min = data.capacity; // Can't reduce below current capacity
                document.getElementById('capacityHint').classList.remove('hidden');
            } else {
                form.date.disabled = false;
                form.time.disabled = false;
                form.capacity.min = 1;
                document.getElementById('capacityHint').classList.add('hidden');
                // Validate initial date/time
                validateTimeInput(form.date, form.time);
            }
            
            document.getElementById('editModal').classList.remove('hidden');
        })
        .catch(error => {
            showNotification('Error', 'Failed to load time slot data', 'error');
        });
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    currentTimeSlotId = null;
}

function saveEdit() {
    const editButton = document.getElementById('saveEditButton');
    const editSpinner = document.getElementById('editSpinner');
    const editButtonText = document.getElementById('editButtonText');

    // Validate date and time
    const form = document.getElementById('editForm');
    const selectedDate = form.date.value;
    const selectedTime = form.time.value;
    const selectedDateTime = new Date(selectedDate + 'T' + selectedTime);
    const now = new Date();

    if (selectedDateTime < now) {
        showNotification('Error', 'Cannot select a past date and time', 'error');
        return;
    }

    // Disable button and show loading state
    editButton.disabled = true;
    editSpinner.classList.remove('hidden');
    editButtonText.textContent = 'Saving...';

    const formData = new FormData(form);
    const data = {
        date: formData.get('date'),
        time: formData.get('time'),
        capacity: parseInt(formData.get('capacity')),
        is_available: formData.get('is_available') === 'on'
    };

    fetch(`/admin/consulting/timeslots/${currentTimeSlotId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Success', data.message);
            closeEditModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        showNotification('Error', error.message, 'error');
        // Reset button state on error
        editButton.disabled = false;
        editSpinner.classList.add('hidden');
        editButtonText.textContent = 'Save Changes';
    });
}

function closeModal() {
    document.getElementById('timeSlotModal').classList.add('hidden');
}

function saveTimeSlot() {
    const saveButton = document.getElementById('saveTimeSlotButton');
    const saveSpinner = document.getElementById('saveTimeSlotSpinner');
    const saveButtonText = document.getElementById('saveTimeSlotButtonText');

    // Disable button and show loading state
    saveButton.disabled = true;
    saveSpinner.classList.remove('hidden');
    saveButtonText.textContent = 'Saving...';

    const form = document.getElementById('timeSlotForm');
    const formData = new FormData(form);
    const data = {
        date: formData.get('date'),
        time: formData.get('time'),
        capacity: parseInt(formData.get('capacity')),
        is_available: formData.get('is_available') === 'on'
    };
    
    const url = currentTimeSlotId 
        ? `/admin/consulting/timeslots/${currentTimeSlotId}`
        : '/admin/consulting/timeslots';
        
    const method = currentTimeSlotId ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Success', data.message);
            closeModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        showNotification('Error', error.message, 'error');
        // Reset button state on error
        saveButton.disabled = false;
        saveSpinner.classList.add('hidden');
        saveButtonText.textContent = 'Save';
    });
}

function openBulkCreateModal() {
    document.getElementById('bulkCreateModal').classList.remove('hidden');
}

function closeBulkCreateModal() {
    document.getElementById('bulkCreateModal').classList.add('hidden');
}

function addTimeInput(button) {
    const container = button.closest('.space-y-2');
    const newInput = document.createElement('div');
    newInput.className = 'flex gap-2';
    newInput.innerHTML = `
        <input type="time" name="times[]" required
               class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        <button type="button" onclick="removeTimeInput(this)"
                class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">-</button>
    `;
    container.appendChild(newInput);
}

function removeTimeInput(button) {
    button.closest('.flex').remove();
}

function saveBulkCreate() {
    const bulkCreateButton = document.getElementById('bulkCreateButton');
    const bulkCreateSpinner = document.getElementById('bulkCreateSpinner');
    const bulkCreateButtonText = document.getElementById('bulkCreateButtonText');

    // Disable button and show loading state
    bulkCreateButton.disabled = true;
    bulkCreateSpinner.classList.remove('hidden');
    bulkCreateButtonText.textContent = 'Creating...';

    const form = document.getElementById('bulkCreateForm');
    const formData = new FormData(form);

    // Convert form data to proper format
    const data = {
        start_date: formData.get('start_date'),
        end_date: formData.get('end_date'),
        days: Array.from(formData.getAll('days[]')).map(Number),
        times: Array.from(formData.getAll('times[]')),
        capacity: parseInt(formData.get('capacity'))
    };
    
    fetch('/admin/consulting/timeslots/bulk-create', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Success', data.message);
            closeBulkCreateModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        showNotification('Error', error.message, 'error');
        // Reset button state on error
        bulkCreateButton.disabled = false;
        bulkCreateSpinner.classList.add('hidden');
        bulkCreateButtonText.textContent = 'Create';
    });
}

function openDeleteModal(id) {
    currentTimeSlotId = id;
    document.getElementById('deleteModalMessage').textContent = 'Are you sure you want to delete this time slot?';
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    currentTimeSlotId = null;
}

function confirmDelete() {
    const deleteButton = document.getElementById('confirmDeleteButton');
    const deleteSpinner = document.getElementById('deleteSpinner');
    const deleteButtonText = document.getElementById('deleteButtonText');

    // Disable button and show loading state
    deleteButton.disabled = true;
    deleteSpinner.classList.remove('hidden');
    deleteButtonText.textContent = 'Deleting...';

    if (currentTimeSlotId) {
        // Single delete
        fetch(`/admin/consulting/timeslots/${currentTimeSlotId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Success', data.message);
                setTimeout(() => window.location.reload(), 1000);
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            showNotification('Error', error.message, 'error');
            // Reset button state on error
            deleteButton.disabled = false;
            deleteSpinner.classList.add('hidden');
            deleteButtonText.textContent = 'Delete';
        })
        .finally(() => {
            closeDeleteModal();
        });
    } else {
        // Bulk delete
        const selectedIds = Array.from(document.querySelectorAll('.slot-checkbox:checked'))
            .map(checkbox => checkbox.value);

        fetch(`{{ route("admin.consulting.timeslots.bulk-destroy") }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids: selectedIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Success', data.message);
                setTimeout(() => window.location.reload(), 1000);
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            showNotification('Error', error.message, 'error');
            // Reset button state on error
            deleteButton.disabled = false;
            deleteSpinner.classList.add('hidden');
            deleteButtonText.textContent = 'Delete';
        })
        .finally(() => {
            closeDeleteModal();
        });
    }
}

// Add these new functions for bulk delete
const selectAllCheckbox = document.getElementById('selectAll');
const slotCheckboxes = document.querySelectorAll('.slot-checkbox');
const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

selectAllCheckbox.addEventListener('change', function() {
    slotCheckboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkDeleteButton();
});

slotCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkDeleteButton);
});

function updateBulkDeleteButton() {
    const selectedCount = document.querySelectorAll('.slot-checkbox:checked').length;
    bulkDeleteBtn.disabled = selectedCount === 0;
    bulkDeleteBtn.textContent = selectedCount > 0 ? `Delete Selected (${selectedCount})` : 'Delete Selected';
}

function openBulkDeleteModal() {
    const selectedCount = document.querySelectorAll('.slot-checkbox:checked').length;
    if (selectedCount === 0) return; // Don't open modal if no selections
    document.getElementById('deleteModalMessage').textContent = 
        `Are you sure you want to delete ${selectedCount} selected time slots?`;
    document.getElementById('deleteModal').classList.remove('hidden');
    currentTimeSlotId = null; // Set to null to indicate bulk delete
}

function validateTimeInput(dateInput, timeInput) {
    const selectedDate = dateInput.value;
    const selectedTime = timeInput.value;
    
    if (selectedDate === new Date().toISOString().split('T')[0]) {
        const now = new Date();
        const selectedDateTime = new Date(selectedDate + 'T' + selectedTime);
        
        if (selectedDateTime < now) {
            timeInput.setCustomValidity('Cannot select a past time for today');
        } else {
            timeInput.setCustomValidity('');
        }
    } else {
        timeInput.setCustomValidity('');
    }
}

// Add event listeners to date and time inputs in single create form
document.querySelector('#timeSlotForm [name="date"]').addEventListener('change', function() {
    validateTimeInput(this, document.querySelector('#timeSlotForm [name="time"]'));
});

document.querySelector('#timeSlotForm [name="time"]').addEventListener('change', function() {
    validateTimeInput(document.querySelector('#timeSlotForm [name="date"]'), this);
});

// Add event listeners to bulk create form
document.querySelector('#bulkCreateForm [name="start_date"]').addEventListener('change', function() {
    const timeInputs = document.querySelectorAll('#bulkCreateForm [name="times[]"]');
    timeInputs.forEach(timeInput => {
        validateTimeInput(this, timeInput);
    });
});

document.querySelectorAll('#bulkCreateForm [name="times[]"]').forEach(timeInput => {
    timeInput.addEventListener('change', function() {
        validateTimeInput(document.querySelector('#bulkCreateForm [name="start_date"]'), this);
    });
});

// Add validation for edit form date/time inputs
document.querySelector('#editForm [name="date"]').addEventListener('change', function() {
    validateTimeInput(this, document.querySelector('#editForm [name="time"]'));
});

document.querySelector('#editForm [name="time"]').addEventListener('change', function() {
    validateTimeInput(document.querySelector('#editForm [name="date"]'), this);
});
</script>
@endpush
@endsection 