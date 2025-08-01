<x-mail.layout>
    <x-slot:greeting>
        {{ $greeting }}
    </x-slot>

    <div>
        @if(isset($type) && $type === 'progress')
            <p>The progress of your project has been updated:</p>
            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <p><strong>Project:</strong> {{ $projectName }}</p>
                <p><strong>New Progress:</strong> {{ $progress }}</p>
            </div>
        @else
            <p>Your project details have been updated:</p>
        @endif
        
        <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p><strong>Project:</strong> {{ $projectName }}</p>
            
            @if($isProgressUpdate)
                <p><strong>Progress:</strong> {{ $progress }}%</p>
                <p><strong>Status:</strong> {{ ucfirst($status) }}</p>
            @else
                @foreach($changes as $field => $value)
                    <p><strong>{{ ucwords(str_replace('_', ' ', $field)) }}:</strong> {{ $value }}</p>
                @endforeach
            @endif
        </div>

        <p class="mt-4">You can view the updated project details from your dashboard.</p>
    </div>

    <x-slot:closing>
        {{ $closing }}
    </x-slot>
</x-mail.layout> 