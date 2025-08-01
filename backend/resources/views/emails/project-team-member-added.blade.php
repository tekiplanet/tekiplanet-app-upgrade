<x-mail.layout>
    <x-slot:greeting>
        {{ $greeting }}
    </x-slot>

    <div>
        <p>You have been added to a project team:</p>
        
        <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p><strong>Project:</strong> {{ $teamMember->project->name }}</p>
            <p><strong>Role:</strong> {{ ucwords(str_replace('_', ' ', $teamMember->role)) }}</p>
            <p><strong>Start Date:</strong> {{ $teamMember->joined_at->format('M d, Y') }}</p>
        </div>

        <p class="mt-4">Please log in to your dashboard to view project details and start collaborating with the team.</p>
    </div>

    <x-slot:closing>
        {{ $closing }}
    </x-slot>
</x-mail.layout> 