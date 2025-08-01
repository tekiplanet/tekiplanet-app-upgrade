@component('mail::layout')
    @slot('greeting')
        Hello {{ $project->businessProfile->user->name }},
    @endslot

    @if($action === 'created')
        A new stage has been added to your project "{{ $project->name }}".
    @elseif($action === 'updated')
        A stage in your project "{{ $project->name }}" has been updated.
    @else
        A stage has been removed from your project "{{ $project->name }}".
    @endif

    @if($action !== 'deleted')
    **Stage Details:**
    - Name: {{ $stage->name }}
    - Status: {{ ucfirst($stage->status) }}
    - Start Date: {{ $stage->start_date->format('M d, Y') }}
    - End Date: {{ $stage->end_date ? $stage->end_date->format('M d, Y') : 'Not set' }}
    @if($stage->description)
    - Description: {{ $stage->description }}
    @endif
    @endif

    @component('mail::button', ['url' => config('app.url') . "/projects/{$project->id}"])
        View Project
    @endcomponent

    @slot('closing')
        Best regards,<br>
        {{ config('app.name') }} Team
    @endslot
@endcomponent 