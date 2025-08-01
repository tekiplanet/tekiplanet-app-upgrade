@extends('admin.layouts.app')

@section('title', 'View Plan')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $plan->name }}
        </h2>
        <div class="flex gap-2">
            <a href="{{ route('admin.workstation.plans.edit', $plan) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Edit Plan
            </a>
            <a href="{{ route('admin.workstation.plans.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Plans
            </a>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Plan Details -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Plan Details</h3>
                            <dl class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Price</dt>
                                    <dd class="mt-1 text-sm text-gray-900">₦{{ number_format($plan->price, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Duration</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $plan->duration_days }} days</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Print Pages Limit</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $plan->print_pages_limit }} pages</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Meeting Room Hours</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $plan->meeting_room_hours === -1 ? 'Unlimited' : $plan->meeting_room_hours . ' hours' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Features</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <ul class="list-disc list-inside">
                                            @foreach($plan->features as $feature)
                                                <li>{{ $feature }}</li>
                                            @endforeach
                                        </ul>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Additional Information -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Additional Information</h3>
                            <dl class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Locker Access</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $plan->has_locker ? 'Yes' : 'No' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Dedicated Support</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $plan->has_dedicated_support ? 'Yes' : 'No' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Installment Option</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($plan->allows_installments)
                                            Yes ({{ $plan->installment_months }} months @ ₦{{ number_format($plan->installment_amount, 2) }}/month)
                                        @else
                                            No
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Subscriptions</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $plan->subscriptions_count }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 