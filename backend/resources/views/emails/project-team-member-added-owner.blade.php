<x-mail.layout>
    <x-slot:greeting>
        {{ $greeting }}
    </x-slot>

    <div>
        <p>A new team member has been assigned to your project:</p>
        
        <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p><strong>Project:</strong> {{ $teamMember->project->name }}</p>
            <p><strong>Team Member:</strong> {{ $teamMember->professional->user->first_name }} {{ $teamMember->professional->user->last_name }}</p>
            <p><strong>Role:</strong> {{ ucwords(str_replace('_', ' ', $teamMember->role)) }}</p>
            <p><strong>Start Date:</strong> {{ $teamMember->joined_at->format('M d, Y') }}</p>
        </div>

        <p class="mt-4">You can see project team members from your dashboard.</p>
    </div>

    <x-slot:closing>
        {{ $closing }}
    </x-slot>
</x-mail.layout> 