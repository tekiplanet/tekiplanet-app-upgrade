@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.hustles.show', $hustle) }}" 
               class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                    Applications
                </h2>
                <p class="text-sm text-gray-500">{{ $hustle->title }}</p>
            </div>
        </div>
    </div>

    <!-- Applications List -->
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
                    @forelse($applications as $application)
                        <tr>
                            <td class="px-6 py-4">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $application->professional->user->name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $application->professional->user->email }}
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
                            <td class="px-6 py-4 space-x-2">
                                <a href="{{ route('admin.hustles.applications.show', [$hustle, $application]) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    View
                                </a>
                                @if($application->status === 'pending')
                                    <button onclick="updateApplicationStatus(
                                        '{{ route('admin.hustles.applications.update-status', [$hustle, $application]) }}',
                                        'approved',
                                        'Approve'
                                    )" class="text-green-600 hover:text-green-900 ml-2">
                                        Approve
                                    </button>
                                    <button onclick="updateApplicationStatus(
                                        '{{ route('admin.hustles.applications.update-status', [$hustle, $application]) }}',
                                        'rejected',
                                        'Reject'
                                    )" class="text-red-600 hover:text-red-900 ml-2">
                                        Reject
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                No applications found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t">
            {{ $applications->links() }}
        </div>
    </div>
</div>
@endsection 

@push('scripts')
    @include('admin.hustles.applications._status-update-script')
@endpush 