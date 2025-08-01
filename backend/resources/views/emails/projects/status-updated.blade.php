@component('mail::layout')
    @slot('greeting')
        Hello {{ $project->businessProfile->user->name }},
    @endslot

    Your project "{{ $project->name }}" status has been updated from {{ ucfirst($oldStatus) }} to {{ ucfirst($newStatus) }}.

    @if($notes)
    **Additional Notes:**
    {{ $notes }}
    @endif

    **Project Details:**
    - Client: {{ $project->client_name }}
    - Progress: {{ $project->progress }}%
    - Start Date: {{ $project->start_date->format('M d, Y') }}
    - End Date: {{ $project->end_date ? $project->end_date->format('M d, Y') : 'Not set' }}

    @component('mail::button', ['url' => config('app.url') . "/projects/{$project->id}"])
        View Project
    @endcomponent

    @slot('closing')
        Best regards,<br>
        {{ config('app.name') }} Team
    @endslot
@endcomponent 