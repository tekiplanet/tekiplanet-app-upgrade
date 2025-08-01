@component('mail::layout')
    @slot('greeting')
        Hello {{ $project->businessProfile->user->name }},
    @endslot

    A new project has been created for your business.

    **Project Details:**
    - Name: {{ $project->name }}
    - Client: {{ $project->client_name }}
    - Status: {{ ucfirst($project->status) }}
    - Start Date: {{ $project->start_date->format('M d, Y') }}
    - End Date: {{ $project->end_date ? $project->end_date->format('M d, Y') : 'Not set' }}
    - Budget: ₦{{ number_format($project->budget, 2) }}

    @component('mail::button', ['url' => config('app.url') . "/projects/{$project->id}"])
        View Project
    @endcomponent

    @slot('closing')
        Best regards,<br>
        {{ config('app.name') }} Team
    @endslot
@endcomponent 