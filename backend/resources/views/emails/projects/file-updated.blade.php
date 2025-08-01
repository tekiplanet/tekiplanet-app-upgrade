@component('mail::layout')
    @slot('greeting')
        Hello {{ $project->businessProfile->user->name }},
    @endslot

    @if($action === 'uploaded')
        A new file has been uploaded to your project "{{ $project->name }}".
    @else
        A file has been deleted from your project "{{ $project->name }}".
    @endif

    @if($action === 'uploaded')
    **File Details:**
    - Name: {{ $file->name }}
    - Size: {{ number_format($file->size / 1024, 2) }} KB
    - Type: {{ $file->type }}
    @if($file->description)
    - Description: {{ $file->description }}
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