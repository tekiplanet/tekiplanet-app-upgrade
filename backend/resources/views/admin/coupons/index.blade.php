@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Coupons
        </h2>
        <button onclick="openCreateModal()" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Create Coupon
        </button>
    </div>

    <!-- Search/Filter Section -->
    <div class="mb-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
        <form action="{{ route('admin.coupons.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                       placeholder="Search coupons...">
            </div>
            <div class="w-full md:w-48">
                <select name="status" 
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Search
            </button>
        </form>
    </div>

    <!-- Coupons List -->
    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap">
                <thead>
                    <tr class="text-left font-semibold bg-gray-50 dark:bg-gray-700">
                        <th class="px-6 py-3">Code</th>
                        <th class="px-6 py-3">Value</th>
                        <th class="px-6 py-3">Usage</th>
                        <th class="px-6 py-3">Valid Period</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($coupons as $coupon)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-semibold">{{ $coupon->code }}</div>
                                <div class="text-sm text-gray-500">{{ $coupon->description }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($coupon->value_type === 'percentage')
                                    {{ $coupon->value }}%
                                    @if($coupon->max_discount_amount)
                                        <div class="text-sm text-gray-500">
                                            Max: ₦{{ number_format($coupon->max_discount_amount) }}
                                        </div>
                                    @endif
                                @else
                                    ₦{{ number_format($coupon->value) }}
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ $coupon->usages_count }}
                                @if($coupon->usage_limit)
                                    <span class="text-gray-500">/ {{ $coupon->usage_limit }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div>{{ $coupon->starts_at->format('M d, Y') }}</div>
                                <div class="text-sm text-gray-500">
                                    to {{ $coupon->expires_at->format('M d, Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $coupon->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button onclick="openEditModal({{ json_encode($coupon) }})"
                                            class="text-blue-600 hover:text-blue-800">
                                        Edit
                                    </button>
                                    <a href="{{ route('admin.coupons.show', $coupon) }}"
                                       class="text-green-600 hover:text-green-800">
                                        View
                                    </a>
                                    <button onclick="deleteCoupon('{{ $coupon->id }}')"
                                            class="text-red-600 hover:text-red-800">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No coupons found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $coupons->links() }}
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
@include('admin.coupons._form_modal')

@push('scripts')
<script>
function openCreateModal() {
    document.getElementById('couponForm').reset();
    document.getElementById('modalTitle').textContent = 'Create Coupon';
    document.getElementById('couponModal').classList.remove('hidden');
}

function openEditModal(coupon) {
    const form = document.getElementById('couponForm');
    form.reset();
    
    // Fill form fields
    Object.keys(coupon).forEach(key => {
        const input = form.elements[key];
        if (input) {
            if (key === 'starts_at' || key === 'expires_at') {
                input.value = coupon[key].split('T')[0];
            } else {
                input.value = coupon[key];
            }
        }
    });

    document.getElementById('modalTitle').textContent = 'Edit Coupon';
    document.getElementById('couponModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('couponModal').classList.add('hidden');
}

function deleteCoupon(id) {
    if (!confirm('Are you sure you want to delete this coupon?')) return;

    fetch(`/admin/coupons/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
    });
}
</script>
@endpush

@endsection 