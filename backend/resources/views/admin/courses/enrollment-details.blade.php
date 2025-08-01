@extends('admin.layouts.app')

@section('title', 'Enrollment Details')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Enrollment Details
            </h1>
            <a href="{{ route('admin.courses.enrollments', $course) }}" 
               class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Back to Enrollments
            </a>
        </div>
        <p class="text-gray-600 dark:text-gray-400">
            Course: {{ $course->title }}
        </p>
    </div>

    <!-- Student Information -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Student Information</h2>
            <div class="flex items-start">
                <img class="h-20 w-20 rounded-full" 
                     src="{{ $enrollment->user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($enrollment->user->first_name.' '.$enrollment->user->last_name) }}" 
                     alt="{{ $enrollment->user->first_name }}">
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ $enrollment->user->first_name }} {{ $enrollment->user->last_name }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">{{ $enrollment->user->email }}</p>
                    <p class="text-gray-600 dark:text-gray-400">Phone: {{ $enrollment->user->phone ?? 'N/A' }}</p>
                    <p class="text-gray-600 dark:text-gray-400">
                        Enrolled: {{ $enrollment->enrolled_at ? date('M d, Y', strtotime($enrollment->enrolled_at)) : 'N/A' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollment Status -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Course Progress</h2>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-gray-700 dark:text-gray-300">Progress</span>
                        <span class="text-gray-700 dark:text-gray-300">{{ number_format($enrollment->progress, 1) }}%</span>
                    </div>
                    <div class="w-full h-2 bg-gray-200 rounded">
                        <div class="h-full bg-blue-600 rounded" style="width: {{ $enrollment->progress }}%"></div>
                    </div>
                </div>
                <div>
                    <span class="text-gray-700 dark:text-gray-300">Status:</span>
                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $enrollment->status === 'completed' ? 'bg-green-100 text-green-800' : 
                           ($enrollment->status === 'active' ? 'bg-blue-100 text-blue-800' : 
                           ($enrollment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                           'bg-red-100 text-red-800')) }}">
                        {{ ucfirst($enrollment->status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Payment Overview</h2>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-gray-700 dark:text-gray-300">Payment Progress</span>
                        <span class="text-gray-700 dark:text-gray-300">{{ number_format($paymentProgress, 1) }}%</span>
                    </div>
                    <div class="w-full h-2 bg-gray-200 rounded">
                        <div class="h-full bg-green-600 rounded" style="width: {{ $paymentProgress }}%"></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Amount</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ number_format($totalAmount, 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Paid Amount</p>
                        <p class="text-lg font-semibold text-green-600">
                            {{ number_format($paidAmount, 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Remaining</p>
                        <p class="text-lg font-semibold text-red-600">
                            {{ number_format($remainingAmount, 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $enrollment->payment_status === 'fully_paid' ? 'bg-green-100 text-green-800' : 
                               ($enrollment->payment_status === 'partially_paid' ? 'bg-yellow-100 text-yellow-800' : 
                               'bg-red-100 text-red-800') }}">
                            {{ str_replace('_', ' ', ucfirst($enrollment->payment_status)) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Next Payment Due</p>
                        @php
                            $nextPayment = $enrollment->installments()
                                ->where('status', 'pending')
                                ->orderBy('due_date')
                                ->first();
                        @endphp
                        @if($nextPayment)
                            <p class="text-lg font-semibold {{ strtotime($nextPayment->due_date) < time() ? 'text-red-600' : 'text-blue-600' }}">
                                {{ date('M d, Y', strtotime($nextPayment->due_date)) }}
                                @if(strtotime($nextPayment->due_date) < time())
                                    <span class="text-sm text-red-500">(Overdue)</span>
                                @endif
                            </p>
                        @else
                            <p class="text-lg font-semibold text-green-600">No pending payments</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Payment History</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Order
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Due Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Paid Date
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($enrollment->installments as $installment)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $installment->order }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ number_format($installment->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $installment->due_date ? date('M d, Y', strtotime($installment->due_date)) : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $installment->status === 'paid' ? 'bg-green-100 text-green-800' : 
                                           ($installment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                           'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($installment->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $installment->paid_at ? date('M d, Y', strtotime($installment->paid_at)) : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 