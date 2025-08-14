@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.hustles.index') }}" 
               class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Hustle Details
            </h2>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.hustles.edit', $hustle) }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Edit
            </a>
            <form action="{{ route('admin.hustles.destroy', $hustle) }}" 
                  method="POST" 
                  onsubmit="return confirm('Are you sure you want to delete this hustle?');"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Hustle Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                Basic Information
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">Title</label>
                    <p class="text-gray-900 dark:text-gray-100">{{ $hustle->title }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">Category</label>
                    <p class="text-gray-900 dark:text-gray-100">{{ $hustle->category->name }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">Budget</label>
                    <p class="text-gray-900 dark:text-gray-100">₦{{ number_format($hustle->budget, 2) }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">Deadline</label>
                    <p class="text-gray-900 dark:text-gray-100">{{ $hustle->deadline->format('M d, Y') }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">Status</label>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $hustle->status === 'open' ? 'bg-green-100 text-green-800' : 
                           ($hustle->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                           ($hustle->status === 'completed' ? 'bg-gray-100 text-gray-800' : 
                            'bg-red-100 text-red-800')) }}">
                        {{ ucfirst($hustle->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Description and Requirements -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">
                        Description
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $hustle->description }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">
                        Requirements
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $hustle->requirements }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications Section -->
    <div class="mt-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                Applications ({{ $hustle->applications->count() }})
            </h3>
            <a href="{{ route('admin.hustles.applications.index', $hustle) }}" 
               class="text-blue-600 hover:text-blue-900">
                View All
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-left bg-gray-50 dark:bg-gray-700">
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Professional
                            </th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Applied At
                            </th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($hustle->applications->take(5) as $application)
                            <tr>
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $application->professional->user->name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $application->professional->user->email }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $application->professional->category->name }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $application->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                           ($application->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($application->status === 'rejected' ? 'bg-red-100 text-red-800' : 
                                            'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst($application->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    {{ $application->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.hustles.applications.show', [$hustle, $application]) }}" 
                                       class="text-blue-600 hover:text-blue-900">
                                        View
                                    </a>
                                    @if($application->status === 'pending')
                                        <form action="{{ route('admin.hustles.applications.update-status', [$hustle, $application]) }}" 
                                              method="POST" 
                                              class="inline ml-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" 
                                                    class="text-green-600 hover:text-green-900">
                                                Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.hustles.applications.update-status', [$hustle, $application]) }}" 
                                              method="POST" 
                                              class="inline ml-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900">
                                                Reject
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    No applications yet
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