@extends('admin.layouts.app')

@section('title', 'User Details')

@section('content')
@if(session()->has('notify'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: @json(session('notify'))
            }));
        });
    </script>
@endif

<div class="space-y-6">
    <!-- Page Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center">
                <a href="{{ route('admin.users.index') }}" class="text-primary hover:text-primary-dark mr-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">User Details</h1>
            </div>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                Manage user information, wallet, and transactions
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-2 sm:space-x-3">
            <!-- Edit User Button -->
            <button 
                type="button" 
                onclick="openEditModal()"
                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
            >
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit User
            </button>

            <!-- Send Notification Button -->
            <button 
                type="button" 
                onclick="openNotificationModal()"
                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
            >
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                Send Notification
            </button>

            <!-- Status Toggle Button -->
            <button 
                type="button" 
                onclick="openStatusModal()"
                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
            >
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}
            </button>

            <!-- New Transaction Button -->
            <button 
                type="button" 
                onclick="openTransactionModal()"
                class="inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
            >
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                New Transaction
            </button>

            <!-- Delete User Button -->
            <button 
                type="button" 
                onclick="openDeleteModal()"
                class="inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete User
            </button>
        </div>
    </div>

    <!-- User Info Card -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:px-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 h-12 w-12">
                    @if($user->avatar)
                        <img class="h-12 w-12 rounded-full" src="{{ $user->avatar }}" alt="">
                    @else
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary">
                            <span class="text-lg font-medium leading-none text-white">
                                {{ strtoupper(substr($user->first_name, 0, 1)) }}
                            </span>
                        </span>
                    @endif
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                        {{ $user->first_name }} {{ $user->last_name }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $user->email }}
                    </p>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Username</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $user->username }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Account Type</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
                            $user->businessProfile ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                            ($user->professional ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 
                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200') 
                        }}">
                            {{ $user->businessProfile ? 'Business' : ($user->professional ? 'Professional' : 'Student') }}
                        </span>
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
                            $user->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                        }}">
                            {{ ucfirst($user->status) }}
                        </span>
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Joined Date</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $user->created_at->format('M d, Y') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Wallet Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <!-- Current Balance -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Current Balance</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ $currency['symbol'] }}{{ number_format($stats['current_balance'], 2) }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Credits -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Credits</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ $currency['symbol'] }}{{ number_format($stats['total_credits'], 2) }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Debits -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Debits</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ $currency['symbol'] }}{{ number_format($stats['total_debits'], 2) }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Section -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
            <div class="sm:flex sm:items-center sm:justify-between">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Recent Transactions
                </h3>
                <div class="mt-3 sm:mt-0">
                    <form action="{{ route('admin.users.show', $user) }}" method="GET" class="flex flex-col sm:flex-row gap-2">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md"
                            placeholder="Search transactions..."
                        >
                        <select 
                            name="type"
                            class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:w-auto sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md"
                        >
                            <option value="">All Types</option>
                            <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit</option>
                            <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit</option>
                        </select>
                        <select 
                            name="status"
                            class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:w-auto sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md"
                        >
                            <option value="">All Status</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                        <button 
                            type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                        >
                            Filter
                        </button>
                        @if(request()->hasAny(['search', 'type', 'status']))
                            <a 
                                href="{{ route('admin.users.show', $user) }}"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                            >
                                Clear
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Desktop Table (hidden on mobile) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Type
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Amount
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Description
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Reference
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($transactions as $transaction)
                        <tr class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                            @click="$dispatch('show-transaction-details', {
                                ...{{ Js::from($transaction) }},
                                currency_symbol: '{{ $currency['symbol'] }}',
                                formatted_date: '{{ $transaction->created_at->format('M d, Y h:i A') }}'
                            })"
                        >
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $transaction->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
                                    $transaction->type === 'credit' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $currency['symbol'] }}{{ number_format($transaction->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
                                    $transaction->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $transaction->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $transaction->reference_number }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                No transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($transactions as $transaction)
                <div class="p-4 space-y-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                    @click="$dispatch('show-transaction-details', {
                        ...{{ Js::from($transaction) }},
                        currency_symbol: '{{ $currency['symbol'] }}',
                        formatted_date: '{{ $transaction->created_at->format('M d, Y h:i A') }}'
                    })"
                >
                    <!-- Amount and Type -->
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ $currency['symbol'] }}{{ number_format($transaction->amount, 2) }}
                        </span>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
                                $transaction->type === 'credit' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                            }}">
                                {{ ucfirst($transaction->type) }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
                                $transaction->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                            }}">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $transaction->description }}
                    </div>

                    <!-- Reference and Date -->
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $transaction->reference_number }}</span>
                        <span>{{ $transaction->created_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            @empty
                <div class="p-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                    No transactions found.
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
            {{ $transactions->links() }}
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-6 py-5 text-left shadow-xl transition-all w-full max-w-2xl">
                <form id="editUserForm" onsubmit="submitEditForm(event)">
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                            Edit User
                        </h3>
                        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    First Name
                                </label>
                                <div class="mt-1">
                                    <input 
                                        type="text" 
                                        name="first_name"
                                        value="{{ $user->first_name }}"
                                        class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md"
                                    >
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Last Name
                                </label>
                                <div class="mt-1">
                                    <input 
                                        type="text" 
                                        name="last_name"
                                        value="{{ $user->last_name }}"
                                        class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md"
                                    >
                                </div>
                            </div>

                            <div class="sm:col-span-6">
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Email
                                </label>
                                <div class="mt-1">
                                    <input 
                                        type="email" 
                                        name="email"
                                        value="{{ $user->email }}"
                                        class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md"
                                    >
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Username
                                </label>
                                <div class="mt-1">
                                    <input 
                                        type="text" 
                                        name="username"
                                        value="{{ $user->username }}"
                                        class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md"
                                    >
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Phone
                                </label>
                                <div class="mt-1">
                                    <input 
                                        type="text" 
                                        name="phone"
                                        value="{{ $user->phone }}"
                                        class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button 
                            type="button"
                            onclick="closeEditModal()"
                            class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:text-sm"
                            id="cancelButton"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            id="submitButton"
                            class="inline-flex justify-center items-center rounded-md border border-transparent bg-primary px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span id="submitSpinner" class="hidden">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span id="submitText">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Send Notification Modal -->
<div id="sendNotificationModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-6 py-5 text-left shadow-xl transition-all w-full max-w-2xl">
                <form id="notificationForm" onsubmit="submitNotificationForm(event)">
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                            Send Notification
                        </h3>
                        <div class="mt-6 space-y-6">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Title
                                </label>
                                <div class="mt-1">
                                    <input 
                                        type="text" 
                                        name="title"
                                        class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md"
                                        required
                                    >
                                </div>
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Message
                                </label>
                                <div class="mt-1">
                                    <textarea 
                                        name="message"
                                        rows="4"
                                        class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md"
                                        required
                                    ></textarea>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    name="send_email"
                                    id="send_email"
                                    class="h-4 w-4 text-primary focus:ring-primary border-gray-300 dark:border-gray-700 rounded"
                                >
                                <label for="send_email" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                    Also send as email
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button 
                            type="button"
                            onclick="closeNotificationModal()"
                            class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:text-sm"
                            id="notifyCancelButton"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            id="notifySubmitButton"
                            class="inline-flex justify-center items-center rounded-md border border-transparent bg-primary px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span id="notifySpinner" class="hidden">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span id="notifyButtonText">Send Notification</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="updateStatusModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-6 py-5 text-left shadow-xl transition-all w-full max-w-lg">
                <form id="statusForm" onsubmit="submitStatusForm(event)">
                    <div>
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full" id="statusIcon">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                Confirm Status Change
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Are you sure you want to <span id="actionText"></span> this user's account? 
                                    This will <span id="consequenceText"></span> them from accessing the platform.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button 
                            type="button"
                            onclick="closeStatusModal()"
                            class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:text-sm"
                            id="statusCancelButton"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            id="statusSubmitButton"
                            class="inline-flex justify-center items-center rounded-md border border-transparent px-4 py-2 text-base font-medium text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span id="statusSpinner" class="hidden">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span id="statusButtonText"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteUserModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-6 py-5 text-left shadow-xl transition-all w-full max-w-lg">
                <form id="deleteForm" onsubmit="submitDeleteForm(event)">
                    <div>
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                Delete User Account
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Are you sure you want to delete this user's account? This action cannot be undone and will permanently delete all associated data.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button 
                            type="button"
                            onclick="closeDeleteModal()"
                            class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:text-sm"
                            id="deleteCancelButton"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            id="deleteSubmitButton"
                            class="inline-flex justify-center items-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span id="deleteSpinner" class="hidden">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span id="deleteButtonText">Delete User</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add the Transaction Modal -->
<div id="createTransactionModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-6 py-5 text-left shadow-xl transition-all w-full max-w-2xl">
                <form id="transactionForm" onsubmit="submitTransactionForm(event)">
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                            Create New Transaction
                        </h3>
                        <div class="mt-6 space-y-6">
                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Type
                                    </label>
                                    <select 
                                        name="type"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                                    >
                                        <option value="credit">Credit</option>
                                        <option value="debit">Debit</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Amount
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">{{ $currency['symbol'] }}</span>
                                        </div>
                                        <input 
                                            type="number" 
                                            name="amount"
                                            required
                                            min="0"
                                            step="0.01"
                                            class="pl-7 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                                        >
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Category
                                    </label>
                                    <select 
                                        name="category"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                                    >
                                        <option value="deposit">Deposit</option>
                                        <option value="withdrawal">Withdrawal</option>
                                        <option value="payment">Payment</option>
                                        <option value="refund">Refund</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Payment Method
                                    </label>
                                    <select 
                                        name="payment_method"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                                    >
                                        <option value="wallet">Wallet</option>
                                        <option value="bank">Bank Transfer</option>
                                        <option value="card">Card</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Description
                                    </label>
                                    <div class="mt-1">
                                        <input 
                                            type="text"
                                            name="description"
                                            required
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                                        >
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Notes
                                    </label>
                                    <div class="mt-1">
                                        <textarea 
                                            name="notes"
                                            rows="3"
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button 
                            type="button"
                            onclick="closeTransactionModal()"
                            class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:text-sm"
                            id="transactionCancelButton"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            id="transactionSubmitButton"
                            class="inline-flex justify-center items-center rounded-md border border-transparent bg-primary px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span id="transactionSpinner" class="hidden">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span id="transactionButtonText">Create Transaction</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add this script at the bottom of your file -->
<script>
    function openEditModal() {
        document.getElementById('editUserModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editUserModal').classList.add('hidden');
    }

    function submitEditForm(event) {
        event.preventDefault();
        const form = event.target;
        const submitButton = document.getElementById('submitButton');
        const cancelButton = document.getElementById('cancelButton');
        const submitSpinner = document.getElementById('submitSpinner');
        const submitText = document.getElementById('submitText');

        // Disable buttons and show loading state
        submitButton.disabled = true;
        cancelButton.disabled = true;
        submitSpinner.classList.remove('hidden');
        submitText.textContent = 'Saving...';

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        fetch('{{ route('admin.users.update', $user) }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.message) {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { 
                        type: 'success', 
                        message: result.message 
                    }
                }));
                
                closeEditModal();
                window.location.reload();
            }
        })
        .catch(error => {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { 
                    type: 'error', 
                    message: error.message || 'Something went wrong' 
                }
            }));

            // Reset button state on error
            submitButton.disabled = false;
            cancelButton.disabled = false;
            submitSpinner.classList.add('hidden');
            submitText.textContent = 'Save Changes';
        });
    }

    // Close modal when clicking outside
    document.getElementById('editUserModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeEditModal();
        }
    });

    // Close modal with escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeEditModal();
        }
    });

    function openNotificationModal() {
        document.getElementById('sendNotificationModal').classList.remove('hidden');
    }

    function closeNotificationModal() {
        document.getElementById('sendNotificationModal').classList.add('hidden');
        document.getElementById('notificationForm').reset();
    }

    function submitNotificationForm(event) {
        event.preventDefault();
        const form = event.target;
        const submitButton = document.getElementById('notifySubmitButton');
        const cancelButton = document.getElementById('notifyCancelButton');
        const spinner = document.getElementById('notifySpinner');
        const buttonText = document.getElementById('notifyButtonText');

        // Disable buttons and show loading state
        submitButton.disabled = true;
        cancelButton.disabled = true;
        spinner.classList.remove('hidden');
        buttonText.textContent = 'Sending...';

        const formData = new FormData(form);
        const data = {
            title: formData.get('title'),
            message: formData.get('message'),
            send_email: formData.get('send_email') === 'on'
        };

        fetch('{{ route('admin.users.notify', $user) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(async response => {
            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'Failed to send notification');
            }
            return result;
        })
        .then(result => {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { 
                    type: 'success', 
                    message: result.message 
                }
            }));
            
            closeNotificationModal();
        })
        .catch(error => {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { 
                    type: 'error', 
                    message: error.message || 'Failed to send notification' 
                }
            }));
        })
        .finally(() => {
            // Reset button state
            submitButton.disabled = false;
            cancelButton.disabled = false;
            spinner.classList.add('hidden');
            buttonText.textContent = 'Send Notification';
        });
    }

    // Close modal when clicking outside
    document.getElementById('sendNotificationModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeNotificationModal();
        }
    });

    function openTransactionModal() {
        document.getElementById('createTransactionModal').classList.remove('hidden');
    }

    function closeTransactionModal() {
        document.getElementById('createTransactionModal').classList.add('hidden');
        document.getElementById('transactionForm').reset();
    }

    function submitTransactionForm(event) {
        event.preventDefault();
        const form = event.target;
        const submitButton = document.getElementById('transactionSubmitButton');
        const cancelButton = document.getElementById('transactionCancelButton');
        const spinner = document.getElementById('transactionSpinner');
        const buttonText = document.getElementById('transactionButtonText');

        // Disable buttons and show loading state
        submitButton.disabled = true;
        cancelButton.disabled = true;
        spinner.classList.remove('hidden');
        buttonText.textContent = 'Processing...';

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        fetch('{{ route('admin.users.transactions.store', $user) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(async response => {
            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'Failed to create transaction');
            }
            return result;
        })
        .then(result => {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { 
                    type: 'success', 
                    message: result.message 
                }
            }));
            
            closeTransactionModal();
            window.location.reload();
        })
        .catch(error => {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { 
                    type: 'error', 
                    message: error.message || 'Failed to create transaction' 
                }
            }));
        })
        .finally(() => {
            // Reset button state
            submitButton.disabled = false;
            cancelButton.disabled = false;
            spinner.classList.add('hidden');
            buttonText.textContent = 'Create Transaction';
        });
    }

    // Close modal when clicking outside
    document.getElementById('createTransactionModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeTransactionModal();
        }
    });

    const currentStatus = '{{ $user->status }}';
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';

    function updateStatusUI() {
        const isDeactivating = currentStatus === 'active';
        const submitButton = document.getElementById('statusSubmitButton');
        const statusIcon = document.getElementById('statusIcon');
        const actionText = document.getElementById('actionText');
        const consequenceText = document.getElementById('consequenceText');
        const buttonText = document.getElementById('statusButtonText');

        // Update colors and text
        if (isDeactivating) {
            submitButton.classList.add('bg-red-600', 'hover:bg-red-700', 'focus:ring-red-500');
            submitButton.classList.remove('bg-green-600', 'hover:bg-green-700', 'focus:ring-green-500');
            statusIcon.classList.add('bg-red-100', 'text-red-600');
            statusIcon.classList.remove('bg-green-100', 'text-green-600');
        } else {
            submitButton.classList.add('bg-green-600', 'hover:bg-green-700', 'focus:ring-green-500');
            submitButton.classList.remove('bg-red-600', 'hover:bg-red-700', 'focus:ring-red-500');
            statusIcon.classList.add('bg-green-100', 'text-green-600');
            statusIcon.classList.remove('bg-red-100', 'text-red-600');
        }

        actionText.textContent = isDeactivating ? 'deactivate' : 'activate';
        consequenceText.textContent = isDeactivating ? 'prevent' : 'allow';
        buttonText.textContent = isDeactivating ? 'Deactivate Account' : 'Activate Account';
    }

    function openStatusModal() {
        document.getElementById('updateStatusModal').classList.remove('hidden');
        updateStatusUI();
    }

    function closeStatusModal() {
        document.getElementById('updateStatusModal').classList.add('hidden');
    }

    function submitStatusForm(event) {
        event.preventDefault();
        const submitButton = document.getElementById('statusSubmitButton');
        const cancelButton = document.getElementById('statusCancelButton');
        const spinner = document.getElementById('statusSpinner');
        const buttonText = document.getElementById('statusButtonText');

        // Disable buttons and show loading state
        submitButton.disabled = true;
        cancelButton.disabled = true;
        spinner.classList.remove('hidden');
        buttonText.textContent = 'Processing...';

        fetch('{{ route('admin.users.status', $user) }}', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(async response => {
            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'Failed to update status');
            }
            return result;
        })
        .then(result => {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { 
                    type: 'success', 
                    message: result.message 
                }
            }));
            
            closeStatusModal();
            window.location.reload();
        })
        .catch(error => {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { 
                    type: 'error', 
                    message: error.message || 'Failed to update status' 
                }
            }));
        })
        .finally(() => {
            // Reset button state
            submitButton.disabled = false;
            cancelButton.disabled = false;
            spinner.classList.add('hidden');
            updateStatusUI();
        });
    }

    // Close modal when clicking outside
    document.getElementById('updateStatusModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeStatusModal();
        }
    });

    function openDeleteModal() {
        document.getElementById('deleteUserModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteUserModal').classList.add('hidden');
    }

    function submitDeleteForm(event) {
        event.preventDefault();
        const submitButton = document.getElementById('deleteSubmitButton');
        const cancelButton = document.getElementById('deleteCancelButton');
        const spinner = document.getElementById('deleteSpinner');
        const buttonText = document.getElementById('deleteButtonText');

        // Disable buttons and show loading state
        submitButton.disabled = true;
        cancelButton.disabled = true;
        spinner.classList.remove('hidden');
        buttonText.textContent = 'Deleting...';

        fetch('{{ route('admin.users.destroy', $user) }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'Failed to delete user');
            }
            return result;
        })
        .then(result => {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { 
                    type: 'success', 
                    message: result.message 
                }
            }));
            
            // Redirect to users list
            window.location.href = '{{ route('admin.users.index') }}';
        })
        .catch(error => {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { 
                    type: 'error', 
                    message: error.message || 'Failed to delete user' 
                }
            }));

            // Reset button state
            submitButton.disabled = false;
            cancelButton.disabled = false;
            spinner.classList.add('hidden');
            buttonText.textContent = 'Delete User';
        });
    }

    // Close modal when clicking outside
    document.getElementById('deleteUserModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeDeleteModal();
        }
    });
</script>
@endsection 