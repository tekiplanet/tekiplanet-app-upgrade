@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Product Request Details
        </h2>
        <a href="{{ route('admin.product-requests.index') }}" 
           class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            Back to List
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Request Information -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold mb-4">Request Information</h3>
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-gray-500">Status</label>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 text-sm rounded-full 
                            {{ $productRequest->status === 'completed' ? 'bg-green-100 text-green-800' : 
                               ($productRequest->status === 'processing' ? 'bg-blue-100 text-blue-800' : 
                               ($productRequest->status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                'bg-yellow-100 text-yellow-800')) }}">
                            {{ ucfirst($productRequest->status) }}
                        </span>
                        <button onclick="openStatusModal()" 
                                class="text-sm text-blue-600 hover:text-blue-800">
                            Update
                        </button>
                    </div>
                </div>

                <div>
                    <label class="text-sm text-gray-500">Product Name</label>
                    <p class="font-medium">{{ $productRequest->product_name }}</p>
                </div>

                <div>
                    <label class="text-sm text-gray-500">Price Range</label>
                    <p class="font-medium">₦{{ number_format($productRequest->min_price) }} - ₦{{ number_format($productRequest->max_price) }}</p>
                </div>

                <div>
                    <label class="text-sm text-gray-500">Quantity Needed</label>
                    <p class="font-medium">{{ $productRequest->quantity_needed }} units</p>
                </div>

                <div>
                    <label class="text-sm text-gray-500">Deadline</label>
                    <p class="font-medium">{{ $productRequest->deadline->format('M d, Y') }}</p>
                </div>

                <div>
                    <label class="text-sm text-gray-500">Additional Details</label>
                    <p class="mt-1 whitespace-pre-wrap text-gray-700 dark:text-gray-300">
                        {{ $productRequest->additional_details ?? 'No additional details provided.' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Customer Information & Admin Notes -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
                <h3 class="text-lg font-semibold mb-4">Customer Information</h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm text-gray-500">Name</label>
                        <p class="font-medium">{{ $productRequest->user->first_name }} {{ $productRequest->user->last_name }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Email</label>
                        <p class="font-medium">{{ $productRequest->user->email }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Phone</label>
                        <p class="font-medium">{{ $productRequest->user->phone ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Admin Notes</h3>
                    <button onclick="openNoteModal()" 
                            class="text-sm text-blue-600 hover:text-blue-800">
                        Update Note
                    </button>
                </div>
                <div class="whitespace-pre-wrap">
                    {{ $productRequest->admin_response ?? 'No notes added yet.' }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-medium text-gray-900">Update Status</h3>
                <div class="mt-4">
                    <select id="statusSelect" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        onclick="updateStatus()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Update
                </button>
                <button type="button" 
                        onclick="closeStatusModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Note Update Modal -->
<div id="noteModal" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-medium text-gray-900">Update Admin Note</h3>
                <div class="mt-4">
                    <textarea id="noteText" 
                              rows="4" 
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Enter admin note...">{{ $productRequest->admin_response }}</textarea>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        onclick="updateNote()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Save Note
                </button>
                <button type="button" 
                        onclick="closeNoteModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openStatusModal() {
    document.getElementById('statusModal').classList.remove('hidden');
    document.getElementById('statusSelect').value = '{{ $productRequest->status }}';
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

function openNoteModal() {
    document.getElementById('noteModal').classList.remove('hidden');
}

function closeNoteModal() {
    document.getElementById('noteModal').classList.add('hidden');
}

async function updateStatus() {
    const status = document.getElementById('statusSelect').value;
    
    try {
        const response = await fetch('{{ route("admin.product-requests.update-status", $productRequest) }}', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status })
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Success', 'Status updated successfully');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        showNotification('Error', error.message, 'error');
    }

    closeStatusModal();
}

async function updateNote() {
    const note = document.getElementById('noteText').value;
    
    try {
        const response = await fetch('{{ route("admin.product-requests.update-note", $productRequest) }}', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ admin_response: note })
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Success', 'Note updated successfully');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        showNotification('Error', error.message, 'error');
    }

    closeNoteModal();
}
</script>
@endpush

@endsection 