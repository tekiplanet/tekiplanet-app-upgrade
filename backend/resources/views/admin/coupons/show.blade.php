@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Coupon Details
        </h2>
        <a href="{{ route('admin.coupons.index') }}" 
           class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            Back to List
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Coupon Information -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold mb-4">Coupon Information</h3>
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-gray-500">Code</label>
                    <p class="font-semibold">{{ $coupon->code }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Value</label>
                    <p>
                        @if($coupon->value_type === 'percentage')
                            {{ $coupon->value }}%
                            @if($coupon->max_discount)
                                (Max: ₦{{ number_format($coupon->max_discount) }})
                            @endif
                        @else
                            ₦{{ number_format($coupon->value) }}
                        @endif
                    </p>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Minimum Order Amount</label>
                    <p>₦{{ number_format($coupon->min_order_amount) }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Valid Period</label>
                    <p>{{ $coupon->starts_at->format('M d, Y') }} to {{ $coupon->expires_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Usage Limit Per User</label>
                    <p>{{ $coupon->usage_limit_per_user ?? 'Unlimited' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Status</label>
                    <span class="px-2 py-1 text-xs rounded-full 
                        {{ $coupon->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Usage Statistics -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold mb-4">Usage Statistics</h3>
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-gray-500">Total Uses</label>
                    <p class="text-2xl font-bold">{{ $coupon->usages->count() }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Total Discount Amount</label>
                    <p class="text-2xl font-bold">
                        ₦{{ number_format($coupon->usages->sum('discount_amount')) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Usage History -->
    <div class="mt-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Usage History</h3>
            <div class="overflow-x-auto">
                <table class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-left font-semibold bg-gray-50 dark:bg-gray-700">
                            <th class="px-6 py-3">User</th>
                            <th class="px-6 py-3">Order ID</th>
                            <th class="px-6 py-3">Order Amount</th>
                            <th class="px-6 py-3">Discount</th>
                            <th class="px-6 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($coupon->usages as $usage)
                            <tr>
                                <td class="px-6 py-4">
                                    {{ $usage->user->name }}
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.orders.show', $usage->order_id) }}"
                                       class="text-blue-600 hover:text-blue-800">
                                        {{ $usage->order_id }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    ₦{{ number_format($usage->order_amount) }}
                                </td>
                                <td class="px-6 py-4">
                                    ₦{{ number_format($usage->discount_amount) }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $usage->created_at->format('M d, Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No usage history found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 