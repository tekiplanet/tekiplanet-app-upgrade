<x-mail.layout>
    <x-slot:greeting>
        Hello {{ $professional->user->name }}!
    </x-slot>

    <p>Congratulations! Your application for the GRIT "{{ $grit->title }}" has been approved.</p>
    
    <div style="margin: 20px 0; padding: 15px; background-color: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px;">
        <h3 style="margin-top: 0; color: #0c4a6e; font-size: 18px;">{{ $grit->title }}</h3>
        <p><strong>Category:</strong> {{ $grit->category->name }}</p>
        <p><strong>Budget:</strong> {{ $grit->owner_currency ?? '₦' }}{{ number_format($grit->owner_budget ?? $grit->budget, 2) }}</p>
        <p><strong>Deadline:</strong> {{ optional($grit->deadline)->format('M d, Y') }}</p>
        <p><strong>Status:</strong> <span style="color: #059669; font-weight: 600;">Approved</span></p>
    </div>

    <p>You have been assigned to this GRIT and can now start working on it. The business owner will be notified of your approval and you can begin communication through the platform.</p>

    <x-slot:closing>
        <p>Please log in to your account to view the full details and get started.</p>
        <p>Best regards,<br>TekiPlanet Team</p>
    </x-slot>
</x-mail.layout> 