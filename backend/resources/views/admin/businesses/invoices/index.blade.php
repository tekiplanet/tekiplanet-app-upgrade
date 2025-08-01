@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            {{ $business->business_name }} - Invoices
        </h2>
        <a href="{{ route('admin.businesses.show', $business) }}" 
           class="inline-flex items-center text-sm text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Business
        </a>
    </div>

    <!-- Filters and Search -->
    <div class="mb-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" 
                       id="searchInput"
                       placeholder="Search invoices..."
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
        </div>
    </div>

    <!-- Invoices List -->
    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800">
        <!-- Desktop Table (hidden on mobile) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left bg-gray-50 dark:bg-gray-700">
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Invoice #
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Customer
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Amount
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($invoices as $invoice)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $invoice->invoice_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $invoice->customer->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 
                                   ($invoice->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button onclick="showInvoiceDetails('{{ $invoice->id }}')" 
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 inline-flex items-center">
                                <span class="inline-flex items-center">
                                    <svg id="invoiceSpinner-{{ $invoice->id }}" class="w-4 h-4 mr-2 animate-spin hidden" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span id="invoiceButtonText-{{ $invoice->id }}">View Details</span>
                                </span>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile List View -->
        <div class="md:hidden">
            @foreach($invoices as $invoice)
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Invoice #{{ $invoice->invoice_number }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $invoice->customer->name }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full 
                        {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 
                           ($invoice->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}
                    </span>
                    <button onclick="showInvoiceDetails('{{ $invoice->id }}')" 
                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 inline-flex items-center">
                        <span class="inline-flex items-center">
                            <svg id="invoiceSpinnerMobile-{{ $invoice->id }}" class="w-4 h-4 mr-2 animate-spin hidden" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="invoiceButtonTextMobile-{{ $invoice->id }}">View Details</span>
                        </span>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <div class="px-6 py-4">
            {{ $invoices->links() }}
        </div>
    </div>
</div>

<!-- Invoice Details Modal -->
<div id="invoiceDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-[60]">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl dark:bg-gray-800 w-full max-w-2xl max-h-[90vh] flex flex-col">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                    Invoice Details
                </h3>
            </div>
            <div class="p-6 overflow-y-auto">
                <div id="invoiceDetailsContent" class="space-y-4">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 rounded-b-lg flex justify-end border-t border-gray-200 dark:border-gray-700">
                <button onclick="closeInvoiceModal()" 
                        class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-700">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function formatCurrency(amount, currency) {
    const formatter = new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency: currency || 'NGN',
        minimumFractionDigits: 2
    });
    return formatter.format(amount);
}

async function showInvoiceDetails(invoiceId) {
    // Show loading state
    const spinner = document.getElementById(`invoiceSpinner-${invoiceId}`);
    const spinnerMobile = document.getElementById(`invoiceSpinnerMobile-${invoiceId}`);
    const buttonText = document.getElementById(`invoiceButtonText-${invoiceId}`);
    const buttonTextMobile = document.getElementById(`invoiceButtonTextMobile-${invoiceId}`);
    
    spinner.classList.remove('hidden');
    spinnerMobile.classList.remove('hidden');
    buttonText.textContent = 'Loading...';
    buttonTextMobile.textContent = 'Loading...';

    try {
        const response = await fetch(`{{ route('admin.businesses.invoices.show', ['business' => $business->id, 'invoice' => '__ID__']) }}`.replace('__ID__', invoiceId));
        const data = await response.json();
        
        if (response.ok) {
            const content = `
                <div class="space-y-6">
                    <!-- Invoice Header -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Invoice Number</label>
                            <p class="text-gray-800 dark:text-gray-200">${data.invoice_number}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Status</label>
                            <p class="mt-1">
                                <span class="px-2 py-1 text-sm rounded-full ${
                                    data.status === 'paid' ? 'bg-green-100 text-green-800' : 
                                    (data.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')
                                }">
                                    ${data.status.charAt(0).toUpperCase() + data.status.slice(1)}
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Customer Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Name</label>
                                <p class="text-gray-800 dark:text-gray-200">${data.customer.name}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Email</label>
                                <p class="text-gray-800 dark:text-gray-200">${data.customer.email}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Details -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Invoice Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Amount</label>
                                <p class="text-gray-800 dark:text-gray-200">${formatCurrency(data.amount, data.currency)}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Due Date</label>
                                <p class="text-gray-800 dark:text-gray-200">${data.due_date ? new Date(data.due_date).toLocaleDateString() : 'N/A'}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Invoice Items</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left bg-gray-50 dark:bg-gray-700">
                                        <th class="px-4 py-2 text-xs font-medium text-gray-500 dark:text-gray-300">Description</th>
                                        <th class="px-4 py-2 text-xs font-medium text-gray-500 dark:text-gray-300">Quantity</th>
                                        <th class="px-4 py-2 text-xs font-medium text-gray-500 dark:text-gray-300">Unit Price</th>
                                        <th class="px-4 py-2 text-xs font-medium text-gray-500 dark:text-gray-300">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    ${data.items.map(item => `
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-200">${item.description}</td>
                                            <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-200">${item.quantity}</td>
                                            <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-200">${formatCurrency(item.unit_price, data.currency)}</td>
                                            <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-200">${formatCurrency(item.amount, data.currency)}</td>
                                        </tr>
                                    `).join('')}
                                    <tr class="bg-gray-50 dark:bg-gray-700">
                                        <td colspan="3" class="px-4 py-2 text-sm font-medium text-right text-gray-700 dark:text-gray-300">Total:</td>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-800 dark:text-gray-200">${formatCurrency(data.amount, data.currency)}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Payment Information</h4>
                        <div class="space-y-4">
                            ${data.payments.map(payment => `
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="text-sm text-gray-600 dark:text-gray-400">Amount</label>
                                            <p class="text-gray-800 dark:text-gray-200">${formatCurrency(payment.amount, payment.currency)}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-600 dark:text-gray-400">Payment Date</label>
                                            <p class="text-gray-800 dark:text-gray-200">${payment.payment_date ? new Date(payment.payment_date).toLocaleDateString() : 'N/A'}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-600 dark:text-gray-400">Notes</label>
                                            <p class="text-gray-800 dark:text-gray-200">${payment.notes || 'N/A'}</p>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <label class="text-sm text-gray-600 dark:text-gray-400">Notes</label>
                        <p class="text-gray-800 dark:text-gray-200">${data.notes || 'N/A'}</p>
                    </div>
                </div>
            `;
            
            document.getElementById('invoiceDetailsContent').innerHTML = content;
            document.getElementById('invoiceDetailsModal').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error', 'Failed to load invoice details', 'error');
    } finally {
        // Hide loading state
        spinner.classList.add('hidden');
        spinnerMobile.classList.add('hidden');
        buttonText.textContent = 'View Details';
        buttonTextMobile.textContent = 'View Details';
    }
}

function closeInvoiceModal() {
    document.getElementById('invoiceDetailsModal').classList.add('hidden');
}

// Search functionality
const searchInput = document.getElementById('searchInput');
let searchTimeout;

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const searchQuery = this.value;
        window.location.href = `{{ route('admin.businesses.invoices.index', $business) }}?search=${searchQuery}`;
    }, 500);
});
</script>
@endpush
@endsection 