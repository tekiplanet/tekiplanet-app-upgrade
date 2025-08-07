@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Discount Slip Details
        </h2>
        <div class="flex gap-2">
            <a href="{{ route('admin.discount-slips.edit', $discountSlip) }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Edit
            </a>
            <a href="{{ route('admin.discount-slips.index') }}" 
               class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Discount Slip Details -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-200">Discount Slip Information</h3>
            
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-gray-600 dark:text-gray-400">Discount Code:</span>
                    <span class="font-mono text-lg bg-gray-100 px-3 py-1 rounded">{{ $discountSlip->discount_code }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-gray-600 dark:text-gray-400">Service:</span>
                    <span>{{ $discountSlip->service_name }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-gray-600 dark:text-gray-400">Discount Percentage:</span>
                    <span class="font-semibold text-green-600">{{ $discountSlip->discount_percent }}%</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-gray-600 dark:text-gray-400">Status:</span>
                    <span>
                        @if($discountSlip->is_used)
                            <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Used</span>
                        @elseif($discountSlip->expires_at < now())
                            <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 rounded-full">Expired</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Active</span>
                        @endif
                    </span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-gray-600 dark:text-gray-400">Created:</span>
                    <span>{{ $discountSlip->created_at->format('M j, Y g:i A') }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-gray-600 dark:text-gray-400">Expires:</span>
                    <span class="{{ $discountSlip->expires_at < now() ? 'text-red-600' : '' }}">
                        {{ $discountSlip->expires_at->format('M j, Y g:i A') }}
                    </span>
                </div>
                
                @if($discountSlip->is_used)
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-gray-600 dark:text-gray-400">Used At:</span>
                    <span>{{ $discountSlip->used_at->format('M j, Y g:i A') }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- User Information -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-200">User Information</h3>
            
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-gray-600 dark:text-gray-400">Name:</span>
                    <span>{{ $discountSlip->user->name ?? 'N/A' }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-gray-600 dark:text-gray-400">Email:</span>
                    <span>{{ $discountSlip->user->email ?? 'N/A' }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-gray-600 dark:text-gray-400">User ID:</span>
                    <span>{{ $discountSlip->user_id }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Information -->
    @if($discountSlip->userConversionTask)
    <div class="mt-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-200">Associated Task</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex justify-between items-center">
                <span class="font-semibold text-gray-600 dark:text-gray-400">Task:</span>
                <span>{{ $discountSlip->userConversionTask->task->title ?? 'N/A' }}</span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="font-semibold text-gray-600 dark:text-gray-400">Task Type:</span>
                <span>{{ $discountSlip->userConversionTask->task->type->name ?? 'N/A' }}</span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="font-semibold text-gray-600 dark:text-gray-400">Reward Type:</span>
                <span>{{ $discountSlip->userConversionTask->task->rewardType->name ?? 'N/A' }}</span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="font-semibold text-gray-600 dark:text-gray-400">Task Status:</span>
                <span>
                    @if($discountSlip->userConversionTask->claimed)
                        <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Claimed</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                    @endif
                </span>
            </div>
        </div>
    </div>
    @endif

    <!-- Terms and Conditions -->
    @if($discountSlip->terms_conditions)
    <div class="mt-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-200">Terms & Conditions</h3>
        <div class="prose max-w-none">
            {!! nl2br(e($discountSlip->terms_conditions)) !!}
        </div>
    </div>
    @endif

    <!-- Actions -->
    <div class="mt-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-200">Actions</h3>
        
                 <div class="flex flex-wrap gap-3">
             <button type="button" 
                     onclick="toggleUsedStatus('{{ $discountSlip->id }}', '{{ $discountSlip->is_used ? 'unused' : 'used' }}')" 
                     class="px-4 py-2 {{ $discountSlip->is_used ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-lg">
                 {{ $discountSlip->is_used ? 'Mark as Unused' : 'Mark as Used' }}
             </button>
             
             <button onclick="openExtendModal()" 
                     class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                 Extend Expiration
             </button>
             
             <button type="button" 
                     onclick="deleteDiscountSlip('{{ $discountSlip->id }}')" 
                     class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                 Delete
             </button>
         </div>
    </div>
</div>

<!-- Extend Expiration Modal -->
<div id="extendModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Extend Expiration</h3>
            <form action="{{ route('admin.discount-slips.extend-expiration', $discountSlip) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Extend by (days)</label>
                    <input type="number" name="days" min="1" max="365" value="7" 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Extend
                    </button>
                    <button type="button" onclick="closeExtendModal()" 
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Forms for Actions -->
<form id="toggleUsedForm" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
function openExtendModal() {
    document.getElementById('extendModal').classList.remove('hidden');
}

function closeExtendModal() {
    document.getElementById('extendModal').classList.add('hidden');
}

function toggleUsedStatus(slipId, action) {
    const status = action === 'used' ? 'mark as used' : 'mark as unused';
    
    Swal.fire({
        title: 'Confirm Action',
        text: `Are you sure you want to ${status} this discount slip?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, proceed!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('toggleUsedForm');
            form.action = `/admin/discount-slips/${slipId}/toggle-used`;
            form.submit();
        }
    });
}

function deleteDiscountSlip(slipId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this! This discount slip will be permanently deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('deleteForm');
            form.action = `/admin/discount-slips/${slipId}`;
            form.submit();
        }
    });
}
</script>
@endpush
@endsection
