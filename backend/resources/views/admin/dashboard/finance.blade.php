@extends('admin.layouts.app')

@section('content')
@php
    $currency = App\Models\Setting::getSetting('currency_symbol', '$');
@endphp

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <h1 class="text-2xl font-semibold text-gray-900">Finance Dashboard</h1>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Stats Overview Cards -->
        <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Revenue Card -->
            <div class="relative group bg-white overflow-hidden rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-blue-100 rounded-lg">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                                    Total Revenue
                                </dt>
                                <dd class="mt-1 text-2xl font-extrabold text-gray-900">
                                    {{ $currency }}{{ number_format($totalRevenue, 2) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="relative group bg-white overflow-hidden rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-green-100 rounded-lg">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                                    Monthly Revenue
                                </dt>
                                <dd class="mt-1 text-2xl font-extrabold text-gray-900">
                                    {{ $currency }}{{ number_format($monthlyRevenue, 2) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verified Bank Accounts -->
            <div class="relative group bg-white overflow-hidden rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-purple-100 rounded-lg">
                            <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                                    Verified Accounts
                                </dt>
                                <dd class="mt-1 text-2xl font-extrabold text-gray-900">
                                    {{ $activeBankAccounts }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Transactions -->
            <div class="relative group bg-white overflow-hidden rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-yellow-100 rounded-lg">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                                    Pending Transactions
                                </dt>
                                <dd class="mt-1 text-2xl font-extrabold text-gray-900">
                                    {{ $pendingTransactions }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
            <!-- Recent Transactions -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-medium text-gray-900">Recent Transactions</h2>
                        <a href="{{ route('admin.transactions.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                            View all
                        </a>
                    </div>
                    <div class="flow-root">
                        <ul role="list" class="-my-5 divide-y divide-gray-200">
                            @forelse($recentTransactions as $transaction)
                            <li class="py-5">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">
                                            {{ $transaction->description }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $transaction->created_at->format('M d, Y H:i') }}
                                        </p>
                                        <p class="mt-1 text-sm font-medium text-gray-900">
                                            {{ $currency }}{{ number_format($transaction->amount, 2) }}
                                        </p>
                                    </div>
                                    <div>
                                        <span @class([
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            'bg-green-100 text-green-800' => $transaction->type === 'credit',
                                            'bg-red-100 text-red-800' => $transaction->type === 'debit',
                                        ])>
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="py-5">
                                <p class="text-sm text-gray-500 text-center">No recent transactions</p>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Bank Accounts Summary -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-medium text-gray-900">Bank Accounts</h2>
                        <a href="{{ route('admin.bank-accounts.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                            View all
                        </a>
                    </div>
                    <div class="flow-root">
                        <ul role="list" class="-my-5 divide-y divide-gray-200">
                            @forelse($bankAccounts as $account)
                            <li class="py-5">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ $account->bank_name }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $account->account_number }}
                                        </p>

                                    </div>
                                    <div>
                                        <span @class([
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            'bg-green-100 text-green-800' => $account->is_verified,
                                            'bg-yellow-100 text-yellow-800' => !$account->is_verified,
                                        ])>
                                            {{ $account->is_verified ? 'Verified' : 'Pending Verification' }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="py-5">
                                <p class="text-sm text-gray-500 text-center">No bank accounts found</p>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 