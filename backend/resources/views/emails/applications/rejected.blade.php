<x-mail.layout>
    <x-slot:greeting>
        Hello {{ $professional->user->name }}!
    </x-slot>

    <p>We regret to inform you that your application for the GRIT "{{ $grit->title }}" was not selected.</p>
    
    <div style="margin: 20px 0; padding: 15px; background-color: #fef2f2; border: 1px solid #ef4444; border-radius: 6px;">
        <h3 style="margin-top: 0; color: #991b1b; font-size: 18px;">{{ $grit->title }}</h3>
        <p><strong>Category:</strong> {{ $grit->category->name }}</p>
        <p><strong>Budget:</strong> {{ $grit->owner_currency ?? '₦' }}{{ number_format($grit->owner_budget ?? $grit->budget, 2) }}</p>
        <p><strong>Deadline:</strong> {{ optional($grit->deadline)->format('M d, Y') }}</p>
        <p><strong>Status:</strong> <span style="color: #dc2626; font-weight: 600;">Rejected</span></p>
    </div>

    @if(isset($reason) && $reason)
    <div style="margin: 20px 0; padding: 15px; background-color: #fffbeb; border: 1px solid #f59e0b; border-radius: 6px;">
        <h4 style="margin-top: 0; color: #92400e; font-size: 16px;">Reason:</h4>
        <p style="color: #92400e; margin: 0;">{{ $reason }}</p>
    </div>
    @endif

    <p>Don't worry! Keep checking for new GRITs that match your skills. There are always new opportunities available.</p>

    <x-slot:closing>
        <p>Best regards,<br>TekiPlanet Team</p>
    </x-slot>
</x-mail.layout> 