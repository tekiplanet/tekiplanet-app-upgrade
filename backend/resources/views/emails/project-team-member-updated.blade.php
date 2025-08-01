<x-mail.layout>
    <x-slot:greeting>
        {{ $greeting }}
    </x-slot>

    <div>
        @if($oldStatus === 'removed')
            <p>You have been removed from the following project team:</p>
        @else
            <p>Your status in the project team has been updated:</p>
        @endif
        
        <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p><strong>Project:</strong> {{ $projectName }}</p>
            <p><strong>Role:</strong> {{ ucwords(str_replace('_', ' ', $memberRole)) }}</p>
            @if($oldStatus !== 'removed')
                <p><strong>Previous Status:</strong> {{ ucfirst($oldStatus) }}</p>
                <p><strong>New Status:</strong> {{ ucfirst($newStatus) }}</p>
            @endif
        </div>

        @if($oldStatus !== 'removed')
            <p class="mt-4">You can view the project details and your updated status from your dashboard.</p>
        @endif
    </div>

    <x-slot:closing>
        {{ $closing }}
    </x-slot>
</x-mail.layout> 