@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            {{ $business->business_name }} - Customers
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
                       placeholder="Search customers..."
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
        </div>
    </div>

    <!-- Customers List -->
    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800">
        <!-- Desktop Table (hidden on mobile) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left bg-gray-50 dark:bg-gray-700">
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Phone
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($customers as $customer)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $customer->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $customer->email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $customer->phone }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full {{ $customer->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($customer->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button onclick="showCustomerDetails('{{ $customer->id }}')" 
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 inline-flex items-center">
                                <span class="inline-flex items-center">
                                    <svg id="customerSpinner-{{ $customer->id }}" class="w-4 h-4 mr-2 animate-spin hidden" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span id="customerButtonText-{{ $customer->id }}">View Details</span>
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
            @foreach($customers as $customer)
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $customer->name }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $customer->email }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full {{ $customer->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($customer->status) }}
                    </span>
                </div>
                <div class="flex justify-end">
                    <button onclick="showCustomerDetails('{{ $customer->id }}')" 
                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 inline-flex items-center">
                        <span class="inline-flex items-center">
                            <svg id="customerSpinnerMobile-{{ $customer->id }}" class="w-4 h-4 mr-2 animate-spin hidden" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="customerButtonTextMobile-{{ $customer->id }}">View Details</span>
                        </span>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <div class="px-6 py-4">
            {{ $customers->links() }}
        </div>
    </div>
</div>

<!-- Customer Details Modal -->
<div id="customerDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-[60]">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl dark:bg-gray-800 w-full max-w-2xl max-h-[90vh] flex flex-col">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                    Customer Details
                </h3>
            </div>
            <div class="p-6 overflow-y-auto">
                <div id="customerDetailsContent" class="space-y-4">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 rounded-b-lg flex justify-end border-t border-gray-200 dark:border-gray-700">
                <button onclick="closeCustomerModal()" 
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

async function showCustomerDetails(customerId) {
    // Show loading state
    const spinner = document.getElementById(`customerSpinner-${customerId}`);
    const spinnerMobile = document.getElementById(`customerSpinnerMobile-${customerId}`);
    const buttonText = document.getElementById(`customerButtonText-${customerId}`);
    const buttonTextMobile = document.getElementById(`customerButtonTextMobile-${customerId}`);
    
    spinner.classList.remove('hidden');
    spinnerMobile.classList.remove('hidden');
    buttonText.textContent = 'Loading...';
    buttonTextMobile.textContent = 'Loading...';

    try {
        const response = await fetch(`{{ route('admin.businesses.customers.show', ['business' => $business->id, 'customer' => '__ID__']) }}`.replace('__ID__', customerId));
        const data = await response.json();
        
        if (response.ok) {
            const content = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Basic Information -->
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-400">Name</label>
                        <p class="text-gray-800 dark:text-gray-200">${data.name}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-400">Email</label>
                        <p class="text-gray-800 dark:text-gray-200">${data.email}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-400">Phone</label>
                        <p class="text-gray-800 dark:text-gray-200">${data.phone || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-400">Status</label>
                        <p class="mt-1">
                            <span class="px-2 py-1 text-sm rounded-full ${
                                data.status === 'active' 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-red-100 text-red-800'
                            }">
                                ${data.status.charAt(0).toUpperCase() + data.status.slice(1)}
                            </span>
                        </p>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="col-span-1 md:col-span-2">
                        <label class="text-sm text-gray-600 dark:text-gray-400">Address</label>
                        <p class="text-gray-800 dark:text-gray-200">${data.address || 'N/A'}</p>
                    </div>
                    
                    <!-- Additional Information -->
                    <div class="col-span-1 md:col-span-2">
                        <label class="text-sm text-gray-600 dark:text-gray-400">Notes</label>
                        <p class="text-gray-800 dark:text-gray-200">${data.notes || 'N/A'}</p>
                    </div>
                    
                    <!-- Customer Since -->
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-400">Customer Since</label>
                        <p class="text-gray-800 dark:text-gray-200">${new Date(data.created_at).toLocaleDateString()}</p>
                    </div>
                    
                    <!-- Last Updated -->
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-400">Last Updated</label>
                        <p class="text-gray-800 dark:text-gray-200">${new Date(data.updated_at).toLocaleDateString()}</p>
                    </div>
                    
                    <!-- Additional Customer Details -->
                    <div class="col-span-1 md:col-span-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Additional Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Total Orders</label>
                                <p class="text-gray-800 dark:text-gray-200">${data.total_orders || '0'}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Total Spent</label>
                                <p class="text-gray-800 dark:text-gray-200">
                                    ${formatCurrency(data.total_spent || 0, data.currency)}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('customerDetailsContent').innerHTML = content;
            document.getElementById('customerDetailsModal').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error', 'Failed to load customer details', 'error');
    } finally {
        // Hide loading state
        spinner.classList.add('hidden');
        spinnerMobile.classList.add('hidden');
        buttonText.textContent = 'View Details';
        buttonTextMobile.textContent = 'View Details';
    }
}

function closeCustomerModal() {
    document.getElementById('customerDetailsModal').classList.add('hidden');
}

// Search functionality
const searchInput = document.getElementById('searchInput');
let searchTimeout;

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const searchQuery = this.value;
        window.location.href = `{{ route('admin.businesses.customers.index', $business) }}?search=${searchQuery}`;
    }, 500);
});
</script>
@endpush
@endsection 