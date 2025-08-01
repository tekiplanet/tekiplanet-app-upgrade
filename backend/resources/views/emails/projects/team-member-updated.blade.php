@component('mail::layout')
    @slot('greeting')
        Hello {{ $project->businessProfile->user->name }},
    @endslot

    @if($action === 'added')
        {{ $user->name }} has been added to your project "{{ $project->name }}".
    @elseif($action === 'updated')
        {{ $user->name }}'s role in project "{{ $project->name }}" has been updated.
    @else
        {{ $user->name }} has been removed from project "{{ $project->name }}".
    @endif

    @if($action !== 'removed')
    **Team Member Details:**
    - Name: {{ $user->name }}
    - Role: {{ $member->role }}
    - Status: {{ ucfirst($member->status) }}
    - Joined: {{ $member->joined_at->format('M d, Y') }}
    @if($member->left_at)
    - Left: {{ $member->left_at->format('M d, Y') }}
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