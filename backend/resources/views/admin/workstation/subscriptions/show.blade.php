@extends('admin.layouts.app')

@section('title', 'View Subscription')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Subscription Details
        </h2>
        <a href="{{ route('admin.workstation.subscriptions.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Back to Subscriptions
        </a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Subscription Details -->
                <div class="col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="flex justify-between items-start mb-6">
                                <h3 class="text-lg font-semibold">Subscription Information</h3>
                                <div class="relative">
                                    <button type="button" 
                                        onclick="showStatusUpdateModal()"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Update Status
                                    </button>
                                </div>
                            </div>

                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Tracking Code</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $subscription->tracking_code }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $subscription->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $subscription->status === 'expired' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $subscription->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}
                                            {{ $subscription->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                            {{ ucfirst($subscription->status) }}
                                        </span>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $subscription->start_date->format('M d, Y') }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">End Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $subscription->end_date->format('M d, Y') }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Payment Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($subscription->payment_type) }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                                    <dd class="mt-1 text-sm text-gray-900">₦{{ number_format($subscription->total_amount, 2) }}</dd>
                                </div>

                                @if($subscription->cancelled_at)
                                    <div class="col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Cancellation Details</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            Cancelled on {{ $subscription->cancelled_at->format('M d, Y H:i:s') }}
                                            @if($subscription->cancellation_reason)
                                                <br>
                                                Reason: {{ $subscription->cancellation_reason }}
                                            @endif
                                            @if($subscription->refund_amount)
                                                <br>
                                                Refund Amount: ₦{{ number_format($subscription->refund_amount, 2) }}
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- User and Plan Information -->
                <div class="space-y-6">
                    <!-- User Info -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">User Information</h3>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $subscription->user->full_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $subscription->user->email }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Plan Info -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">Plan Information</h3>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Plan Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $subscription->plan->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Duration</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $subscription->plan->duration_days }} days</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Price</dt>
                                    <dd class="mt-1 text-sm text-gray-900">₦{{ number_format($subscription->plan->price, 2) }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments Section -->
            @if($subscription->payments->isNotEmpty())
                <div class="mt-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">Payment History</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            @if($subscription->payment_type === 'installment')
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Installment</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($subscription->payments as $payment)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    ₦{{ number_format($payment->amount, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ ucfirst($payment->type) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $payment->due_date->format('M d, Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                        {{ $payment->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                                        {{ $payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                        {{ $payment->status === 'overdue' ? 'bg-red-100 text-red-800' : '' }}">
                                                        {{ ucfirst($payment->status) }}
                                                    </span>
                                                </td>
                                                @if($subscription->payment_type === 'installment')
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $payment->installment_number }} of {{ $subscription->plan->installment_months }}
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusUpdateModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity hidden">
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-yellow-100">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-base font-semibold leading-6 text-gray-900">Update Subscription Status</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Please select the new status for this subscription:</p>
                                <select id="statusSelect" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="active" {{ $subscription->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="expired" {{ $subscription->status === 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="cancelled" {{ $subscription->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="pending" {{ $subscription->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button type="button" 
                            onclick="updateStatus()"
                            id="confirmButton"
                            class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">
                            <span id="confirmButtonLoader" class="hidden">
                                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span id="confirmButtonText">Update Status</span>
                        </button>
                        <button type="button" 
                            onclick="hideStatusUpdateModal()"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const modal = document.getElementById('statusUpdateModal');
        const confirmButton = document.getElementById('confirmButton');
        const confirmButtonLoader = document.getElementById('confirmButtonLoader');
        const confirmButtonText = document.getElementById('confirmButtonText');

        function showStatusUpdateModal() {
            modal.classList.remove('hidden');
        }

        function hideStatusUpdateModal() {
            modal.classList.add('hidden');
        }

        async function updateStatus() {
            const status = document.getElementById('statusSelect').value;
            const currentStatus = '{{ $subscription->status }}';

            if (status === currentStatus) {
                hideStatusUpdateModal();
                return;
            }

            try {
                // Show loading state
                confirmButton.disabled = true;
                confirmButtonLoader.classList.remove('hidden');
                confirmButtonText.classList.add('hidden');

                const response = await fetch('{{ route("admin.workstation.subscriptions.update-status", $subscription) }}', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status })
                });

                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || 'Failed to update status');
                }

                // Show success message
                await Swal.fire({
                    title: 'Success!',
                    text: 'Subscription status updated successfully',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000,
                    timerProgressBar: true
                });

                // Reload the page
                window.location.reload();
            } catch (error) {
                console.error('Error:', error);
                
                // Show error message
                await Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Failed to update status',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } finally {
                // Reset loading state
                confirmButton.disabled = false;
                confirmButtonLoader.classList.add('hidden');
                confirmButtonText.classList.remove('hidden');
                hideStatusUpdateModal();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target === modal) {
                hideStatusUpdateModal();
            }
        }
    </script>
    @endpush
@endsection 