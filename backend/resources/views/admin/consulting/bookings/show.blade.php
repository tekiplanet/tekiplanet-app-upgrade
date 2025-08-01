@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Booking Details
        </h2>
        <a href="{{ route('admin.consulting.bookings.index') }}" 
           class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            Back to List
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Booking Information -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold mb-4">Booking Information</h3>
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-gray-500">Status</label>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 text-sm rounded-full {{ $statusColors[$booking->status] }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                        <button onclick="openStatusModal()" 
                                class="text-sm text-blue-600 hover:text-blue-800">
                            Update
                        </button>
                        @if(in_array($booking->status, ['pending', 'confirmed']))
                            <button onclick="openReminderModal()" 
                                    class="text-sm text-blue-600 hover:text-blue-800">
                                Send Reminder
                            </button>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="text-sm text-gray-500">Date & Time</label>
                    <p class="font-medium">
                        {{ $booking->selected_date->format('M d, Y') }} at 
                        {{ $booking->selected_time->format('h:i A') }}
                    </p>
                </div>

                <div>
                    <label class="text-sm text-gray-500">Duration</label>
                    <p class="font-medium">{{ $booking->hours }} hours</p>
                </div>

                <div>
                    <label class="text-sm text-gray-500">Total Cost</label>
                    <p class="font-medium">₦{{ number_format($booking->total_cost, 2) }}</p>
                </div>

                <div>
                    <label class="text-sm text-gray-500">Payment Status</label>
                    <p class="font-medium">
                        <span class="px-2 py-1 text-sm rounded-full 
                            {{ $booking->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ ucfirst($booking->payment_status) }}
                        </span>
                    </p>
                </div>

                @if($booking->requirements)
                    <div>
                        <label class="text-sm text-gray-500">Requirements</label>
                        <p class="font-medium whitespace-pre-line">{{ $booking->requirements }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Client Information -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold mb-4">Client Information</h3>
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-gray-500">Name</label>
                    <p class="font-medium">{{ $booking->user->first_name }} {{ $booking->user->last_name }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Email</label>
                    <p class="font-medium">{{ $booking->user->email }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Phone</label>
                    <p class="font-medium">{{ $booking->user->phone ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Expert Information -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold mb-4">Expert Information</h3>
            @if($booking->expert)
                <div class="space-y-4">
                    <div>
                        <label class="text-sm text-gray-500">Name</label>
                        <p class="font-medium">
                            {{ $booking->expert->user->first_name }} {{ $booking->expert->user->last_name }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Email</label>
                        <p class="font-medium">{{ $booking->expert->user->email }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Expertise</label>
                        <p class="font-medium">{{ $booking->expert->expertise }}</p>
                    </div>
                    <div>
                        <button onclick="openExpertModal()" 
                                class="text-sm text-blue-600 hover:text-blue-800">
                            Change Expert
                        </button>
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <p class="text-gray-500 mb-4">No expert assigned</p>
                    <button onclick="openExpertModal()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Assign Expert
                    </button>
                </div>
            @endif
        </div>

        <!-- Review Information -->
        @if($booking->review)
            <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
                <h3 class="text-lg font-semibold mb-4">Client Review</h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm text-gray-500">Rating</label>
                        <div class="flex items-center gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-5 h-5 {{ $i <= $booking->review->rating ? 'text-yellow-400' : 'text-gray-300' }}" 
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Comment</label>
                        <p class="font-medium">{{ $booking->review->comment }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Update Booking Status</h3>
                <form id="statusForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="ongoing" {{ $booking->status === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div id="cancellationReasonField" class="hidden">
                        <label class="block text-sm font-medium text-gray-700">Cancellation Reason</label>
                        <textarea name="cancellation_reason" rows="3" 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                </form>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end gap-2">
                <button onclick="closeStatusModal()" 
                        class="px-4 py-2 text-gray-700 hover:text-gray-900">
                    Cancel
                </button>
                <button onclick="updateStatus()" 
                        id="updateStatusButton"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <div class="flex items-center gap-2">
                        <svg id="statusSpinner" class="animate-spin h-4 w-4 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="statusButtonText">Update</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Expert Assignment Modal -->
<div id="expertModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Assign Expert</h3>
                <form id="expertForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Select Expert</label>
                        <select name="expert_id" 
                                id="expert_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select an Expert</option>
                            @foreach($experts as $expert)
                                <option value="{{ $expert->id }}" 
                                    {{ $booking->assigned_expert_id === $expert->id ? 'selected' : '' }}>
                                    {{ $expert->user->first_name }} {{ $expert->user->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end gap-2">
                <button onclick="closeExpertModal()" 
                        class="px-4 py-2 text-gray-700 hover:text-gray-900">
                    Cancel
                </button>
                <button onclick="assignExpert()" 
                        id="assignExpertButton"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <div class="flex items-center gap-2">
                        <svg id="assignSpinner" class="animate-spin h-4 w-4 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="assignButtonText">Assign</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reminder Modal -->
<div id="reminderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Send Booking Reminder</h3>
                <form id="reminderForm" class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-4" id="timeUntilBooking"></p>
                        <label class="block text-sm font-medium text-gray-700">Additional Note (Optional)</label>
                        <textarea name="note" rows="3" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Add any additional information..."></textarea>
                    </div>
                </form>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end gap-2">
                <button onclick="closeReminderModal()" 
                        class="px-4 py-2 text-gray-700 hover:text-gray-900">
                    Cancel
                </button>
                <button onclick="sendReminder()" 
                        id="sendReminderButton"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <div class="flex items-center gap-2">
                        <svg id="reminderSpinner" class="animate-spin h-4 w-4 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="reminderButtonText">Send Reminder</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Status Modal Functions
function openStatusModal() {
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

function updateStatus() {
    const updateButton = document.getElementById('updateStatusButton');
    const statusSpinner = document.getElementById('statusSpinner');
    const statusButtonText = document.getElementById('statusButtonText');

    // Disable button and show loading state
    updateButton.disabled = true;
    statusSpinner.classList.remove('hidden');
    statusButtonText.textContent = 'Updating...';

    const form = document.getElementById('statusForm');
    const formData = new FormData(form);

    fetch(`{{ route('admin.consulting.bookings.update-status', $booking) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Success', 'Booking status updated successfully');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        showNotification('Error', error.message, 'error');
        // Reset button state on error
        updateButton.disabled = false;
        statusSpinner.classList.add('hidden');
        statusButtonText.textContent = 'Update';
    });
}

// Expert Modal Functions
function openExpertModal() {
    document.getElementById('expertModal').classList.remove('hidden');
}

function closeExpertModal() {
    document.getElementById('expertModal').classList.add('hidden');
}

function assignExpert() {
    const assignButton = document.getElementById('assignExpertButton');
    const assignSpinner = document.getElementById('assignSpinner');
    const assignButtonText = document.getElementById('assignButtonText');

    // Disable button and show loading state
    assignButton.disabled = true;
    assignSpinner.classList.remove('hidden');
    assignButtonText.textContent = 'Assigning...';

    const expertId = document.getElementById('expert_id').value;

    fetch(`{{ route('admin.consulting.bookings.assign-expert', $booking->id) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ expert_id: expertId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Success', data.message);
            closeExpertModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        showNotification('Error', error.message, 'error');
        // Reset button state on error
        assignButton.disabled = false;
        assignSpinner.classList.add('hidden');
        assignButtonText.textContent = 'Assign';
    });
}

// Show/hide cancellation reason field based on status
document.querySelector('select[name="status"]').addEventListener('change', function() {
    const cancellationField = document.getElementById('cancellationReasonField');
    cancellationField.classList.toggle('hidden', this.value !== 'cancelled');
});

function calculateTimeUntilBooking() {
    // Format date and time properly for JavaScript
    const dateStr = '{{ $booking->selected_date->format("Y-m-d") }}';
    const timeStr = '{{ $booking->selected_time->format("H:i:s") }}';
    const bookingDate = new Date(`${dateStr}T${timeStr}`);
    const now = new Date();
    const diff = bookingDate - now;
    
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    
    let timeText = '';
    if (days > 0) {
        timeText = `${days} days`;
    } else if (hours > 0) {
        timeText = `${hours} hours`;
    } else {
        timeText = `${minutes} minutes`;
    }
    
    // For debugging
    // console.log({
    //     dateStr,
    //     timeStr,
    //     bookingDate,
    //     now,
    //     diff,
    //     days,
    //     hours,
    //     minutes
    // });
    
    return timeText;
}

function openReminderModal() {
    const timeUntil = calculateTimeUntilBooking();
    document.getElementById('timeUntilBooking').textContent = 
        `This booking is scheduled for {{ $booking->selected_date->format('M d, Y') }} at {{ $booking->selected_time->format('h:i A') }} (in ${timeUntil})`;
    document.getElementById('reminderModal').classList.remove('hidden');
}

function closeReminderModal() {
    document.getElementById('reminderModal').classList.add('hidden');
}

function sendReminder() {
    const reminderButton = document.getElementById('sendReminderButton');
    const reminderSpinner = document.getElementById('reminderSpinner');
    const reminderButtonText = document.getElementById('reminderButtonText');

    // Disable button and show loading state
    reminderButton.disabled = true;
    reminderSpinner.classList.remove('hidden');
    reminderButtonText.textContent = 'Sending...';

    const form = document.getElementById('reminderForm');
    const formData = new FormData(form);

    fetch(`{{ route('admin.consulting.bookings.send-reminder', $booking->id) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            note: formData.get('note'),
            time_until: calculateTimeUntilBooking()
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Success', 'Reminder sent successfully');
            closeReminderModal();
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        showNotification('Error', error.message, 'error');
    })
    .finally(() => {
        // Reset button state
        reminderButton.disabled = false;
        reminderSpinner.classList.add('hidden');
        reminderButtonText.textContent = 'Send Reminder';
    });
}
</script>
@endpush
@endsection 