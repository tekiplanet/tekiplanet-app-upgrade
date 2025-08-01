<x-mail.layout>
    <div>
        <h2 class="text-xl font-bold mb-4">New Project Created</h2>
        
        <div class="text-gray-600 dark:text-gray-400">
            <p>A new project has been created for your business:</p>
            
            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <p><strong>Project Name:</strong> {{ $project->name }}</p>
                <p><strong>Start Date:</strong> {{ $project->start_date->format('M d, Y') }}</p>
                <p><strong>Budget:</strong> ₦{{ number_format($project->budget, 2) }}</p>
                <p><strong>Status:</strong> {{ ucfirst($project->status) }}</p>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ config('app.url') }}/dashboard/projects/{{ $project->id }}" 
               class="button">
                View Project Details
            </a>
        </div>
    </div>
</x-mail.layout> 