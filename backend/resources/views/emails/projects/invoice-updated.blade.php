@component('mail::layout')
    @slot('greeting')
        Hello {{ $project->businessProfile->user->name }},
    @endslot

    @if($action === 'created')
        A new invoice has been created for your project "{{ $project->name }}".
    @elseif($action === 'updated')
        An invoice in project "{{ $project->name }}" has been updated.
    @else
        An invoice has been deleted from project "{{ $project->name }}".
    @endif

    @if($action !== 'deleted')
    **Invoice Details:**
    - Amount: ₦{{ number_format($invoice->amount, 2) }}
    - Status: {{ ucfirst($invoice->status) }}
    - Due Date: {{ $invoice->due_date->format('M d, Y') }}
    - Description: {{ $invoice->description }}
    @endif

    @component('mail::button', ['url' => config('app.url') . "/projects/{$project->id}"])
        View Project
    @endcomponent

    @slot('closing')
        Best regards,<br>
        {{ config('app.name') }} Team
    @endslot
@endcomponent 