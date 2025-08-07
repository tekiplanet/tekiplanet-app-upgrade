@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Discount Slips Management
        </h2>
    </div>

    <!-- Search and Filter Section -->
    <div class="mb-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
        <form action="{{ route('admin.discount-slips.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                       placeholder="Search by discount code, service name, or user...">
            </div>
            <div class="flex gap-2">
                <select name="status" class="px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="used" {{ request('status') === 'used' ? 'selected' : '' }}>Used</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Search
                </button>
            </div>
        </form>
    </div>

    <!-- Discount Slips List -->
    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap">
                <thead>
                    <tr class="text-left font-semibold bg-gray-50 dark:bg-gray-700">
                        <th class="px-6 py-3">Discount Code</th>
                        <th class="px-6 py-3">User</th>
                        <th class="px-6 py-3">Service</th>
                        <th class="px-6 py-3">Discount %</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Expires</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($discountSlips as $slip)
                        <tr>
                            <td class="px-6 py-4 font-mono text-sm">
                                <span class="bg-gray-100 px-2 py-1 rounded">{{ $slip->discount_code }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <div class="font-semibold">{{ $slip->user->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $slip->user->email ?? 'N/A' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">{{ $slip->service_name }}</td>
                            <td class="px-6 py-4 font-semibold">{{ $slip->discount_percent }}%</td>
                            <td class="px-6 py-4">
                                @if($slip->is_used)
                                    <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Used</span>
                                @elseif($slip->expires_at < now())
                                    <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 rounded-full">Expired</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Active</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">
                                    <div>{{ $slip->expires_at->format('M j, Y') }}</div>
                                    <div class="text-gray-500">{{ $slip->expires_at->format('g:i A') }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.discount-slips.show', $slip) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        View
                                    </a>
                                    <a href="{{ route('admin.discount-slips.edit', $slip) }}" 
                                       class="text-green-600 hover:text-green-800 text-sm">
                                        Edit
                                    </a>
                                                                         <button type="button" 
                                             onclick="toggleUsedStatus('{{ $slip->id }}', '{{ $slip->is_used ? 'unused' : 'used' }}')" 
                                             class="text-orange-600 hover:text-orange-800 text-sm">
                                         {{ $slip->is_used ? 'Mark Unused' : 'Mark Used' }}
                                     </button>
                                     <button type="button" 
                                             onclick="deleteDiscountSlip('{{ $slip->id }}')" 
                                             class="text-red-600 hover:text-red-800 text-sm">
                                         Delete
                                     </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No discount slips found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($discountSlips->hasPages())
        <div class="mt-6">
            {{ $discountSlips->links() }}
        </div>
    @endif
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
