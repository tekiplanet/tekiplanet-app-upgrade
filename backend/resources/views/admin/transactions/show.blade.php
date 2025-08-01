@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Transaction Details
        </h2>
        <a href="{{ route('admin.transactions.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Transactions
        </a>
    </div>

    <!-- Transaction Information -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Basic Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Reference Number</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $transaction->reference_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Amount</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ number_format($transaction->amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                            <dd class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $transaction->type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                            <dd class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                       'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- User Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">User Information</h3>
                    @if($transaction->user)
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $transaction->user->full_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $transaction->user->email }}</dd>
                            </div>
                        </dl>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">User not found</p>
                    @endif
                </div>
            </div>

            <!-- Additional Details -->
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Additional Details</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $transaction->description }}</dd>
                    </div>
                    @if($transaction->notes)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes History</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                @if(is_array($transaction->notes))
                                    <div class="space-y-3">
                                        @foreach($transaction->notes as $note)
                                            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                                @if(isset($note['status_update']))
                                                    <div class="mb-2">
                                                        <span class="font-medium">Status Update ({{ $note['status_update']['date'] }})</span>
                                                        <div class="ml-4">
                                                            <div>From: <span class="font-medium">{{ ucfirst($note['status_update']['from']) }}</span></div>
                                                            <div>To: <span class="font-medium">{{ ucfirst($note['status_update']['to']) }}</span></div>
                                                            @if(isset($note['status_update']['note']))
                                                                <div class="mt-1">Note: {{ $note['status_update']['note'] }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                @if(isset($note['wallet_update']))
                                                    <div class="text-green-600 dark:text-green-400">
                                                        {{ $note['wallet_update'] }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                        {{ $transaction->notes }}
                                    </div>
                                @endif
                            </dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $transaction->created_at->format('M d, Y H:i:s') }}</dd>
                    </div>
                    @if($transaction->updated_at->ne($transaction->created_at))
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $transaction->updated_at->format('M d, Y H:i:s') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Receipt Actions -->
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Receipt Actions</h3>
                <div class="flex space-x-4">
                    <button onclick="downloadReceipt()" 
                            id="downloadReceiptBtn"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Download Receipt</span>
                    </button>

                    <button onclick="sendReceipt()" 
                            id="sendReceiptBtn"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>Send Receipt to User</span>
                    </button>
                </div>
            </div>

            <!-- Update Status Form -->
            @if(!in_array($transaction->status, ['completed', 'cancelled', 'failed']))
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Update Status</h3>
                    <form id="updateStatusForm" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                    <option value="pending" {{ $transaction->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="completed" {{ $transaction->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="failed" {{ $transaction->status === 'failed' ? 'selected' : '' }}>Failed</option>
                                    <option value="cancelled" {{ $transaction->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Update Status
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <p class="text-gray-700 dark:text-gray-300">
                            This transaction is {{ $transaction->status }} and cannot be updated.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('updateStatusForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    
    try {
        submitButton.disabled = true;
        submitButton.textContent = 'Updating...';
        
        const response = await fetch('{{ route('admin.transactions.update-status', $transaction) }}', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                status: form.status.value,
                notes: form.notes.value
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showNotification('Success', data.message);
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(data.message || 'Failed to update status');
        }
    } catch (error) {
        showNotification('Error', error.message, 'error');
    } finally {
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    }
});

async function downloadReceipt() {
    const button = document.getElementById('downloadReceiptBtn');
    const originalContent = button.innerHTML;
    
    try {
        button.disabled = true;
        button.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Downloading...
        `;

        const response = await fetch('{{ route('admin.transactions.download-receipt', $transaction) }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) throw new Error('Download failed');

        // Get the blob from the response
        const blob = await response.blob();
        
        // Create a link and trigger download
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = "transaction-{{ $transaction->reference_number }}.pdf";
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        
        showNotification('Success', 'Receipt downloaded successfully');
    } catch (error) {
        showNotification('Error', 'Failed to download receipt', 'error');
    } finally {
        button.disabled = false;
        button.innerHTML = originalContent;
    }
}

async function sendReceipt() {
    const button = document.getElementById('sendReceiptBtn');
    const originalContent = button.innerHTML;
    
    try {
        button.disabled = true;
        button.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Sending...
        `;

        const response = await fetch('{{ route('admin.transactions.send-receipt', $transaction) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showNotification('Success', data.message);
        } else {
            throw new Error(data.message || 'Failed to send receipt');
        }
    } catch (error) {
        showNotification('Error', error.message, 'error');
    } finally {
        button.disabled = false;
        button.innerHTML = originalContent;
    }
}
</script>
@endpush

@endsection 